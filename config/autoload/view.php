<?php
declare(strict_types=1);

use Hyperf\View\Mode;
use Hyperf\View\Engine\BladeEngine;

return [
    'engine' => BladeEngine::class, // 使用的渲染引擎
    // 'mode' => Mode::TASK,          // 不填写则默认为 Task 模式，推荐使用 Task 模式
    'mode' => Hyperf\View\Mode::SYNC,//
    'config' => [                   // 若下列文件夹不存在请自行创建
        'view_path' => BASE_PATH . '/app/View/',
        'cache_path' => BASE_PATH . '/runtime/view/',
        //'aliases'   => [
        //    'Helper' => App\Helpers\Helper::class,
        //],
    ],
];
