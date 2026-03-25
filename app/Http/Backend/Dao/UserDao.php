<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class UserDao
{
    public static function list(array $request): array
    {
        $query = Db::table('users_v')
            ->select('name', 'creation_ts', 'admin', 'is_guest', 'deactivated', 'shadow_banned', 
                     'consent_ts', 'approved', 'locked', 'suspended', 'nickname', 'avatar_url', 
                     'device_id', 'last_seen', 'device_name', 'ip', 'user_agent')
            ->orderBy('creation_ts', 'DESC');

        if (!empty($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%');
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
        $row = Db::table('users_v')
            ->select('name', 'creation_ts', 'admin', 'is_guest', 'deactivated', 'shadow_banned', 
                     'consent_ts', 'approved', 'locked', 'suspended', 'nickname', 'avatar_url', 
                     'device_id', 'last_seen', 'device_name', 'ip', 'user_agent')
            ->where('name', $request['name'])
            ->first();

        if (!$row) {
            return ['error' => '用户不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function setAdmin(array $request): array
    {
        // 先检查用户是否存在
        $user = Db::table('users')->select('admin')->where('name', $request['name'])->first();
        if (!$user) {
            return ['error' => '用户不存在'];
        }

        $toStatus = (int)$request['status'];
        if ($user->admin == $toStatus) {
            return ['error' => '用户状态未发生变化'];
        }

        $affectedRows = Db::table('users')
            ->where('name', $request['name'])
            ->update(['admin' => $toStatus]);

        if ($affectedRows == 0) {
            return ['error' => '管理员状态更新失败'];
        }

        return ['error' => ''];
    }
}
