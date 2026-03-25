#!/usr/bin/env php
<?php

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

require BASE_PATH . '/vendor/autoload.php';

use Hyperf\Utils\ApplicationContext;
use Hyperf\Contract\ApplicationInterface;

// 初始化类加载器
Hyperf\Di\ClassLoader::init();

// 加载容器
$container = require BASE_PATH . '/config/container.php';

// 设置全局容器（同时设置两个命名空间的容器）
ApplicationContext::setContainer($container);
// 如果 Hyperf\Context\ApplicationContext 存在，也设置它
if (class_exists('Hyperf\Context\ApplicationContext')) {
    \Hyperf\Context\ApplicationContext::setContainer($container);
}

// 启动应用
$application = $container->get(ApplicationInterface::class);
$application->run();

