<?php
declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Context\ApplicationContext;
use App\Http\Frontend\Dao\Index as IndexDao;
use App\Common\Utils;


#[Controller(prefix: "api/v1/login")]
class LoginController extends FrontendController
{
    /**
     * 用户登录（支持账号/靓号/手机号/邮箱 + 密码）
     * 入参：identifier（必填）、password（必填）、device_id、device_name（可选）
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "login")]
    public function login(RequestInterface $request): mixed
    {
        $data = $request->all();

        $data['request_ip'] = Utils::clientIPByPSR($request);
        // 始终以服务端配置为准，覆盖客户端 home_server（与 Synapse server_name 对齐）
        $data['home_server'] = ApplicationContext::getContainer()->get(ConfigInterface::class)->get('domains.default_home_server', 'im-sq01.bleiworc.xyz');

        // 兼容旧版：phone/email 映射为 identifier
        if (empty($data['identifier']) && !empty($data['phone'])) {
            $data['identifier'] = $data['phone'];
        } elseif (empty($data['identifier']) && !empty($data['email'])) {
            $data['identifier'] = $data['email'];
        }

        $result = IndexDao::login($data);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        return self::jsonArray($result['data']);
    }

    /**
     * 忘记密码
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "forgot-password")]
    public function forgotPassword(RequestInterface $request): mixed
    {
        // TODO: 实现忘记密码逻辑
        return self::jsonOk();
    }

    /**
     * 退出登录（单设备）
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "logout")]
    public function logout(RequestInterface $request): mixed
    {
        // 从 Header 获取 access_token
        $accessToken = $request->header('authorization', '');
        
        // 去掉可能的 Bearer 前缀
        if (strpos($accessToken, 'Bearer ') === 0) {
            $accessToken = substr($accessToken, 7);
        }
        
        if (empty($accessToken)) {
            return self::jsonErr('Access token is required');
        }
        
        $result = IndexDao::logout(['access_token' => $accessToken]);
        
        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }
        
        // 返回空对象，符合 Synapse 标准
        return self::jsonArray([]);
    }

    /**
     * 退出所有设备
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "logout-all")]
    public function logoutAll(RequestInterface $request): mixed
    {
        // 从 Header 获取 access_token
        $accessToken = $request->header('authorization', '');
        
        // 去掉可能的 Bearer 前缀
        if (strpos($accessToken, 'Bearer ') === 0) {
            $accessToken = substr($accessToken, 7);
        }
        
        if (empty($accessToken)) {
            return self::jsonErr('Access token is required');
        }
        
        $result = IndexDao::logoutAll(['access_token' => $accessToken]);
        
        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }
        
        // 返回空对象，符合 Synapse 标准
        return self::jsonArray([]);
    }
}