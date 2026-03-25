<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\HttpServer\Contract\RequestInterface;
use App\Common\Utils;

/**
 * @property int $id
 * @property int $user_id
 * @property string $username
 * @property int $object_id
 * @property string $object_name
 * @property int $level
 * @property int $type
 * @property string $url
 * @property string $ip
 * @property string $user_agent
 * @property int $created
 * @property string $remark
 */
class UserLog extends Model
{
    const UPDATED_AT = null;
    const CREATED_AT = 'created';

    // 日志级别 0:调试 1:普通 2:警告 3:危险 4:致命 5:错误
    public const LEVEL_DEBUG = 0;
    public const LEVEL_GENERAL = 1;
    public const LEVEL_WARNING = 2;
    public const LEVEL_DANGER = 3;
    public const LEVEL_FAULT = 4;
    public const LEVEL_ERROR = 5;

    // 日志类型 0:登录退出 1:财务调整 2:会员管理 3:内容管理 4:系统设置 5:其他
    public const TYPE_IO = 0;
    public const TYPE_FINANCE = 1;
    public const TYPE_USER = 2;
    public const TYPE_CONTENT = 3;
    public const TYPE_SYSTEM = 4;
    public const TYPE_OTHER = 5;

    /**
     * @var array
     */
    public static array $logLevels = [
        0 => '调试',
        1 => '普通',
        2 => '警告',
        3 => '危险',
        4 => '致命',
        5 => '错误',
    ];

    /**
     * @var array
     */
    public static array $logTypes = [
        0 => '登录/退出',
        1 => '财务调整',
        2 => '会员操作',
        3 => '内容变更',
        4 => '系统设置',
        5 => '其他类型',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'user_log';
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
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'object_id' => 'integer', 'level' => 'integer', 'type' => 'integer', 'created' => 'integer'];
    protected ?string $dateFormat = 'U';

    /**
     * @param RequestInterface $request
     * @param int $userId
     * @param string $userName
     * @param string $message
     * @return void
     */
    public static function login(RequestInterface $request, int $userId, string $userName, string $message = '用户登录系统')
    {
        self::writeLog($request, $userId, $userName, $message, self::LEVEL_GENERAL, self::TYPE_IO);
    }

    /**
     * @param RequestInterface $request
     * @param int $userId
     * @param string $userName
     * @param string $message
     * @param int $level
     * @param int $type
     * @return void
     */
    private static function writeLog(RequestInterface $request, int $userId, string $userName, string $message, int $level, int $type)
    {
        // $clientIP = $request->server('remote_addr', '');
        $clientIP = Utils::clientIP($request);
        $data = [
            'user_id' => $userId,
            'username' => $userName,
            'object_id' => $userId,
            'object_name' => $userName,
            'level' => $level,
            'type' => $type,
            'url' => $request->url(),
            'ip' => $clientIP,
            'user_agent' => $request->header('user-agent', ''),
            'remark' => $message,
            'created' => time(),
        ];
        self::query()->insert($data);
    }

    /**
     * @param RequestInterface $request
     * @param int $userId
     * @param string $userName
     * @param string $message
     * @return void
     */
    public static function logout(RequestInterface $request, int $userId, string $userName, string $message = '后台用户退出系统')
    {
        self::writeLog($request, $userId, $userName, $message, self::LEVEL_GENERAL, self::TYPE_IO);
    }

    /**
     * @param RequestInterface $request
     * @param int $userId
     * @param string $userName
     * @param string $message
     * @return void
     */
    public static function finance(RequestInterface $request, int $userId, string $userName, string $message = '网站财务相关操作')
    {
        self::writeLog($request, $userId, $userName, $message, self::LEVEL_DANGER, self::TYPE_FINANCE);
    }

    /**
     * @param RequestInterface $request
     * @param int $userId
     * @param string $userName
     * @param string $message
     * @return void
     */
    public static function user(RequestInterface $request, int $userId, string $userName, string $message = '后台管理用户相关操作')
    {
        self::writeLog($request, $userId, $userName, $message, self::LEVEL_WARNING, self::TYPE_USER);
    }

    /**
     * @param RequestInterface $request
     * @param int $userId
     * @param string $userName
     * @param string $message
     * @return void
     */
    public static function content(RequestInterface $request, int $userId, string $userName, string $message = '内容管理相关操作')
    {
        self::writeLog($request, $userId, $userName, $message, self::LEVEL_GENERAL, self::TYPE_IO);
    }

    /**
     * @param RequestInterface $request
     * @param int $userId
     * @param string $userName
     * @param string $message
     * @return void
     */
    public static function system(RequestInterface $request, int $userId, string $userName, string $message = '系统管理相关操作')
    {
        self::writeLog($request, $userId, $userName, $message, self::LEVEL_GENERAL, self::TYPE_IO);
    }

    /**
     * @param RequestInterface $request
     * @param int $userId
     * @param string $userName
     * @param string $message
     * @return void
     */
    public static function other(RequestInterface $request, int $userId, string $userName, string $message = '其他操作')
    {
        self::writeLog($request, $userId, $userName, $message, self::LEVEL_FAULT, self::TYPE_OTHER);
    }
}
