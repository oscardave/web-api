<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class AdminMenuDao
{
    public static function list(array $request): array
    {
        $query = Db::table('syn_menus')
            ->select('id', 'name', 'path', 'icon', 'sort', 'type', 'status', 'created_at', 'updated_at', 'remark')
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
        $row = Db::table('syn_menus')
            ->select('id', 'name', 'path', 'icon', 'sort', 'type', 'status', 'created_at', 'updated_at', 'remark')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '菜单不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        $affectedRows = Db::table('syn_menus')->insert([
            'name' => $request['name'],
            'path' => $request['path'],
            'icon' => $request['icon'],
            'sort' => $request['sort'],
            'type' => $request['type'],
            'status' => $request['status'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建菜单失败'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        $affectedRows = Db::table('syn_menus')
            ->where('id', $request['id'])
            ->update([
                'name' => $request['name'],
                'path' => $request['path'],
                'icon' => $request['icon'],
                'sort' => $request['sort'],
                'type' => $request['type'],
                'status' => $request['status'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新菜单失败'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $affectedRows = Db::table('syn_menus')
            ->where('id', $request['id'])
            ->update(['status' => $request['status']]);

        if ($affectedRows == 0) {
            return ['error' => '更新菜单状态失败'];
        }

        return ['error' => ''];
    }
}
