<?php
/**
 * ApplicationContext 别名类
 * 用于兼容 Hyperf\Context\ApplicationContext 和 Hyperf\Utils\ApplicationContext
 */

namespace Hyperf\Context;

use Hyperf\Utils\ApplicationContext as UtilsApplicationContext;
use Psr\Container\ContainerInterface;

class ApplicationContext
{
    public static function getContainer(): ContainerInterface
    {
        return UtilsApplicationContext::getContainer();
    }

    public static function hasContainer(): bool
    {
        return UtilsApplicationContext::hasContainer();
    }

    public static function setContainer(ContainerInterface $container): ContainerInterface
    {
        return UtilsApplicationContext::setContainer($container);
    }
}
