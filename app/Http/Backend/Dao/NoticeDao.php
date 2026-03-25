<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class NoticeDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_notices')
            ->select('id', 'title', 'content', 'type', 'status', 'sort_order', 
                     'start_time', 'end_time', 'created_at', 'updated_at')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'DESC');

        if (!empty($request['title'])) {
            $query->where('title', 'like', '%' . $request['title'] . '%');
        }
        if (($request['type'] ?? 0) > 0) {
            $query->where('type', $request['type']);
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
        $row = Db::table('ext_notices')
            ->select('id', 'title', 'content', 'type', 'status', 'sort_order', 
                     'start_time', 'end_time', 'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '公告记录不存在，请检查ID是否正确', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        $affectedRows = Db::table('ext_notices')->insert([
            'title' => $request['title'],
            'content' => $request['content'],
            'type' => $request['type'],
            'status' => $request['status'],
            'sort_order' => $request['sort_order'],
            'start_time' => $request['start_time'],
            'end_time' => $request['end_time'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建公告失败，请检查数据是否有效'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        $affectedRows = Db::table('ext_notices')
            ->where('id', $request['id'])
            ->update([
                'title' => $request['title'],
                'content' => $request['content'],
                'type' => $request['type'],
                'status' => $request['status'],
                'sort_order' => $request['sort_order'],
                'start_time' => $request['start_time'],
                'end_time' => $request['end_time'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新公告失败，记录可能不存在或数据未变更'];
        }

        return ['error' => ''];
    }

    public static function del(array $request): array
    {
        $affectedRows = Db::table('ext_notices')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除公告失败，记录不存在或已被删除'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $affectedRows = Db::table('ext_notices')
            ->where('id', $request['id'])
            ->update(['status' => $request['status']]);

        if ($affectedRows == 0) {
            return ['error' => '修改公告状态失败，记录不存在或状态值无效'];
        }

        return ['error' => ''];
    }
}
