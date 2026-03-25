<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Redis\Redis;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coroutine\Coroutine;
use Swoole\Server;

/**
 * Worker 0 内启动 Redis 订阅，收到账变事件后按 user_id 查找 WS 连接并通过 sendMessage 让对应 worker 推送。
 */
class AccountChangeSubscribeListener implements ListenerInterface
{
    public const PUSH_TYPE = 'ws_push';

    public function __construct(
        protected Redis $redis,
        protected ConfigInterface $config
    ) {
    }

    public function listen(): array
    {
        return [AfterWorkerStart::class];
    }

    public function process(object $event): void
    {
        if (! $event instanceof AfterWorkerStart) {
            return;
        }
        $server = $event->server;
        $workerId = $event->workerId;
        if ($workerId !== 0) {
            return;
        }

        $channel = $this->config->get('websocket.account_change_channel', 'account_change');

        Coroutine::create(function () use ($server, $channel): void {
            $this->redis->subscribe([$channel], function ($redis, $channelName, $message) use ($server): void {
                $data = json_decode($message, true);
                if (! is_array($data) || empty($data['user_id'])) {
                    return;
                }
                $userId = (int) $data['user_id'];
                $pushPayload = json_encode([
                    'type' => 'account_change',
                    'user_id' => $userId,
                    'ts' => $data['ts'] ?? time(),
                ], JSON_UNESCAPED_UNICODE);

                $key = 'user_ws:' . $userId;
                $members = $this->redis->sMembers($key);
                if (! is_array($members)) {
                    return;
                }
                foreach ($members as $member) {
                    if (strpos($member, ':') === false) {
                        continue;
                    }
                    [$wId, $fd] = explode(':', $member, 2);
                    $wId = (int) $wId;
                    $fd = (int) $fd;
                    $server->sendMessage($wId, json_encode([
                        'type' => self::PUSH_TYPE,
                        'fd' => $fd,
                        'message' => $pushPayload,
                    ]));
                }
            });
        });
    }
}
