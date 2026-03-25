<?php
declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\DbConnection\Db;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Context\ApplicationContext;
use App\Common\Utils;
use App\Http\Frontend\Dao\Index as IndexDao;



#[Controller(prefix: "/api/v1/index")]
class IndexController extends FrontendController
{

    /**
     * 默认首页
     *
     * @param RequestInterface $request
     * @return object
     */
    #[GetMapping(path: "index")]
    public function index(RequestInterface $request): object
    {
        return self::jsonOk("Hello World");
    }

    /**
     * 默认首页
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "ping")]
    public function ping(RequestInterface $request): mixed
    {
        return self::jsonOk();
    }

    /**
     * 用户注册（支持手机号和邮箱）
     *
     * @param RequestInterface $request
     * @return object
     */
    #[PostMapping(path: "register")]
    public function registerUser(RequestInterface $request): object
    {
        $data = $request->all();
        
        // 在 Controller 层设置必要参数
        $data['request_ip'] = Utils::clientIPByPSR($request);
        // 始终以服务端配置为准，覆盖客户端 home_server（与 Synapse server_name 对齐）
        $data['home_server'] = ApplicationContext::getContainer()->get(ConfigInterface::class)->get('domains.default_home_server', 'im-sq01.bleiworc.xyz');
        
        // 判断是手机号还是邮箱注册
        if (!empty($data['phone'])) {
            $data['contact_type'] = 'phone';
            $data['contact_value'] = $data['phone'];
        } elseif (!empty($data['email'])) {
            $data['contact_type'] = 'mail';
            $data['contact_value'] = $data['email'];
        } else {
            return self::jsonErr('请提供手机号或邮箱');
        }

        if (trim((string)($data['username'] ?? '')) === '') {
            return self::jsonErr('请提供用户名');
        }

        $result = IndexDao::register($data);
        
        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }
        
        // 返回 Synapse 格式的数据
        return self::jsonArray($result['data']);
    }

    /**
     * 获取手机验证码
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "phone-verify-code")]
    public function sendPhoneVerifyCode(RequestInterface $request): mixed
    {
        $data = $request->all();
        
        // 在 Controller 层设置必要参数
        $data['verify_type'] = $data['verify_type'] ?? 'register'; // 默认为注册类型
        $data['request_ip'] = Utils::clientIPByPSR($request); // 获取客户端IP
        $data['user_id'] = $data['user_id'] ?? 0; // 默认用户ID为0
        
        $errMessage = IndexDao::sendPhoneVerifyCode($data);
        
        if ($errMessage !== '') {
            return self::jsonErr($errMessage);
        }
        
        return self::jsonOk('验证码发送成功');
    }

    /**
     * 获取邮箱验证码
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "email-verify-code")]
    public function sendEmailVerifyCode(RequestInterface $request): mixed
    {
        $data = $request->all();
        
        // 在 Controller 层设置必要参数
        $data['verify_type'] = $data['verify_type'] ?? 'register'; // 默认为注册类型
        $data['request_ip'] = Utils::clientIPByPSR($request); // 获取客户端IP
        $data['user_id'] = $data['user_id'] ?? 0; // 默认用户ID为0
        
        $errMessage = IndexDao::sendEmailVerifyCode($data);
        
        if ($errMessage !== '') {
            return self::jsonErr($errMessage);
        }
        
        return self::jsonOk('验证码发送成功');
    }

    /**
     * 重置密码（忘记密码流程的第三步）
     * 入参支持 phone+verify_code+new_password 或 email+verify_code+new_password
     */
    #[PostMapping(path: "reset-password")]
    public function resetPassword(RequestInterface $request): mixed
    {
        $data = $request->all();
        if (!empty($data['phone'])) {
            $data['contact_type'] = 'phone';
            $data['contact_value'] = trim($data['phone']);
        } elseif (!empty($data['email'])) {
            $data['contact_type'] = 'mail';
            $data['contact_value'] = strtolower(trim($data['email']));
        } elseif (empty($data['contact_type']) || empty($data['contact_value'])) {
            return self::jsonErr('请提供手机号或邮箱');
        }
        $errMessage = IndexDao::resetPassword($data);
        if ($errMessage !== '') {
            return self::jsonErr($errMessage);
        }
        return self::jsonOk('密码重置成功');
    }
    /**
     *
     * @param RequestInterface $request
     * @return object
     */
    #[GetMapping(path: "version")]
    public function circleNoticeList(RequestInterface $request): object
    {
        $type = $request->input("type");
        $res = Db::table("ext_app_versions")->where("client_type", $type)->where("version_status", 1)->orderBy("id", "desc")->first();
        return self::jsonResult((array)$res);
    }
}
