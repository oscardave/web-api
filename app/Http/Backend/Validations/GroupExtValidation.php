<?php
declare(strict_types=1);

namespace App\Http\Backend\Validations;

class GroupExtValidation
{
    private const ALLOWED_STATUSES = ['active', 'banned'];
    private const ALLOWED_RESTRICTION_TYPES = ['unlimited', 'restricted'];

    public static function validate(array $request): string
    {
        return '';
    }

    public static function validateChangeStatus(array $request): string
    {
        if (empty($request['group_id']) || trim((string)$request['group_id']) === '') {
            return '群组ID不能为空';
        }
        if (empty($request['status']) || trim((string)$request['status']) === '') {
            return '状态不能为空';
        }
        if (!in_array($request['status'], self::ALLOWED_STATUSES, true)) {
            return '状态必须是 active 或 banned';
        }
        return '';
    }

    public static function validateDissolve(array $request): string
    {
        if (empty($request['group_id']) || trim((string)$request['group_id']) === '') {
            return '群组ID不能为空';
        }
        return '';
    }

    public static function validateTransferOwnership(array $request): string
    {
        if (empty($request['group_id']) || trim((string)$request['group_id']) === '') {
            return '群组ID不能为空';
        }
        if (empty($request['new_owner_id']) || trim((string)$request['new_owner_id']) === '') {
            return '新群主ID不能为空';
        }
        return '';
    }

    public static function validateUpdateInfo(array $request): string
    {
        if (empty($request['group_id']) || trim((string)$request['group_id']) === '') {
            return '群组ID不能为空';
        }
        if (isset($request['group_name']) && mb_strlen((string)$request['group_name']) > 200) {
            return '群名称长度不能超过200字符';
        }
        if (isset($request['max_members'])) {
            $max = (int)$request['max_members'];
            if ($max < 1 || $max > 12000) {
                return '群人数上限须在 1 到 12000 之间';
            }
        }
        if (isset($request['description']) && mb_strlen((string)$request['description']) > 2000) {
            return '群描述长度不能超过2000字符';
        }
        return '';
    }

    public static function validateUpdateRestriction(array $request): string
    {
        if (empty($request['group_id']) || trim((string)$request['group_id']) === '') {
            return '群组ID不能为空';
        }
        if (!in_array($request['restriction_type'] ?? '', self::ALLOWED_RESTRICTION_TYPES, true)) {
            return '群限制类型必须是 unlimited 或 restricted';
        }
        return '';
    }
}
