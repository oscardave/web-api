<?php
declare(strict_types=1);

namespace App\Http\Backend\Validations;

class UserEnsureValidation
{
    public static function validate(array $request): string
    {
        if (array_key_exists('points_to_retrieve', $request)) {
            $v = (int)($request['points_to_retrieve'] ?? -1);
            if ($v < 0) {
                return '申请取回金额不能为负数';
            }
        }
        return '';
    }
}
