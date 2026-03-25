<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Swoole\WebSocket\Server;

/**
 * 处理 worker 间 pipe 消息：type=ws_push 时向对应 fd 推送 WebSocket 消息。
 * 主服务为 WS（server.php 中 ws 配置在 http 前），主 server 具备 push 方法。
 */
class WsPushPipeMessageListener implements ListenerInterface
{
    public function listen(): array
    {
        return [OnPipeMessage::class];
    }

    public function process(object $event): void
    {
        if (! $event instanceof OnPipeMessage) {
            return;
        }
        $data = $event->data;
        if (! is_string($data)) {
            return;
        }
        $decoded = json_decode($data, true);
        if (! is_array($decoded) || ($decoded['type'] ?? '') !== AccountChangeSubscribeListener::PUSH_TYPE) {
            return;
        }
        $fd = (int) ($decoded['fd'] ?? 0);
        $message = $decoded['message'] ?? '';
        if ($fd <= 0 || $message === '') {
            return;
        }
        /** @var Server $server */
        $server = $event->server;
        if ($server->exists($fd)) {
            $server->push($fd, $message);
        }
    }
}
