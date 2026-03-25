<?php
declare(strict_types=1);

namespace App\Http\Backend\Validations;

/**
 * Index 验证类
 * 对应 C++ 的 IndexValidation
 */
class IndexValidation
{
    // 用户名称正则：必须是英文字母开头，只能有字母和数字
    private const NAME_REGEX = '/^[a-zA-Z][a-zA-Z0-9]*$/';
    
    // 验证码正则：必须是4位数字
    private const VERIFY_CODE_REGEX = '/^[0-9]{4}$/';

    /**
     * 验证登录请求
     * 对应 C++ 的 IndexValidation::validate()
     * 
     * @param array $request
     * @return string 空字符串表示验证通过，非空字符串表示错误信息
     */
    public static function validate(array $request): string
    {
        $name = $request['name'] ?? '';
        $password = $request['password'] ?? '';
        $verifyCode = $request['verifyCode'] ?? '';

        // 用户名称必须是 6-20 位
        if (strlen($name) < 6 || strlen($name) > 20) {
            return '用户名称必须是 6-20 位';
        }
        
        // 用户名称必须是英文字母开头只能有字母和数字
        if (!preg_match(self::NAME_REGEX, $name)) {
            return '用户名称必须是英文字母开头只能有字母和数字';
        }

        // 密码必须是 6-20 位
        if (strlen($password) < 6 || strlen($password) > 20) {
            return '密码必须是 6-20 位';
        }

        // 验证码必须是4位数字
        if (strlen($verifyCode) != 4) {
            return '验证码必须是4位数字';
        } elseif (!preg_match(self::VERIFY_CODE_REGEX, $verifyCode)) {
            return '验证码必须是数字';
        }

        return '';
    }
}
