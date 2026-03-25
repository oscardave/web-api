<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class HelpDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_helps_v')
            ->select('id', 'category_id', 'title', 'category_name', 'content', 
                     'status', 'sort_order', 'created_at', 'updated_at')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'DESC');

        if (!empty($request['title'])) {
            $query->where('title', 'like', '%' . $request['title'] . '%');
        }
        if (($request['category_id'] ?? 0) > 0) {
            $query->where('category_id', $request['category_id']);
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
        $row = Db::table('ext_helps_v')
            ->select('id', 'category_id', 'title', 'category_name', 'content', 
                     'status', 'sort_order', 'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '帮助记录不存在，请检查ID是否正确', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        $affectedRows = Db::table('ext_helps')->insert([
            'category_id' => $request['category_id'],
            'title' => $request['title'],
            'content' => $request['content'],
            'status' => $request['status'],
            'sort_order' => $request['sort_order'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建帮助失败，请检查数据是否有效'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        $affectedRows = Db::table('ext_helps')
            ->where('id', $request['id'])
            ->update([
                'category_id' => $request['category_id'],
                'title' => $request['title'],
                'content' => $request['content'],
                'status' => $request['status'],
                'sort_order' => $request['sort_order'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新帮助失败，记录可能不存在或数据未变更'];
        }

        return ['error' => ''];
    }

    public static function del(array $request): array
    {
        $affectedRows = Db::table('ext_helps')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除帮助失败，记录不存在或已被删除'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $affectedRows = Db::table('ext_helps')
            ->where('id', $request['id'])
            ->update(['status' => $request['status']]);

        if ($affectedRows == 0) {
            return ['error' => '修改帮助状态失败，记录不存在或状态值无效'];
        }

        return ['error' => ''];
    }
}
