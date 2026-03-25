<?php
declare(strict_types=1);

namespace App\Model;

use App\Common\Cache;

/**
 * @property int $id
 * @property string $name
 * @property string $login_ip
 * @property int $login_time
 * @property string $session_id
 */
class AdminSession
{
    /**
     * @param int $id
     * @return AdminSession|null
     */
    public static function get(int $id): ?AdminSession
    {
        $key = 'admin_login_' . $id;
        $cache = Cache::get();
        $value = $cache->get($key);
        if (!$value) {
            return null;
        }
        $obj = new AdminSession();
        $val = json_decode($value);
        $obj->session_id = $val->session_id;
        $obj->id = $val->id;
        $obj->name = $val->name;
        $obj->login_ip = $val->login_ip;
        $obj->login_time = $val->login_time;
        return $obj;
    }

    /**
     * @return void
     */
    public function refresh()
    {
        $this->save();
    }

    /**
     * @return void
     */
    public function save()
    {
        $key = 'admin_login_' . $this->id;
        $cache = Cache::get();
        $cache->setex($key, 9600, json_encode((object)[
            'id' => $this->id,
            'name' => $this->name,
            'login_ip' => $this->login_ip,
            'login_time' => $this->login_time,
            'session_id' => $this->session_id,
        ]));
    }
}
