<?php
declare(strict_types=1);

namespace App\Caches;

use App\Common\Cache;
use App\Model\PermissionIp;

class PermissionIpsCache extends BaseCache
{
    /**
     * @var string
     */
    protected static string $modelName = PermissionIp::class;

    /**
     * @param string $visitIP
     * @return bool
     */
    public static function allow(string $visitIP): bool
    {
        $cache = Cache::get();
        $key = self::getCacheKey(true);
        $rows = $cache->hGetAll($key);
        if (isset($rows[$visitIP])) {
            $row = json_decode($rows[$visitIP]);
            if (isset($row->status) && $row->status == 1) {
                return true;
            }
        }

        $rows = array_map(function ($r) {
            return (object)$r;
        }, PermissionIp::query()->get()->toArray());
        $returnValue = false;
        foreach ($rows as $r) {
            $cache->hSet($key, $r->ip, json_encode($r));
            if ($r->ip == $visitIP && $r->status == 1) {
                $returnValue = true;
            }
        }

        return $returnValue;
    }
}
