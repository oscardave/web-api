<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\HttpServer\Contract\RequestInterface as HttpRequest;
use App\Common\Cache;
use Hyperf\Di\Annotation\Inject;
use App\Common\Utils;
use Psr\Http\Message\ResponseInterface;
use App\Caches\UsersCache;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Model\BlockedIp;
use App\Model\BlockedUser;
use App\Model\BlockedDevice;
use App\Model\User;


class Limiter implements MiddlewareInterface
{

    /**
     * 每分钟最大可以允许访问次数
     * @var int
     */
    const MAX_VISIT = 50;

    /**
     * 需要管控的API接口或地址
     * @var array|int[]
     */
    public static array $checkPaths = [
        '/api/v1/login/login' => 0,
        '/index/code' => 0,
        '/' => 0,
    ];

    /**
     * 拉黑的IP列表 key:value => ip:加入时间
     * @var array
     */
    public static array $blockedIPs = [];

    /**
     * 拉黑的用户信息 key:value => 用户名称:加入时间
     * @var array
     */
    public static array $blockedUsers = [];

    /**
     * 拉黑的用户浏览器头信息 key:value => 头信息:加入时间
     * @var array
     */
    public static array $blockedDevices = [];

    /**
     * @var bool
     */
    private static bool $initialized = false;

    /**
     * @Inject()
     * @var HttpResponse
     */
    protected HttpResponse $response;
    /**
     * @Inject()
     * @var HttpRequest
     */
    protected HttpRequest $request;

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!self::$initialized) {
            self::initializeList();
        }
//        $token = $this->request->header("authorization");
//        if (!empty($token)) {
//            $user = UsersCache::getUserByToken($token);
//            if ($user && isset($user->id)) {
//                $temp = User::find($user->id);
//                if ($temp && $temp->status == 2) {
//                    return $this->limitError('IB');
//                }
//            }
//        }


        // -- 检测是否在监控范围
        $path = '/' . trim($request->getUri()->getPath(), '/'); // 当前请求路径
        if (!isset(self::$checkPaths[$path])) { // 如果不在监测范围之内, 则直接返回
            return $handler->handle($request);
        }

        // -------------------------- 关于ip相关处理 ------------------------------------
        // -- 判断是否是ip黑名单
        $clientIP = Utils::clientIPByPSR($request);
        if (isset(self::$blockedIPs[$clientIP])) { // 如果已经被加到了黑名单里面, 则直接返回
            return $this->limitError('IB');
        }
        // -- 查询redis连接, 判断本ip是否多次请求
        $currentTime = date('YmdHi');
        $rKey = $clientIP . '_' . $currentTime;
        $cache = Cache::get();
        $visitCount = $cache->incr($rKey);
        if ($visitCount == 1) {
            $cache->expire($rKey, 180); // 自动设置过期时间 - 3分钟
        }
        if ($visitCount >= self::MAX_VISIT) { // 如果每分钟超过最大限流则返回错误
            self::$blockedIPs[$clientIP] = time();
            BlockedIp::addRow($clientIP);
            return $this->limitError('IM');
        }

        // -------------------------- 关于浏览器头信息相关处理 --------------------------------
        $userAgent = trim($this->request->header('user-agent', ''));
        if (!$userAgent) { // 如果查不到, 则直接返回
            return $this->limitError('GN');
        }
        if (isset(self::$blockedDevices[$userAgent])) { // 在拉黑名单里里面
            return $this->limitError('GB');
        }
        // 因为可能多个用户的头信息是一样的, 比如用同一个操作系统/同浏览器版本, 此时将不再进一步判断

        // -------------------------- 关于用户相关相关处理 ------------------------------------
        // -- 检查redis, 判断本用户是否多次请求
        $token = $this->request->header('authorization', '');
        if ($token) {
            $user = UsersCache::getUserByToken($token);
            if (!$user) {
                return $this->limitError('UN');
            }

            // 只要在查到用信息的情况下, 才能使用此检测项 // 先检查是否被拉黑过
            if (isset(self::$blockedUsers[$user->username])) {
                return $this->limitError('UB');
            }

            $uKey = $user->username . '_' . $currentTime;
            $userVisits = $cache->incr($uKey);
            if ($userVisits === 1) {
                $cache->expire($uKey, 180);
            }
            if ($userVisits >= self::MAX_VISIT) { // 如果超出最大限制
                self::$blockedUsers[$user->username] = time(); // 将用户拉入黑名单
                BlockedUser::addRow($user->username);
                return $this->limitError('UM');
            }
        }

        return $handler->handle($request); // 正常返回
    }

    /**
     * @return void
     */
    private static function initializeList()
    {
        self::initializeBlockedIPs(); // ip黑名单
        self::initializeBlockedUsers(); // 用户黑名单

        // 设备黑名单
        $rows = BlockedDevice::all();
        $rArr = [];
        foreach ($rows as $r) {
            $rArr[trim($r->device)] = $r->create_time;
        }
        self::$blockedDevices = $rArr;

        self::$initialized = true; // 标记为初始化完成
    }

    /**
     * IP黑名单
     * @return void
     */
    public static function initializeBlockedIPs()
    {
        $rows = BlockedIp::all();
        $rArr = [];
        foreach ($rows as $r) {
            $rArr[trim($r->ip)] = $r->create_time;
        }
        self::$blockedIPs = $rArr;
    }

    /**
     * 用户黑名单
     * @return void
     */
    public static function initializeBlockedUsers()
    {
        // 用户黑名单
        $rows = BlockedUser::all();
        $rArr = [];
        foreach ($rows as $r) {
            $rArr[trim($r->username)] = $r->create_time;
        }
        self::$blockedUsers = $rArr;
    }

    /**
     * 返回默认错误信息
     * @param string $message
     * @return ResponseInterface
     */
    private function limitError(string $message = 'ER'): ResponseInterface
    {
        return $this->response->json([
            'code' => 200,
            'message' => '操作错误:' . $message,
        ]);
    }
}