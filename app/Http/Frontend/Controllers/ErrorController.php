<?php
declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;
use Hyperf\View\RenderInterface;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Http\Middleware\Cors;

//@Middleware(Cors::class)


#[Controller(prefix: "api/v1/error")]
class ErrorController extends FrontendController
{
    /**
     *
     * @param RenderInterface $render
     * @param RequestInterface $request
     * @param string $message
     * @return Psr7ResponseInterface
     */
    #[GetMapping(path: "message")]
    public function index(RenderInterface $render, RequestInterface $request, string $message): Psr7ResponseInterface
    {
        return self::render($render, $request, ['message' => $message]);
    }
}
