<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class CustomerServiceDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_customer_services_v')
            ->select('id', 'sort_order', 'nickname', 'username', 'beautiful_id', 
                     'friends_count', 'max_friends', 'status', 'status_name', 
                     'created_at', 'updated_at')
            ->orderBy('sort_order', 'ASC');

        if (!empty($request['nickname'])) {
            $query->where('nickname', 'like', '%' . $request['nickname'] . '%');
        }
        if (!empty($request['username'])) {
            $query->where('username', 'like', '%' . $request['username'] . '%');
        }
        if (!empty($request['beautiful_id'])) {
            $query->where('beautiful_id', 'like', '%' . $request['beautiful_id'] . '%');
        }
        if (($request['status'] ?? 0) > 0) {
            $query->where('status', $request['status']);
        }
        if (($request['sort_order'] ?? 0) > 0) {
            $query->where('sort_order', $request['sort_order']);
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
        $row = Db::table('ext_customer_services_v')
            ->select('id', 'sort_order', 'nickname', 'username', 'beautiful_id', 
                     'friends_count', 'max_friends', 'status', 'status_name', 
                     'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '客服记录不存在，请检查ID是否正确', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        $affectedRows = Db::table('ext_customer_services')->insert([
            'sort_order' => $request['sort_order'],
            'nickname' => $request['nickname'],
            'username' => $request['username'],
            'beautiful_id' => $request['beautiful_id'],
            'friends_count' => $request['friends_count'],
            'max_friends' => $request['max_friends'],
            'status' => $request['status'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建客服失败，请检查数据是否有效或用户名/靓靓号是否重复'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        $affectedRows = Db::table('ext_customer_services')
            ->where('id', $request['id'])
            ->update([
                'sort_order' => $request['sort_order'],
                'nickname' => $request['nickname'],
                'username' => $request['username'],
                'beautiful_id' => $request['beautiful_id'],
                'friends_count' => $request['friends_count'],
                'max_friends' => $request['max_friends'],
                'status' => $request['status'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新客服失败，记录可能不存在或数据未变更'];
        }

        return ['error' => ''];
    }

    public static function del(array $request): array
    {
        $affectedRows = Db::table('ext_customer_services')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除客服失败，记录不存在或已被删除'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $affectedRows = Db::table('ext_customer_services')
            ->where('id', $request['id'])
            ->update(['status' => $request['status']]);

        if ($affectedRows == 0) {
            return ['error' => '修改客服状态失败，记录不存在或状态值无效'];
        }

        return ['error' => ''];
    }

    public static function changeSort(array $request): array
    {
        $affectedRows = Db::table('ext_customer_services')
            ->where('id', $request['id'])
            ->update(['sort_order' => $request['sort_order']]);

        if ($affectedRows == 0) {
            return ['error' => '修改客服排序失败，记录不存在或排序值无效'];
        }

        return ['error' => ''];
    }
}
