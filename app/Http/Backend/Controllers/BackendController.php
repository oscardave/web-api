<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

/**
 * Backend 控制器基类
 * 提供统一的返回格式，与 C++ 版本保持一致
 */
class BackendController
{
    /**
     * 成功返回（带数据）
     * 对应 C++ 的 Response::data()
     * 
     * @param mixed $data
     * @return Psr7ResponseInterface
     */
    protected function responseData($data): Psr7ResponseInterface
    {
        $response = ApplicationContext::getContainer()->get(ResponseInterface::class);
        return $response->json([
            'success' => true,
            'message' => null,
            'data' => $data,
        ]);
    }

    /**
     * 错误返回
     * 对应 C++ 的 Response::error()
     * 
     * @param string $message
     * @return Psr7ResponseInterface
     */
    protected function responseError(string $message): Psr7ResponseInterface
    {
        $response = ApplicationContext::getContainer()->get(ResponseInterface::class);
        return $response->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ]);
    }

    /**
     * 成功返回（不带数据）
     * 对应 C++ 的 Response::success()
     * 
     * @param string $message
     * @return Psr7ResponseInterface
     */
    protected function responseSuccess(string $message = ''): Psr7ResponseInterface
    {
        $response = ApplicationContext::getContainer()->get(ResponseInterface::class);
        return $response->json([
            'success' => true,
            'message' => $message,
            'data' => null,
        ]);
    }
}
