<?php
declare(strict_types=1);

namespace App\Caches;

use App\Common\Cache;
use App\Model\Bank;

class BankCache extends BaseCache
{
    private const KEY_INDEX_CONFIG = 'user_bank';

    /**
     * @var string
     */
    protected static string $modelName = Bank::class;
    /**
     * @var array|string[]
     */
    protected static array $fields = ['id', 'uid', 'name', 'bank_name'];

    /**
     * @param int $key
     * @return array|null
     */
    public static function getById(int $key): ?array
    {
        $key = self::KEY_INDEX_CONFIG . "_" . $key;
        $cache = Cache::get();
        $res = $cache->get($key);

        if ($res) {
            return json_decode($res);
        }

        return [];
    }

    /**
     * @param int $key
     * @param array $arr
     * @return void
     */
    public static function setById(int $key, array $arr)
    {
        $key = self::KEY_INDEX_CONFIG . "_" . $key;
        $cache = Cache::get();
        $value = json_encode($arr);
        $cache->set($key, $value, static::EXPIRE_TIME);
    }

    /**
     * @param int $id
     * @return void
     */
    public static function del(int $id)
    {
        $key = self::KEY_INDEX_CONFIG . "_" . $id;
        self::delKey($key);
    }
}
