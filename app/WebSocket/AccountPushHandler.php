<?php

declare(strict_types=1);

namespace App\WebSocket;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Hyperf\Redis\Redis;
use Hyperf\Contract\ConfigInterface;
use App\Http\Frontend\Dao\Users as UsersDao;

/**
 * WebSocket 账变推送：握手鉴权、维护 user_id -> fd 映射，供 Redis 订阅端推送。
 * Redis: user_ws:{ext_user_id} = SET of "worker_id:fd"；ws_handshake:{fd}=ext_user_id（握手阶段 TTL）；ws_fd_uid:{worker_id}:{fd}=ext_user_id（onClose 时查找）.
 */
class AccountPushHandler
{
    private const HANDSHAKE_TTL = 30;
    private const KEY_HANDSHAKE = 'ws_handshake:%d';
    private const KEY_USER_WS = 'user_ws:%d';
    private const KEY_FD_UID = 'ws_fd_uid:%d:%d';

    public function __construct(
        protected Redis $redis,
        protected ConfigInterface $config
    ) {
    }

    /**
     * 握手：校验 token（仅接受 header Authorization: Bearer <token>），
     * 解析出 ext_user_id 并写入 Redis 供 onOpen 使用。
     *
     * 不再接受 URL query ?token= 以避免 token 泄露到访问日志和 Referer。
     */
    public function onHandshake(Request $request, Response $response): bool
    {
        if (isset($request->get['token'])) {
            $response->status(400);
            $response->end('token via query string is no longer accepted; use Authorization header');
            return false;
        }

        $token = '';
        $auth = $request->header['authorization'] ?? '';
        if ($auth !== '' && stripos($auth, 'Bearer ') === 0) {
            $token = trim(substr($auth, 7));
        }
        if ($token === '') {
            $response->status(401);
            $response->end('token required via Authorization header');
            return false;
        }

        $userResult = UsersDao::getUserByToken($token);
        if ($userResult['error'] !== '' || empty($userResult['data']['user_id'])) {
            $response->status(401);
            $response->end('invalid token');
            return false;
        }

        $initResult = UsersDao::initializeExtUser($userResult['data']['user_id']);
        if ($initResult['error'] !== '' || empty($initResult['ext_user_id'])) {
            $response->status(403);
            $response->end('user init failed');
            return false;
        }

        $extUserId = (int) $initResult['ext_user_id'];
        $fd = $request->fd;
        $key = sprintf(self::KEY_HANDSHAKE, $fd);
        $this->redis->setex($key, self::HANDSHAKE_TTL, (string) $extUserId);

        $response->status(101);
        $response->header('Upgrade', 'websocket');
        $response->header('Connection', 'Upgrade');
        $response->header('Sec-WebSocket-Accept', $this->secAccept($request->header['sec-websocket-key'] ?? ''));
        $response->header('Sec-WebSocket-Version', '13');
        $response->end();
        return true;
    }

    public function onOpen(Server $server, Request $request): void
    {
        $fd = $request->fd;
        $workerId = $server->worker_id;
        $key = sprintf(self::KEY_HANDSHAKE, $fd);
        $extUserId = $this->redis->get($key);
        $this->redis->del($key);

        if ($extUserId === false || $extUserId === '') {
            $server->close($fd);
            return;
        }

        $extUserId = (int) $extUserId;
        $member = $workerId . ':' . $fd;
        $this->redis->sAdd(sprintf(self::KEY_USER_WS, $extUserId), $member);
        $this->redis->setex(sprintf(self::KEY_FD_UID, $workerId, $fd), 86400 * 7, (string) $extUserId);
    }

    public function onMessage(Server $server, Frame $frame): void
    {
        // 可选：处理 ping/pong 或心跳，此处忽略
    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        $workerId = $server->worker_id;
        $key = sprintf(self::KEY_FD_UID, $workerId, $fd);
        $extUserId = $this->redis->get($key);
        $this->redis->del($key);
        if ($extUserId !== false && $extUserId !== '') {
            $this->redis->sRem(sprintf(self::KEY_USER_WS, (int) $extUserId), $workerId . ':' . $fd);
        }
    }

    private function secAccept(string $key): string
    {
        return base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
    }
}
