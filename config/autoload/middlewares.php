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

// use App\Http\Middleware\Cors;
// use App\Http\Middleware\Limiter;
use Hyperf\Session\Middleware\SessionMiddleware;
use Hyperf\Validation\Middleware\ValidationMiddleware;
use App\Http\Middleware\CheckNum;
use App\Http\Middleware\RouteLogMiddleware;
use App\Http\Backend\Middlewares\AdminLogger;

return [
    'http' => [
        RouteLogMiddleware::class, // 路由日志中间件，记录所有路由访问信息
        AdminLogger::class,         // 后台访问写入 syn_admin_logs
        SessionMiddleware::class,
        ValidationMiddleware::class,
        //CheckNum::class,
        //Limiter::class,
    ],
];
