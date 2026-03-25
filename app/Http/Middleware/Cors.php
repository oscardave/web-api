<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Cors implements MiddlewareInterface
{
    /** @var string[] */
    private array $allowedOrigins;

    public function __construct(ConfigInterface $config)
    {
        $raw = $config->get('cors.allowed_origins', '');
        $this->allowedOrigins = array_filter(array_map('trim', explode(',', (string) $raw)));
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = Context::get(ResponseInterface::class);

        $origin = $request->getHeaderLine('Origin');
        $matched = $this->matchOrigin($origin);

        if ($matched !== '') {
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', $matched)
                ->withHeader('Access-Control-Allow-Credentials', 'true')
                ->withHeader('Access-Control-Allow-Headers', 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization')
                ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,PATCH,OPTIONS')
                ->withHeader('Vary', 'Origin');
        }

        Context::set(ResponseInterface::class, $response);

        if ($request->getMethod() === 'OPTIONS') {
            return $response;
        }
        return $handler->handle($request);
    }

    private function matchOrigin(string $origin): string
    {
        if ($origin === '' || $this->allowedOrigins === []) {
            return '';
        }

        foreach ($this->allowedOrigins as $allowed) {
            if (strcasecmp($origin, $allowed) === 0) {
                return $origin;
            }
        }

        return '';
    }
}