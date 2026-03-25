<?php

declare(strict_types=1);

/**
 * WebSocket 与账变推送配置
 */
return [
    // 账变事件 Redis 频道名，发布后 WS 订阅端会推送给对应用户
    'account_change_channel' => env('WS_ACCOUNT_CHANGE_CHANNEL', 'account_change'),
    // WebSocket 服务端口（与 HTTP 同进程，需在 server.php 中增加 ws 服务）
    'port' => (int) env('WS_PORT', 9509),
];
