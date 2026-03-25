<?php
declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Frontend\Dao\Register;
use App\Http\Frontend\Dao\Index as IndexDao;
use App\Common\Utils;


#[Controller(prefix: "api/v1/register")]
class RegisterController extends FrontendController
{
    /**
     * @var array|\string[][][]
     */
    protected static array $validations = [
        'sms' => [
            'rules' => [
                'action' => 'required',
                'mobile' => 'required',
            ],
            'messages' => [
                'action.required' => '缺少必要字段: action',
                'mobile.required' => '缺少手机号码',
            ],
        ],
        'reg' => [
            'rules' => [
                'account' => 'required',
                'invite' => 'required',
                'code' => 'required',
                'password' => 'required',
            ],
            'messages' => [
                'password.required' => '必须输入登录密码',
                'code.required' => '必须输入验证码',
                'invite.required' => '必须输入邀请码',
                'account.required' => '必须输入账号',
            ],
        ]
    ];

    /**
     *
     * @param RequestInterface $request
     * @return array
     */
    #[PostMapping(path: "reg")]
    public function reg(RequestInterface $request): mixed
    {
        $data = $request->all();
//
        if ( empty($data["account"]) || empty($data["password"]) ) {
            return self::jsonErr(Utils::ChangeMessage("参数错误", $request));
        }

        $pattern = '/^[a-zA-Z0-9]+$/';
        if (!preg_match($pattern, $data["account"])) {
            return self::jsonErr(Utils::ChangeMessage("账号格式错误", $request));
        }
        if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $data["account"])) {
            return self::jsonErr(Utils::ChangeMessage("账号不能包含中文", $request));
        }
        $len = strlen($data["account"]);
        if ($len < 6 || $len > 16) {
            return self::jsonErr(Utils::ChangeMessage("输入6-16位用户名", $request));
        }
        $returnData = [];
        if ($errMessage = Register::reg($request, $data, $returnData)) {
            return self::jsonErr(Utils::ChangeMessage($errMessage, $request));
        }

        return self::jsonArray($returnData);
    }

    /**
     * 检查用户名称或手机号是否已注册（注册页输入框实时校验）
     * 入参：keyword（用户名称或手机号），后端根据格式自动判断并查询。
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "check-available")]
    public function checkAvailable(RequestInterface $request): mixed
    {
        $keyword = trim((string)($request->input('keyword', '')));

        $result = IndexDao::checkUsernameOrPhoneExists($keyword);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        return self::jsonArray([
            'exists' => $result['exists'],
            'type' => $result['type'],
        ]);
    }

    /**
     *
     * @return array
     */
    #[GetMapping(path: "is_reg_off")]
    public function isRegisterOff(): mixed
    {
        return self::jsonArray(['reg_off' => Register::isRegisterOff()]);
    }

    /**
     *
     * @param RequestInterface $request
     * @return array
     */
    #[GetMapping(path: "sms")]
    public function sms(RequestInterface $request): mixed
    {
        $data = $request->all();
        $errMessage = $this->validate($data, 'sms');
        if ($errMessage) {
            return self::jsonErr($errMessage);
        }
        $errMessage = Register::sendSMS($data);
        if ($errMessage) {
            return self::jsonErr($errMessage);
        }

        return self::jsonOk($errMessage);
    }

    /**
     * 找回密码 - 输入账号
     *
     * @param RequestInterface $request
     * @return array
     */
    #[PostMapping(path: "find-password")]
    public function findPassword(RequestInterface $request): mixed
    {
        // TODO: 实现找回密码第一步逻辑
        return self::jsonOk();
    }

    /**
     * 发送找回密码验证码
     *
     * @param RequestInterface $request
     * @return array
     */
    #[PostMapping(path: "send-password-reset-code")]
    public function sendPasswordResetCode(RequestInterface $request): mixed
    {
        // TODO: 实现发送找回密码验证码逻辑
        return self::jsonOk();
    }

    /**
     * 验证找回密码验证码
     *
     * @param RequestInterface $request
     * @return array
     */
    #[PostMapping(path: "verify-password-reset-code")]
    public function verifyPasswordResetCode(RequestInterface $request): mixed
    {
        // TODO: 实现验证找回密码验证码逻辑
        return self::jsonOk();
    }

    /**
     * 重置密码
     *
     * @param RequestInterface $request
     * @return array
     */
    #[PostMapping(path: "reset-password")]
    public function resetPassword(RequestInterface $request): mixed
    {
        // TODO: 实现重置密码逻辑
        return self::jsonOk();
    }

    /**
     * 检查短信功能是否关闭
     *
     * @return array
     */
    #[GetMapping(path: "is_sms_off")]
    public function isSmsOff(): mixed
    {
        return self::jsonArray(['sms_off' => Register::isSmsOff()]);
    }
}