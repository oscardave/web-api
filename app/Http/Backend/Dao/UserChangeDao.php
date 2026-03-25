<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class UserChangeDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_user_changes as c')
            ->leftJoin('ext_users as eu', 'c.user_id', '=', 'eu.id')
            ->select(
                'c.id', 'c.user_id', 'eu.nickname as user_name',
                'c.change_type', 'c.operation_type', 'c.amount',
                'c.balance_before', 'c.balance_after',
                'c.source_id', 'c.description', 'c.status', 'c.created_at', 'c.updated_at'
            )
            ->orderBy('c.created_at', 'DESC');

        if (($request['user_id'] ?? 0) > 0) {
            $query->where('c.user_id', $request['user_id']);
        }
        if (!empty($request['nickname'] ?? '')) {
            $query->where('eu.nickname', 'like', '%' . $request['nickname'] . '%');
        }
        if (($request['change_type'] ?? 0) > 0) {
            $query->where('c.change_type', $request['change_type']);
        }
        if (($request['operation_type'] ?? 0) > 0) {
            $query->where('c.operation_type', $request['operation_type']);
        }
        if (($request['status'] ?? 0) > 0) {
            $query->where('c.status', $request['status']);
        }
        if (!empty($request['source_id'])) {
            $query->where('c.source_id', 'like', '%' . $request['source_id'] . '%');
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

    public static function listByUserId(array $request): array
    {
        $userId = (int)($request['user_id'] ?? 0);
        if ($userId <= 0) {
            return ['error' => 'user_id无效', 'data' => ['records' => [], 'total' => 0]];
        }

        $query = Db::table('ext_user_changes')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC');

        if (($request['change_type'] ?? 0) > 0) {
            $query->where('change_type', $request['change_type']);
        }
        if (($request['operation_type'] ?? 0) > 0) {
            $query->where('operation_type', $request['operation_type']);
        }

        $total = $query->count();
        $page = max(1, (int)($request['page'] ?? 1));
        $pageSize = min(100, max(1, (int)($request['pageSize'] ?? 15)));
        $offset = ($page - 1) * $pageSize;
        $rows = $query->limit($pageSize)->offset($offset)->get()->toArray();

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
        $row = Db::table('ext_user_changes')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '账变记录不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function addRecord(array $request): array
    {
        $affected = Db::table('ext_user_changes')->insert([
            'user_id' => $request['user_id'],
            'change_type' => $request['change_type'],
            'operation_type' => (int)($request['operation_type'] ?? 1),
            'amount' => $request['amount'],
            'balance_before' => (int)($request['balance_before'] ?? 0),
            'balance_after' => (int)($request['balance_after'] ?? 0),
            'source_id' => $request['source_id'] ?? '',
            'description' => $request['description'] ?? '',
            'status' => 1,
        ]);

        if (!$affected) {
            return ['error' => '写入账变记录失败'];
        }
        return ['error' => ''];
    }
}
