<?php
declare(strict_types=1);

namespace App\Caches;

use App\Common\Cache;
use App\Model\System;
use Hyperf\DbConnection\Db;

class ConfigsCache extends BaseCache
{
    private const KEY_INDEX_CONFIG = 'index_configs';

    /**
     * @var string
     */
    protected static string $modelName = System::class;

    /**
     * @var array|string[]
     */
    protected static array $fields = ['id', 'key', 'value'];

    /**
     * @var array|string[]
     */
    private static array $cachedKeys = [
        'reg_onoff',
        'sms_onoff',
        'company_desc',
        'reward',
        'company_images',
        'h5_domain',
        'protocol',
        'privacy',
        'logo',
        'name',
        'min_withdraw_money',
        'withdraw_start_time',
        'withdraw_end_time',
    ];

    /**
     * @param string $key
     * @return ?string
     */
    public static function getIndexConfig(string $key): ?string
    {
        $res = self::getIndexConfigs();
        return $res[$key];
    }

    /**
     * @return array
     */
    public static function getIndexConfigs(): array
    {
        $key = self::KEY_INDEX_CONFIG;
        $cache = Cache::get();
        $cache->del($key);
        $res = $cache->get($key);
        if (empty($res)) {
            $arr = [];
            $list = Db::table("system")->get();
            foreach ($list as $val) {
                $arr[$val->key] = $val->value;
            }
            $cache->set($key, json_encode($arr), self::EXPIRE_TIME);
            return $arr;
        }
        $data = json_decode($res);
        return (array)$data;
    }

    /**
     * @param array $keys
     * @return array|null
     */
    public static function getIndexConfigArray(array $keys): ?array
    {
        $res = self::getIndexConfigs();
        $arr = [];
        foreach ($keys as $v) {
            $arr[$v] = $res[$v];
        }
        return $arr;
    }
}
