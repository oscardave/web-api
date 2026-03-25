<?php
declare(strict_types=1);

namespace App\Http\Backend\Middlewares;

use App\Http\Backend\Dao\AdminLogDao;
use App\Http\Backend\Utils\BackendUtils;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 后台访问日志中间件：将 /ht/v1 下的请求记录到 syn_admin_logs
 */
class AdminLogger implements MiddlewareInterface
{
    /** 不记录日志的路径（登录、查看/删除日志等） */
    private const EXCLUDE_PATHS = [
        '/ht/v1/index/login',
        '/ht/v1/index/refresh_token',
        '/ht/v1/adminLogs/list',
        '/ht/v1/adminLogs/detail',
        '/ht/v1/adminLogs/del',
    ];

    /**
     * 注入标准输出日志，用于写库失败时打 warning
     */
    public function __construct(
        protected StdoutLoggerInterface $logger
    ) {
    }

    /**
     * 中间件入口：仅对 /ht/v1 且未在排除列表中的请求在响应后写入 syn_admin_logs
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        if (!$this->shouldLog($path)) {
            return $handler->handle($request);
        }

        $response = $handler->handle($request);

        $this->writeLog($request, $path);
        return $response;
    }

    /**
     * 判断当前请求路径是否需要记录日志：必须以 /ht/v1 开头且不在 EXCLUDE_PATHS 中
     */
    private function shouldLog(string $path): bool
    {
        if (strpos($path, '/ht/v1') !== 0) {
            return false;
        }
        return !in_array($path, self::EXCLUDE_PATHS, true);
    }

    /**
     * 从请求中提取 method、admin_id、ip 等并写入 syn_admin_logs，异常仅打日志不影响响应
     */
    private function writeLog(ServerRequestInterface $request, string $path): void
    {
        try {
            $method = $request->getMethod();
            $adminId = $this->getAdminId($request);
            $ip = $this->getClientIp($request);
            $action = $method . ' ' . $path;

            $result = AdminLogDao::insert([
                'admin_id' => $adminId,
                'path' => $path,
                'menu_id' => 0,
                'action' => $action,
                'method' => $method,
                'ip' => $ip,
                'remark' => '',
            ]);
            if ($result['error'] !== '') {
                $this->logger->warning('[AdminLogger] insert failed: ' . $result['error']);
            }
        } catch (\Throwable $e) {
            $this->logger->warning('[AdminLogger] ' . $e->getMessage());
        }
    }

    /**
     * 从请求头 Authorization 的 Bearer Token 解析出管理员 ID，无 token 或解析失败返回 0
     */
    private function getAdminId(ServerRequestInterface $request): int
    {
        $auth = $request->getHeaderLine('Authorization');
        if ($auth === '') {
            return 0;
        }
        $token = trim($auth);
        if (strpos($token, 'Bearer ') === 0) {
            $token = trim(substr($token, 7));
        }
        if ($token === '') {
            return 0;
        }
        $admin = BackendUtils::parseToken($token);
        if ($admin === null || !isset($admin['id'])) {
            return 0;
        }
        return (int) $admin['id'];
    }

    /**
     * 获取客户端 IP：优先 X-Real-IP，其次 X-Forwarded-For 首段，最后 remote_addr
     */
    private function getClientIp(ServerRequestInterface $request): string
    {
        $ip = $request->getHeaderLine('X-Real-IP');
        if ($ip !== '') {
            return trim(explode(',', $ip)[0]);
        }
        $ip = $request->getHeaderLine('X-Forwarded-For');
        if ($ip !== '') {
            return trim(explode(',', $ip)[0]);
        }
        $serverParams = $request->getServerParams();
        return (string) ($serverParams['remote_addr'] ?? '');
    }
}
