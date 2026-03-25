<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class HelpCategoryDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_help_categories')
            ->select('id', 'name', 'code', 'remark', 'status', 'sort_order', 'created_at', 'updated_at')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'DESC');

        if (!empty($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%');
        }
        if (!empty($request['code'])) {
            $query->where('code', 'like', '%' . $request['code'] . '%');
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
        $row = Db::table('ext_help_categories')
            ->select('id', 'name', 'code', 'remark', 'status', 'sort_order', 'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '帮助分类记录不存在，请检查ID是否正确', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        $affectedRows = Db::table('ext_help_categories')->insert([
            'name' => $request['name'],
            'code' => $request['code'],
            'remark' => $request['remark'],
            'status' => $request['status'],
            'sort_order' => $request['sort_order'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建帮助分类失败，请检查数据是否有效或编码是否重复'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        $affectedRows = Db::table('ext_help_categories')
            ->where('id', $request['id'])
            ->update([
                'name' => $request['name'],
                'code' => $request['code'],
                'remark' => $request['remark'],
                'status' => $request['status'],
                'sort_order' => $request['sort_order'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新帮助分类失败，记录可能不存在或数据未变更'];
        }

        return ['error' => ''];
    }

    public static function del(array $request): array
    {
        $affectedRows = Db::table('ext_help_categories')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除帮助分类失败，记录不存在或已被删除'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $affectedRows = Db::table('ext_help_categories')
            ->where('id', $request['id'])
            ->update(['status' => $request['status']]);

        if ($affectedRows == 0) {
            return ['error' => '修改帮助分类状态失败，记录不存在或状态值无效'];
        }

        return ['error' => ''];
    }
}
