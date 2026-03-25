<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;
use App\Http\Backend\Utils\BackendUtils;

class AdminDao
{
    public static function list(array $request): array
    {
        $query = Db::table('syn_admins as a')
            ->join('syn_roles as r', 'a.role_id', '=', 'r.id')
            ->select('a.id', 'a.name', 'a.mail', 'a.full_name', 'a.phone', 'a.avatar_url', 
                     'a.role_id', 'a.status', 'a.last_login_at', 'a.login_count', 
                     'a.last_login_ip', 'a.remark', 'r.description as role_name')
            ->orderBy('a.id', 'DESC');

        if (!empty($request['name'])) {
            $query->where('a.name', 'like', '%' . $request['name'] . '%');
        }
        if (!empty($request['mail'])) {
            $query->where('a.mail', 'like', '%' . $request['mail'] . '%');
        }
        if (!empty($request['phone'])) {
            $query->where('a.phone', 'like', '%' . $request['phone'] . '%');
        }
        if (($request['role_id'] ?? 0) > 0) {
            $query->where('a.role_id', $request['role_id']);
        }
        if (($request['status'] ?? 0) > 0) {
            $query->where('a.status', $request['status']);
        }

        $total = $query->count();
        $offset = ($request['page'] - 1) * $request['pageSize'];
        $rows = $query->limit($request['pageSize'])->offset($offset)->get()->toArray();

        return [
            'error' => '',
            'data' => [
                'records' => $rows,
                'total' => $total,
            ],
        ];
    }

    public static function get(array $request): array
    {
        $row = Db::table('syn_admins')
            ->select('id', 'name', 'mail', 'full_name', 'phone', 'avatar_url', 
                     'role_id', 'status', 'last_login_at', 'login_count', 'remark')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '管理员不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        // 密码必填验证
        if (empty($request['password'])) {
            return ['error' => '密码不能为空'];
        }
        
        // 加密密码
        $encryptedPassword = BackendUtils::hashAdminPassword($request['password']);
        
        $id = Db::table('syn_admins')->insertGetId([
            'name' => $request['name'],
            'mail' => $request['mail'] ?? '',
            'phone' => $request['phone'] ?? '',
            'role_id' => $request['role_id'],
            'status' => $request['status'],
            'password' => $encryptedPassword,
            'remark' => $request['remark'] ?? '',
        ]);

        if ($id == 0) {
            return ['error' => '创建失败'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        $updateData = [
            'name' => $request['name'],
            'mail' => $request['mail'] ?? '',
            'phone' => $request['phone'] ?? '',
            'role_id' => $request['role_id'],
            'status' => $request['status'],
            'remark' => $request['remark'] ?? '',
        ];
        
        // 如果提供了密码，则更新密码
        if (!empty($request['password'])) {
            $updateData['password'] = BackendUtils::hashAdminPassword($request['password']);
        }
        
        $affectedRows = Db::table('syn_admins')
            ->where('id', $request['id'])
            ->update($updateData);

        if ($affectedRows == 0) {
            return ['error' => '更新失败'];
        }

        return ['error' => ''];
    }

    public static function changePassword(array $request): array
    {
        // 加密密码
        $encryptedPassword = BackendUtils::hashAdminPassword($request['new_password']);

        $affectedRows = Db::table('syn_admins')
            ->where('id', $request['id'])
            ->update(['password' => $encryptedPassword]);

        if ($affectedRows == 0) {
            return ['error' => '更新密码失败'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $affectedRows = Db::table('syn_admins')
            ->where('id', $request['id'])
            ->update(['status' => $request['status']]);

        if ($affectedRows == 0) {
            return ['error' => '更新状态失败'];
        }

        return ['error' => ''];
    }
}
