<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Context\ApplicationContext;
use App\Http\Backend\Dao\IndexDao;
use App\Http\Backend\Validations\IndexValidation;

/**
 * Index 控制器
 * 对应 C++ 的 ht::Index
 * 路由前缀：/ht/v1/index
 */
#[Controller(prefix: "/ht/v1/index")]
class IndexController extends BackendController
{
    /**
     * 首页
     * 路由：GET /ht/v1/index/
     * 对应 C++ 的 Index::index()
     */
    #[GetMapping(path: "")]
    public function index(RequestInterface $request)
    {
        return $this->responseData('index');
    }

    /**
     * 全局配置
     * 路由：GET /ht/v1/index/config
     * 返回管理后台使用的全局配置项，如静态资源域名、IM 域名等。
     */
    #[GetMapping(path: "config")]
    public function config(RequestInterface $request)
    {
        $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
        $data = [
            'static_domain' => (string) $config->get('domains.static_domain', ''),
            'im_domain' => (string) $config->get('domains.im_domain', ''),
        ];
        return $this->responseData($data);
    }

    /**
     * 测试接口
     * 路由：GET /ht/v1/index/test
     * 对应 C++ 的 Index::test()
     */
    #[GetMapping(path: "test")]
    public function test(RequestInterface $request)
    {
        return $this->responseData('test');
    }

    /**
     * 登录
     * 路由：POST /ht/v1/index/login
     * 对应 C++ 的 Index::login()
     */
    #[PostMapping(path: "login")]
    public function login(RequestInterface $request)
    {
        try {
            $data = $request->all();
            
            // 获取客户端 IP
            $clientIP = $request->header('X-Real-IP', '127.0.0.1');
            
            // 构建请求数据
            $indexRequest = [
                'name' => $data['username'] ?? '',
                'password' => $data['password'] ?? '',
                'verifyCode' => $data['verifyCode'] ?? '',
                'clientIP' => $clientIP,
            ];

            // 验证
            $validationError = IndexValidation::validate($indexRequest);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            // DAO 调用
            $result = IndexDao::login($indexRequest);
            if ($result['error'] !== '' || $result['data'] === null) {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * 登出
     * 路由：POST /ht/v1/index/logout
     * 对应 C++ 的 Index::logout()
     */
    #[PostMapping(path: "logout")]
    public function logout(RequestInterface $request)
    {
        // C++ 版本返回：{"success": true, "message": null, "data": {"data": ""}}
        return $this->responseData(['data' => '']);
    }

    /**
     * 刷新 Token
     * 路由：POST /ht/v1/index/refresh_token
     * 对应 C++ 的 Index::refreshToken()
     */
    #[PostMapping(path: "refresh_token")]
    public function refreshToken(RequestInterface $request)
    {
        try {
            $data = $request->all();
            
            if (empty($data)) {
                return $this->responseError('request is null');
            }

            // 获取客户端 IP
            $clientIP = $request->header('X-Real-IP', '127.0.0.1');

            $refreshTokenRequest = [
                'oldToken' => $data['old_token'] ?? '',
                'clientIP' => $clientIP,
            ];

            // DAO 调用
            $result = IndexDao::refreshToken($refreshTokenRequest);
            if ($result['error'] !== '' || $result['data'] === null) {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
