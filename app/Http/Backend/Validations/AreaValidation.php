<?php
declare(strict_types=1);

namespace App\Http\Backend\Validations;

class AreaValidation
{
    public static function validate(array $request): string
    {
        if (($request['page'] ?? 0) < 1) {
            return '页码必须大于0';
        }
        if (($request['pageSize'] ?? 0) < 1 || ($request['pageSize'] ?? 0) > 100) {
            return '每页大小必须在1-100之间';
        }
        if (isset($request['status']) && ($request['status'] < 0 || $request['status'] > 2)) {
            return '状态值无效';
        }
        return '';
    }
}
