<?php
declare(strict_types=1);

namespace App\Caches;

use App\Model\Admin;
use App\Common\Cache;

class AdminsCache extends BaseCache
{
    /**
     * @var string
     */
    protected static string $modelName = Admin::class;

    /**
     * @param string $name
     * @return Admin|null
     */
    public static function getAdminByName(string $name): ?Admin
    {
        $rows = self::all();
        foreach ($rows as $r) {
            if ($r->username == $name) {
                $model = new Admin();
                self::toModel((array)$r, $model);
                return $model;
            }
        }

        return null;
    }

    /**
     * @param int $id
     * @param string $name
     * @param string $sessionID
     * @param string $ip
     * @return void
     */
    public static function setLogin(int $id, string $name, string $sessionID, string $ip)
    {
        $cache = Cache::get();
    }
}
