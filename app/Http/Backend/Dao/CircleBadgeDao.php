<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

/**
 * 圈子徽章数据访问
 */
class CircleBadgeDao
{
    /**
     * 圈子徽章列表
     */
    public static function list(array $request): array
    {
        $query = Db::table('ext_circle_badges')
            ->select('id', 'name', 'icon_url', 'remark', 'sort_order', 'status', 'created_at', 'updated_at')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC');

        if (!empty($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%');
        }
        if (($request['status'] ?? 0) != 0) {
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

    /**
     * 圈子徽章详情
     */
    public static function get(array $request): array
    {
        $row = Db::table('ext_circle_badges')
            ->select('id', 'name', 'icon_url', 'remark', 'sort_order', 'status', 'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '圈子徽章不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    /**
     * 创建圈子徽章
     */
    public static function create(array $request): array
    {
        $affectedRows = Db::table('ext_circle_badges')->insert([
            'name' => $request['name'],
            'icon_url' => $request['icon_url'],
            'remark' => $request['remark'],
            'sort_order' => $request['sort_order'],
            'status' => $request['status'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建圈子徽章失败'];
        }

        return ['error' => ''];
    }

    /**
     * 更新圈子徽章
     */
    public static function update(array $request): array
    {
        $exists = Db::table('ext_circle_badges')->where('id', $request['id'])->first();
        if (!$exists) {
            return ['error' => '圈子徽章不存在'];
        }

        Db::table('ext_circle_badges')
            ->where('id', $request['id'])
            ->update([
                'name' => $request['name'],
                'icon_url' => $request['icon_url'],
                'remark' => $request['remark'],
                'sort_order' => $request['sort_order'],
                'status' => $request['status'],
            ]);

        return ['error' => ''];
    }
}
