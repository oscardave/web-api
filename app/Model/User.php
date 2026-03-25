<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 *
 * @property string $name
 * @property string $password_hash
 * @property int $creation_ts
 * @property string $username
 * @property string $invite
 * @property string $admin
 * @property int $upgrade_ts
 * @property int $is_guest
 * @property int $appservice_id
 * @property int $consent_version
 * @property int $consent_server_notice_sent
 * @property int $user_type
 * @property int $deactivated
 * @property bool $shadow_banned
 * @property int $consent_ts
 * @property bool $approved
 * @property bool $locked
 * @property bool $suspended
 * @property string $avatar_url
 * @property string $nickname
 * @property int $score
 * @property int $level
 * @property string $phone
 * @property string $email

 */
class User extends Model implements CacheableInterface
{
    use Cacheable;

    public static array $usernames = [];

    protected  string $primaryKey = 'name';
    public bool $incrementing = false;
    public bool $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected array $casts = [

    ];

    /**
     * @param array $obj
     * @return User
     */
    public static function toUser(array $obj): User
    {
        $user = new User();
        foreach ($obj as $prop => $value) {
            $user->$prop = $value;
        }
        return $user;
    }

    /**
     * 判断用户是否已经注册
     * @param string $username
     * @return bool
     */
    public static function isRegistered(string $username): bool
    {
        if (count(self::$usernames) == 0) {
            self::loadNames();
        }

        return in_array($username, self::$usernames);
    }

    /**
     * 加载用户名称到内容当中
     * @return void
     */
    public static function loadNames()
    {
        $rArr = self::names();
        self::$usernames = $rArr;
    }

    /**
     *  加载所有用户名称
     * @return array
     */
    public static function names(): array
    {
        $rArr = [];
        $rows = self::all();
        foreach ($rows as $r) {
            $rArr[] = trim($r->username);
        }
        return $rArr;
    }
}