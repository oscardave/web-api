<?php
declare(strict_types=1);

namespace App\Caches;

use Hyperf\HttpServer\Contract\RequestInterface;
use App\Common\Cache;
use \App\Model\User;
use Hyperf\DbConnection\Db;
use App\Helpers\LoggerHelper as Logger;
class UsersCache extends BaseCache
{
    /**
     * @var string
     */
    protected static string $modelName = User::class;

    /**
     * @param string $userName
     * @return User|null
     */
    public static function getUserByName(string $userName): ?User
    {
        $key = self::getCacheKey(true);
        $userKey = 'user_name_' . $userName;
        $value = Cache::get()->hget($key, $userKey);
        if ($value) {
            return User::toUser((array)json_decode($value));
        }

        $user = User::query()->where('username', '=', $userName)->first();
        if (!$user) {
            return null;
        }

        $uArr = $user->toArray();
        $model = User::toUser($uArr);
        Cache::get()->hset($key, $userKey, json_encode($model));

        return $model;
    }

    public static function Token(string $userLocalPart): string
    {
        // 1. base64 编码，不带 "="
        $b64local = rtrim(strtr(base64_encode($userLocalPart), '+/', '-_'), '=');

        // 2. 生成随机字符串（20位）
        $randomString = self::randomString(20);

        // 3. 组合基础部分
        $base = "syt_{$b64local}_{$randomString}";

        // 4. 计算 CRC32 校验并进行 Base62 编码
        $crc32 = sprintf("%u", crc32($base)); // 无符号整数
        $crc62 = self::base62Encode((int)$crc32, 6); // 最小宽度6

        // 5. 拼接最终 token
        return "{$base}_{$crc62}";
    }

    public static function randomString(int $length): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $str;
    }

    public static function base62Encode(int $num, int $minWidth = 0): string
    {
        $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $base = strlen($alphabet);
        $encoded = '';

        if ($num === 0) {
            $encoded = '0';
        } else {
            while ($num > 0) {
                $encoded = $alphabet[$num % $base] . $encoded;
                $num = intdiv($num, $base);
            }
        }

        // 填充宽度
        if ($minWidth > 0 && strlen($encoded) < $minWidth) {
            $encoded = str_pad($encoded, $minWidth, '0', STR_PAD_LEFT);
        }

        return $encoded;
    }

    /**
     * 设置用户token到redis
     * @param int $userId
     * @param string $token
     * @return void
     */
    public static function setToken(string $userId, string $token)
    {
        Cache::get()->setex('user_auth_sign_' . $userId, 86400 * 15, $token);
    }

    /**
     * @param string $token
     * @param User $user
     * @return void
     */
    public static function setUserToken(string $token, User $user)
    {
        $userView=Db::table("users_v")->where("name",$user->name)->first();
        $user->avatar_url=$userView->avatar_url??"";
        $user->nickname=$userView->nickname??"";
        Cache::get()->setex('user_token_' . $token, 86400 * 15, json_encode($user));
    }

    /**
     * @param string $token
     * @return bool
     */
    public static function isLoginByToken(string $token): bool
    {
        $user = Cache::get()->exists('user_token_' . $token);
        if (!$user) {
            return true;
        }
        return false;
    }

    /**
     * @param int $userId
     * @return bool
     */
    public static function isLoginById(int $userId): bool
    {
        if (self::getToken($userId)) {
            return true;
        }
        return false;
    }

    /**
     * 获取用户token
     * @param int $userId
     * @return string
     */
    public static function getToken(int $userId): string
    {
        $temp = Cache::get()->get('user_auth_sign_' . $userId);
        if (!$temp) {
            return '';
        }
        return $temp;
    }

    /**
     * <<<<<<< HEAD
     * @param RequestInterface $request
     * @return mixed
     */
    public static function getUserByRequest(RequestInterface $request): mixed
    {
        $token = $request->header('authorization', '');
        Logger::debug(__FILE__ . " " . __LINE__ . " header authorization: " . $token);
        if (!$token) {
            Logger::debug("token is empty");
            return null;
        }

        Logger::debug(__FILE__ . " " . __LINE__ . " after token: " . $token);
        // 去掉可能的 Bearer 前缀
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        Logger::debug(__FILE__ . " " . __LINE__ . " token: " . $token);

        return self::getUserByToken($token);
    }

    /**
     * @param string $token
     * @return mixed
     */
    public static function getUserByToken(string $token): mixed
    {
        $redis = Cache::get();
        $str =$redis->get('user_token_' . $token);
        if ($str) {
            return json_decode($str);
        } 

        // 根据 token 查询用户信息
        $rowToken = Db::table("access_tokens")->where("token", $token)->first();
        if (!$rowToken) {
            Logger::debug(__FILE__ . " " . __LINE__ . " rowToken is null");
            return null;
        }

        // 根据 user_id 查询用户信息
        $user = Db::table("users_v")->where("name", $rowToken->user_id)->first();
        if (!$user) {
            Logger::debug(__FILE__ . " " . __LINE__ . " user is null");
            return null;
        }

        // 缓存用户信息
        $redis->set('user_token_' . $token, json_encode($user), 86400);
        return $user;
    }

    /**
     * 删除用户缓存
     * @param int $userId
     * @return void
     */
    public static function deleteId(int $userId)
    {
        $key = self::getCacheKey(true);
        $userKey = 'user_id_' . $userId;
        Cache::get()->hdel($key, $userKey);
    }

    /**
     * @param int $userID
     * @return string
     */
    public static function getUserName(int $userID): string
    {
        $user = self::getUserById($userID);
        if ($user) {
            return $user->username ?? '';
        }
        return '';
    }

    /**
     * @param int $userID
     * @return User|null
     */
    public static function getUserById(int $userID): ?User
    {
        $key = self::getCacheKey(true);
        $userKey = 'user_id_' . $userID;
        $value = Cache::get()->hget($key, $userKey);
        if ($value) {
            return User::toUser((array)json_decode($value));
        }

        $user = User::query()->where('id', $userID)->first();
        if (!$user) {
            return null;
        }

        $model = User::toUser($user->toArray());

        Cache::get()->hset($key, $userKey, json_encode($model));

        return $model;
    }

    /**
     * @param int $userId
     * @return void
     */
    public function deleteById(int $userId)
    {
        $token = self::getToken($userId);
        self::deleteByToken($token);
    }

    /**
     * @param string $token
     * @return void
     */
    public static function deleteByToken(string $token)
    {
        $user = self::getUserByToken($token);
        if (!$user) {
            return;
        }

        Cache::get()->del('user_token_' . $token);
        Cache::get()->del('user_auth_sign_' . $user->id);
    }
}