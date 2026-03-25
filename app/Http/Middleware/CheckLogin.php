<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Redis\Redis;

class CheckLogin implements MiddlewareInterface
{

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var HttpResponse
     */
    protected HttpResponse $response;

    /**
     * @var Redis
     */
    protected Redis $redis;

    /**
     * FooMiddleware constructor.
     * @param ContainerInterface $container
     * @param HttpResponse $response
     * @param RequestInterface $request
     * @return void
     */
    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // $isValidToken = true;
        // if ($isValidToken) {
        //     return $handler->handle($request);
        // }
        return $this->response->json(
            [
                'code' => -1,
                'data' => [
                    'error' => '中间里验证token无效，阻止继续向下执行',
                ],
            ]
        );
    }
}