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
// use Hyperf\HttpServer\Router\Router;

// --------------------------------------------------------------------------------------------------------------------------
// 一、前端相关处理接口/路由
// --------------------------------------------------------------------------------------------------------------------------
// Router::addGroup(
//     '/api/v1',
//     function () {
//         // index - 首页相关
//         Router::addRoute(['POST'], '/test', 'App\Http\Frontend\Controllers\IndexController@test');
//     },
// //['middleware' => [\App\Middleware\GlobalMiddleware::class]]
// );

// --------------------------------------------------------------------------------------------------------------------------
// 二、后端相关处理接口/路由
// --------------------------------------------------------------------------------------------------------------------------
// 该 Group 下的所有路由都将应用配置的中间件
// Router::addGroup(
//     '',
//     function () {
//         // index - 首页相关
//         // Router::addRoute(['POST'], '/index/logout', 'App\Http\Backend\Controllers\IndexController@logout');
//         // Router::addRoute(['GET'], '/index/manage', 'App\Http\Backend\Controllers\IndexController@manage');
//     },
//     ['middleware' => [\App\Middleware\GlobalMiddleware::class]]
// );
