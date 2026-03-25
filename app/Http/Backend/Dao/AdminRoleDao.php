<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class AdminRoleDao
{
    public static function list(array $request): array
    {
        $query = Db::table('syn_roles')
            ->select('id', 'name', 'description', 'status', 'created_at', 'updated_at')
            ->orderBy('id', 'DESC');

        if (!empty($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%');
        }
        if (($request['status'] ?? 0) > 0) {
            $query->where('status', $request['status']);
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
        $row = Db::table('syn_roles')
            ->select('id', 'name', 'description', 'status', 'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '角色不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        $affectedRows = Db::table('syn_roles')->insert([
            'name' => $request['name'],
            'description' => $request['description'],
            'status' => $request['status'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建角色失败'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        $affectedRows = Db::table('syn_roles')
            ->where('id', $request['id'])
            ->update([
                'name' => $request['name'],
                'description' => $request['description'],
                'status' => $request['status'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新角色失败'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $affectedRows = Db::table('syn_roles')
            ->where('id', $request['id'])
            ->update(['status' => $request['status']]);

        if ($affectedRows == 0) {
            return ['error' => '更新角色状态失败'];
        }

        return ['error' => ''];
    }
}
