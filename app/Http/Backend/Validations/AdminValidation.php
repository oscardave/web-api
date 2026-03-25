<?php
declare(strict_types=1);

namespace App\Http\Backend\Validations;

class AdminValidation
{
    public static function validate(array $request): string
    {
        // 如果是列表请求
        if (isset($request['page'])) {
            if (($request['page'] ?? 0) < 1) {
                return '页码不能小于1';
            }
            if (($request['page'] ?? 0) > 10000) {
                return '页码不能大于10000';
            }
            if (($request['pageSize'] ?? 0) < 1) {
                return '每页条数不能小于1';
            }
            if (($request['pageSize'] ?? 0) > 100) {
                return '每页条数不能大于100';
            }
            if (isset($request['status']) && $request['status'] < 0 || $request['status'] > 1) {
                return '状态值不合法';
            }
        }

        // 通用字段验证
        if (isset($request['name'])) {
            if (strlen($request['name']) > 50) {
                return '用户名长度不能超过50';
            }
            // 用户名不能以 test 开头（不区分大小写）
            if (stripos($request['name'], 'test') === 0) {
                return '用户名称不能以 test 开头';
            }
        }
        if (isset($request['mail']) && $request['mail'] !== '') {
            if (strlen($request['mail']) > 100) {
                return '邮箱长度不能超过100';
            }
            // 如果提供了邮箱，验证格式
            if (!filter_var($request['mail'], FILTER_VALIDATE_EMAIL)) {
                return '邮箱格式不正确';
            }
        }
        if (isset($request['phone']) && $request['phone'] !== '') {
            if (strlen($request['phone']) > 20) {
                return '手机号长度不能超过20';
            }
            // 如果提供了手机号，验证格式
            if (!preg_match('/^1[3-9]\d{9}$/', $request['phone'])) {
                return '手机号格式不正确';
            }
        }
        if (isset($request['role_id'])) {
            if ($request['role_id'] < 0) {
                return '角色ID不能小于0';
            }
            if ($request['role_id'] > 99999) {
                return '角色ID不能大于99999';
            }
        }

        // 创建/更新请求
        if (isset($request['status']) && !isset($request['page'])) {
            if ($request['status'] < 0 || $request['status'] > 1) {
                return '状态值不合法';
            }
        }
        
        // 创建请求：密码必填
        if (isset($request['name']) && !isset($request['id']) && !isset($request['page'])) {
            if (empty($request['password'])) {
                return '密码不能为空';
            }
            if (strlen($request['password']) < 8) {
                return '密码长度不能小于8';
            }
            if ($request['password'] !== ($request['confirm_password'] ?? '')) {
                return '密码和确认密码不一致';
            }
        }
        
        // 更新请求：如果提供了密码，则验证
        if (isset($request['id']) && isset($request['password']) && $request['password'] !== '') {
            if (strlen($request['password']) < 8) {
                return '密码长度不能小于8';
            }
            if ($request['password'] !== ($request['confirm_password'] ?? '')) {
                return '密码和确认密码不一致';
            }
        }

        // 修改密码请求
        if (isset($request['new_password'])) {
            if (strlen($request['new_password']) < 8) {
                return '新密码长度不能小于8';
            }
            if ($request['new_password'] !== ($request['confirm_password'] ?? '')) {
                return '新密码和确认密码不一致';
            }
        }

        // 修改状态请求
        if (isset($request['status']) && !isset($request['name']) && !isset($request['mail'])) {
            if ($request['status'] <= 0 || $request['status'] > 2) {
                return '状态值不合法';
            }
        }

        return '';
    }
}
