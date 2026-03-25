<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Common\Utils;

/**
 * @property int $id
 * @property int $admin_id
 * @property string $admin_name
 * @property int $object_id
 * @property string $object_name
 * @property int $level
 * @property int $type
 * @property string $value_old
 * @property string $value_new
 * @property string $module
 * @property string $url
 * @property string $ip
 * @property int $created
 * @property string $remark
 */
class AdminLog extends Model implements CacheableInterface
{
    use Cacheable;

    public const LEVEL_DEBUG = 0;
    public const LEVEL_GENERAL = 1;
    public const LEVEL_WARNING = 2;
    public const LEVEL_DANGER = 3;
    public const LEVEL_FAULT = 4;
    public const LEVEL_ERROR = 5;

    public const TYPE_IO = 0;
    public const TYPE_FINANCE = 1;
    public const TYPE_USER = 2;
    public const TYPE_CONTEXT = 3;
    public const TYPE_SYSTEM = 4;
    public const TYPE_OTHER = 5;

    const CREATED_AT = 'created';
    const UPDATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'admin_log';
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
        'id' => 'integer',
        'admin_id' => 'integer',
        'object_id' => 'integer',
        'level' => 'integer',
        'type' => 'integer',
        'created' => 'integer',
    ];

    protected ?string $dateFormat = 'U';


    /**
     * @param RequestInterface $request
     * @param int $userId
     * @param string $userName
     * @param string $message
     * @return void
     */
    public static function login(RequestInterface $request, int $userId, string $userName, string $message = '后台用户登录系统')
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
     * @param int $toUserId
     * @param string $toUserName
     * @return void
     */
    private static function writeLog(RequestInterface $request, int $userId, string $userName, string $message, int $level, int $type, int $toUserId = 0, string $toUserName = '')
    {
        $clientIP = Utils::clientIP($request);
        $ipList = Utils::clientIPList($request);
        $data = json_encode([
            'data' => $request->all(),
            'browser' => $request->header('user-agent'),
            'ip' => $ipList,
        ], JSON_UNESCAPED_UNICODE); // 所有提交数据
        $savingData = [
            'admin_id' => $userId,
            'admin_name' => $userName,
            'object_id' => $toUserId ?? $userId,
            'object_name' => $toUserName ?? $userName,
            'level' => $level,
            'type' => $type,
            'url' => $request->url(),
            'module' => $request->getPathInfo(),
            'ip' => $clientIP,
            'remark' => $message,
            'created' => time(),
            'data' => $data,
        ];
        self::query()->insert($savingData);
    }

    /**
     * @param RequestInterface $request
     * @param int $userId
     * @param string $userName
     * @param string $message
     * @return void
     */
    public static function logout(RequestInterface $request, int $userId, string $userName, string $message = '用户退出系统')
    {
        self::writeLog($request, $userId, $userName, $message, self::LEVEL_GENERAL, self::TYPE_IO);
    }

    /**
     * @param RequestInterface $request
     * @param int $userId
     * @param string $userName
     * @param string $message
     * @param int $toUserId
     * @param string $toUserName
     * @return void
     */
    public static function finance(RequestInterface $request, int $userId, string $userName, string $message = '用户财务操作', int $toUserId = 0, string $toUserName = '')
    {
        self::writeLog($request, $userId, $userName, $message, self::LEVEL_DANGER, self::TYPE_FINANCE, $toUserId, $toUserName);
    }

    /**
     * @param RequestInterface $request
     * @param int $userId
     * @param string $userName
     * @param string $message
     * @param int $toUserId
     * @param string $toUserName
     * @return void
     */
    public static function user(RequestInterface $request, int $userId, string $userName, string $message = '用户操作', int $toUserId = 0, string $toUserName = '')
    {
        self::writeLog($request, $userId, $userName, $message, self::LEVEL_WARNING, self::TYPE_USER, $toUserId, $toUserName);
    }

    /**
     * @param RequestInterface $request
     * @param int $userId
     * @param string $userName
     * @param string $message
     * @param int $toUserId
     * @param string $toUserName
     * @return void
     */
    public static function content(RequestInterface $request, int $userId, string $userName, string $message = '用户内容管理相关操作', int $toUserId = 0, string $toUserName = '')
    {
        self::writeLog($request, $userId, $userName, $message, self::LEVEL_GENERAL, self::TYPE_CONTEXT, $toUserId, $toUserName);
    }

    /**
     * @param RequestInterface $request
     * @param int $userId
     * @param string $userName
     * @param string $message
     * @param int $toUserId
     * @param string $toUserName
     * @return void
     */
    public static function system(RequestInterface $request, int $userId, string $userName, string $message = '系统管理相关操作', int $toUserId = 0, string $toUserName = '')
    {
        self::writeLog($request, $userId, $userName, $message, self::LEVEL_GENERAL, self::TYPE_SYSTEM, $toUserId, $toUserName);
    }

    /**
     * @param RequestInterface $request
     * @param int $userId
     * @param string $userName
     * @param string $message
     * @param int $toUserId
     * @param string $toUserName
     * @return void
     */
    public static function other(RequestInterface $request, int $userId, string $userName, string $message = '其他操作', int $toUserId = 0, string $toUserName = '')
    {
        self::writeLog($request, $userId, $userName, $message, self::LEVEL_FAULT, self::TYPE_OTHER, $toUserId, $toUserName);
    }
}
