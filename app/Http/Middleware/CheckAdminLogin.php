<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Hyperf\HttpServer\Contract\RequestInterface as HttpRequest;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\SessionInterface;
use App\Caches\PermissionIpsCache;
use App\Model\AdminSession;

class CheckAdminLogin implements MiddlewareInterface
{

    /**
     * 要跳过的urls
     */
    private const SKIP_PATHS = [
        '/backend/index/index', // 主页
        '/backend/index/login', // 登录
        '/backend/index/code', // code
        '/backend/index/google_auth',
        '/backend/agent_index/index', // 主页
        '/backend/agent_index/login',
        '/backend/agent_index/manage',
        '/backend/agent_index/logout',
        '/backend/agent',
        '/backend/agent_index/right',
        '/backend/agent/detail',
        '/backend/agent/detail_up',
        '/backend/agent/detail_cards',
        '/backend/agent/detail_buys',
        '/backend/agent/detail_rebates',
        '/backend/agent/detail_recharges',
        '/backend/agent/detail_withdraws',
        '/backend/agent/low_charge',
        '/backend/agent/detail_account_change',
        '/backend/agent_deposit',
        '/backend/agent_withdraw',
        '/backend/agent_orders',
    ];

    /**
     *
     * @var ContainerInterface
     */
    #[Inject]
    protected ContainerInterface $container;

    /**
     *
     * @var HttpRequest
     */
    #[Inject]
    protected HttpRequest $request;

    /**
     *
     * @var HttpResponse
     */
    #[Inject]
    protected HttpResponse $response;

    /**
     *
     * @var SessionInterface
     */
    #[Inject]
    protected SessionInterface $session;

    /**
     * FooMiddleware constructor.
     * @param ContainerInterface $container
     * @param HttpResponse $response
     * @param HttpRequest $request
     */
    public function __construct(ContainerInterface $container, HttpResponse $response, HttpRequest $request)
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
        // -- 检测权限地址
        $clientIP = $this->getRealIP($request);
        if ($clientIP == '') {
            return $this->response->redirect('/errors/限制登录!');
        }
//        if (!PermissionIpsCache::allow($clientIP)) {
//            return $this->response->redirect('/errors/缺少权限: ' . $clientIP);
//        }

        // -- 检测此url是否需要权限检查
        $currentPath = '/' . trim($this->request->getPathInfo(), '/');
        if (in_array($currentPath, self::SKIP_PATHS, true)) {
            return $handler->handle($request);
        }
        // -- 检测用户信息
        $adminId = $this->session->get('admin_id');
        $adminName = $this->session->get('admin_name');
        if (!$adminId || !$adminName) {
            return $this->response->redirect('/index/index');
        }

        // -- 检测ip权限
        $loginIP = $this->session->get('admin_login_ip');
//        if ($loginIP != $clientIP) {
//            $this->session->clear();
//            return $this->response->redirect("/errors/登录IP检测失败!登录IP:${loginIP}/当前IP:${clientIP}");
//        }

        // -- 检测登录签名
        $login = AdminSession::get($adminId);
        if (!$login) {
            return $this->response->redirect('/index/index');
        }
        if ($login->session_id != $this->session->getId()) { // 不是同一个用户登录
            return $this->response->redirect('/index/index');
        }
        $login->refresh(); // 刷新redis相关信息

        return $handler->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    private function getRealIP(ServerRequestInterface $request): string
    {
        $keys = [
            'x-forwarded-for',
            'http_x_forwarded_for',
            'x-real-ip',
        ];
        $headers = $request->getHeaders();
        foreach ($keys as $k) {
            if (isset($headers[$k])) {
                return explode(',', $headers[$k][0])[0];
            }
        }

        return $request->getServerParams()['remote_addr'] ?? '';
    }
}