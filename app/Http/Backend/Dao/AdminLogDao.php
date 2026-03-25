<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

/**
 * AdminLog Dao
 * 对应 C++ 的 AdminLogDao
 */
class AdminLogDao
{
    /**
     * 列表
     */
    public static function list(array $request): array
    {
        $query = Db::table('syn_admin_logs')
            ->select('id', 'admin_id', 'action', 'ip', 'created_at', 'remark', 'path', 'menu_id', 'method')
            ->orderBy('id', 'DESC');

        if (($request['admin_id'] ?? 0) > 0) {
            $query->where('admin_id', $request['admin_id']);
        }
        if (!empty($request['action'])) {
            $query->where('action', 'like', '%' . $request['action'] . '%');
        }
        if (!empty($request['ip'])) {
            $query->where('ip', 'like', '%' . $request['ip'] . '%');
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

    /**
     * 详情
     */
    public static function get(array $request): array
    {
        $row = Db::table('syn_admin_logs')
            ->select('id', 'admin_id', 'action', 'ip', 'created_at', 'remark', 'path', 'menu_id', 'method')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '日志不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    /**
     * 删除
     */
    public static function del(array $request): array
    {
        $affectedRows = Db::table('syn_admin_logs')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除日志失败'];
        }

        return ['error' => ''];
    }

    /**
     * 插入一条日志（供中间件等调用）
     *
     * @param array $row ['admin_id' => int, 'path' => string, 'menu_id' => int, 'action' => string, 'method' => string, 'ip' => string, 'remark' => string]
     * @return array ['error' => string]
     */
    public static function insert(array $row): array
    {
        try {
            Db::table('syn_admin_logs')->insert([
                'admin_id' => (int)($row['admin_id'] ?? 0),
                'path' => (string)($row['path'] ?? ''),
                'menu_id' => (int)($row['menu_id'] ?? 0),
                'action' => (string)($row['action'] ?? ''),
                'method' => (string)($row['method'] ?? ''),
                'ip' => (string)($row['ip'] ?? ''),
                'remark' => (string)($row['remark'] ?? ''),
            ]);
            return ['error' => ''];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
