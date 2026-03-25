<?php
declare(strict_types=1);

namespace App\Common;

use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Redis\Redis;
use App\Model\Model;

class Cache
{
    /**
     * @var ?Redis
     */
    private static ?Redis $cache = null;

    /**
     * 得到类缓存
     * @param string $modelName
     * @param int $id
     * @param int $timeout
     * @return object|NULL
     */
    public static function getModelById(string $modelName, int $id, int $timeout = 86400)
    {
        $key = last(explode('\\', $modelName)) . '_' . $id;
        $cache = self::get();
        $value = $cache->get($key);
        $now = time();
        if ($value) {
            $row = json_decode($value);
            if ($row === null) {
                $row = self::migrateLegacySerialized($value);
            }
            if ($row && isset($row->cached_time_by_redis) && $row->cached_time_by_redis > $now - $timeout) {
                return $row;
            }
        }

        $record = $modelName::query()->where('id', $id)->select();
        $row = $record->toArray();
        $row = (object)$row;
        $row->cached_time_by_redis = $now;
        $cache->set($key, json_encode($row));
        return $row;
    }

    /**
     * Migrate legacy PHP-serialized cache values to JSON.
     * Returns the decoded stdClass on success, or null on failure.
     */
    private static function migrateLegacySerialized(string $value): ?object
    {
        $row = @unserialize($value, ['allowed_classes' => false]);
        if ($row === false) {
            return null;
        }
        return (object)(array)$row;
    }

    /**
     * 返回redis客户端, 为了方便使用编辑器的代码提示,不再继续简化
     * @return Redis
     */
    public static function get(): Redis
    {
        if (!self::$cache) {
            $container = ApplicationContext::getContainer();
            self::$cache = $container->get(Redis::class);
        }
        return self::$cache;
    }


}
