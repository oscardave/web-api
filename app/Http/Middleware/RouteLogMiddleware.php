<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteLogMiddleware implements MiddlewareInterface
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'old_password',
        'new_password',
        'token',
        'access_token',
        'refresh_token',
        'authorization',
        'secret',
        'secret_key',
        'api_key',
    ];

    public function __construct(
        protected StdoutLoggerInterface $logger
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startTime = microtime(true);
        
        // 获取路由信息
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $query = $request->getUri()->getQuery();
        $fullPath = $path . ($query ? '?' . $query : '');
        $clientIP = $request->getServerParams()['remote_addr'] ?? 'unknown';
        
        // 处理请求
        $response = $handler->handle($request);
        
        // 计算耗时
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        $statusCode = $response->getStatusCode();
        
        // 记录请求参数（脱敏后）
        $requestData = $request->getParsedBody();
        if (is_array($requestData)) {
            $requestData = $this->maskSensitiveFields($requestData);
        }
        $requestData = json_encode($requestData, JSON_UNESCAPED_UNICODE) ?? '';
        $this->logger->info('['. $fullPath . '] ' . $requestData);

        // 记录请求完成
        $this->logger->info(sprintf(
            '[%s] [Method: %s] [Client IP: %s] [Status: %d] [Duration: %sms]',
            $fullPath,
            $method,
            $clientIP,
            $statusCode,
            $duration
        ));
        
        return $response;
    }

    private function maskSensitiveFields(array $data): array
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = $this->maskSensitiveFields($value);
                continue;
            }
            if (is_string($key) && in_array(strtolower($key), self::SENSITIVE_KEYS, true)) {
                $value = '******';
            }
        }
        unset($value);
        return $data;
    }
}
