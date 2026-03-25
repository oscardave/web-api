<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\Di\Annotation\Inject;

class CheckInternalAccess implements MiddlewareInterface
{
    private const PRIVATE_CIDRS = [
        ['start' => '10.0.0.0',     'end' => '10.255.255.255'],
        ['start' => '172.16.0.0',   'end' => '172.31.255.255'],
        ['start' => '192.168.0.0',  'end' => '192.168.255.255'],
        ['start' => '127.0.0.0',    'end' => '127.255.255.255'],
    ];

    #[Inject]
    protected HttpResponse $response;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->hasValidToken($request) || $this->isPrivateNetwork($request)) {
            return $handler->handle($request);
        }

        return $this->response->json([
            'error' => 'Forbidden',
            'message' => 'This endpoint is restricted to internal access.',
        ])->withStatus(403);
    }

    private function hasValidToken(ServerRequestInterface $request): bool
    {
        $expected = env('INTERNAL_ACCESS_TOKEN', '');
        if ($expected === '') {
            return false;
        }

        $token = $request->getHeaderLine('X-Internal-Token');

        return $token !== '' && hash_equals($expected, $token);
    }

    private function isPrivateNetwork(ServerRequestInterface $request): bool
    {
        $ip = $request->getServerParams()['remote_addr'] ?? '';
        if ($ip === '') {
            return false;
        }

        $ipLong = ip2long($ip);
        if ($ipLong === false) {
            return false;
        }

        foreach (self::PRIVATE_CIDRS as $range) {
            if ($ipLong >= ip2long($range['start']) && $ipLong <= ip2long($range['end'])) {
                return true;
            }
        }

        return false;
    }
}
