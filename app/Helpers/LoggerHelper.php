<?php
declare(strict_types=1);

namespace App\Helpers;

use Hyperf\Utils\ApplicationContext;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class LoggerHelper
{
    /**
     * @var LoggerInterface|null
     */
    private static ?LoggerInterface $logger = null;

    /**
     * 获取日志实例
     * @return LoggerInterface
     */
    public static function getLogger(): LoggerInterface
    {
        if (!self::$logger) {
            $container = ApplicationContext::getContainer();
            self::$logger = $container->get(LoggerFactory::class)->get();
        }
        return self::$logger;
    }

    /**
     * 记录 debug 级别日志
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function debug(string $message, array $context = []): void
    {
        self::getLogger()->debug($message, $context);
    }

    /**
     * 记录 info 级别日志
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function info(string $message, array $context = []): void
    {
        self::getLogger()->info($message, $context);
    }

    /**
     * 记录 warning 级别日志
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function warning(string $message, array $context = []): void
    {
        self::getLogger()->warning($message, $context);
    }

    /**
     * 记录 error 级别日志
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function error(string $message, array $context = []): void
    {
        self::getLogger()->error($message, $context);
    }
}
