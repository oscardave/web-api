<?php
declare(strict_types=1);

namespace App\Http\Backend\Validations;

/**
 * 圈子徽章入参校验
 */
class CircleBadgeValidation
{
    public static function validateList(array $request): string
    {
        $page = (int)($request['page'] ?? 1);
        $pageSize = (int)($request['pageSize'] ?? 10);
        if ($page < 1 || $pageSize < 1 || $pageSize > 500) {
            return '分页参数无效';
        }
        return '';
    }

    public static function validateDetail(array $request): string
    {
        $id = (int)($request['id'] ?? 0);
        if ($id <= 0) {
            return 'ID无效';
        }
        return '';
    }

    public static function validateCreate(array $request): string
    {
        $name = trim((string)($request['name'] ?? ''));
        if ($name === '') {
            return '名称不能为空';
        }
        if (mb_strlen($name) > 100) {
            return '名称不能超过100字符';
        }
        $sortOrder = (int)($request['sort_order'] ?? 0);
        if ($sortOrder < 0) {
            return '排序不能为负数';
        }
        $status = (int)($request['status'] ?? 1);
        if (!in_array($status, [1, 2], true)) {
            return '状态必须为1(启用)或2(禁用)';
        }
        return '';
    }

    public static function validateUpdate(array $request): string
    {
        $id = (int)($request['id'] ?? 0);
        if ($id <= 0) {
            return 'ID无效';
        }
        return self::validateCreate($request);
    }
}
