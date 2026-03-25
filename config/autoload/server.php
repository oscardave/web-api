<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    'mode' => SWOOLE_PROCESS,
    'servers' => [
        [
            'name' => 'ws',
            'type' => Server::SERVER_WEBSOCKET,
            'host' => '0.0.0.0',
            'port' => (int)env('WS_PORT', 9509),
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_HAND_SHAKE => [\App\WebSocket\AccountPushHandler::class, 'onHandshake'],
                Event::ON_OPEN => [\App\WebSocket\AccountPushHandler::class, 'onOpen'],
                Event::ON_MESSAGE => [\App\WebSocket\AccountPushHandler::class, 'onMessage'],
                Event::ON_CLOSE => [\App\WebSocket\AccountPushHandler::class, 'onClose'],
            ],
        ],
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => (int)env('APP_PORT', 9005),
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
        ],
    ],
    'settings' => [
        'enable_coroutine' => true,
        'worker_num' => swoole_cpu_num(),
        'pid_file' => BASE_PATH . '/runtime/hyperf.pid',
        'max_coroutine' => 100000,
        'open_tcp_nodelay' => true,
        'open_http2_protocol' => true,
        // 'open_cpu_affinity' => true,
        // 'cpu_affinity_ignore' => [0, 1],
        'max_request' => 100000,
        'socket_buffer_size' => 20 * 1024 * 1024,
        'buffer_output_size' => 20 * 1024 * 1024,
        'task_enable_coroutine' => false,
        'reactor_num' => swoole_cpu_num(),
        'task_worker_num' => swoole_cpu_num(),
        'package_max_length' => 1024 * 1024 * 20,
    ],
    'callbacks' => [
        Event::ON_WORKER_START => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
        Event::ON_PIPE_MESSAGE => [Hyperf\Framework\Bootstrap\PipeMessageCallback::class, 'onPipeMessage'],
        Event::ON_WORKER_EXIT => [Hyperf\Framework\Bootstrap\WorkerExitCallback::class, 'onWorkerExit'],
        Event::ON_TASK => [Hyperf\Framework\Bootstrap\TaskCallback::class, 'onTask'],
        Event::ON_FINISH => [Hyperf\Framework\Bootstrap\FinishCallback::class, 'onFinish']
    ],
];
