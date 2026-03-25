<?php
declare(strict_types=1);

namespace App\Http\Backend\Validations;

class CircleValidation
{
    public static function validate(array $request): string
    {
        return '';
    }

    /**
     * 校验圈子设置更新请求
     */
    public static function validateUpdateSettings(array $request): string
    {
        if (empty($request['circle_id']) || trim((string)$request['circle_id']) === '') {
            return '圈子ID不能为空';
        }

        $count = (int)($request['invitation_code_count'] ?? 0);
        if ($count < 1 || $count > 20) {
            return '邀请码数量必须在 1 到 20 之间';
        }

        return '';
    }

    /**
     * 校验圈子信息更新请求
     */
    public static function validateUpdateInfo(array $request): string
    {
        if (empty($request['circle_id']) || trim((string)$request['circle_id']) === '') {
            return '圈子ID不能为空';
        }
        $name = trim((string)($request['circle_name'] ?? ''));
        if ($name === '') {
            return '圈子名称不能为空';
        }
        if (mb_strlen($name) > 200) {
            return '圈子名称不能超过200个字符';
        }
        $limit = (int)($request['member_limit'] ?? 0);
        if (!in_array($limit, [5000, 10000, 12000], true)) {
            return '成员上限只能是 5000、10000、12000 之一';
        }
        return '';
    }
}
