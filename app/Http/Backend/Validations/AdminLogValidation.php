<?php
declare(strict_types=1);

namespace App\Http\Backend\Validations;

/**
 * AdminLog 验证类
 * 对应 C++ 的 AdminLogValidation
 */
class AdminLogValidation
{
    /**
     * 验证列表请求
     */
    public static function validate(array $request): string
    {
        // C++ 版本返回空字符串，表示无验证规则
        return '';
    }
}
