<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class BlockMailDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_block_mails')
            ->select('id', 'mail', 'remark', 'status', 'created_at', 'updated_at')
            ->orderBy('created_at', 'DESC');

        if (!empty($request['mail'])) {
            $query->where('mail', 'like', '%' . $request['mail'] . '%');
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
        $row = Db::table('ext_block_mails')
            ->select('id', 'mail', 'remark', 'status', 'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '封禁IP记录不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        $affectedRows = Db::table('ext_block_mails')->insert([
            'mail' => $request['mail'],
            'remark' => $request['remark'],
            'status' => $request['status'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建封禁IP记录失败'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        $affectedRows = Db::table('ext_block_mails')
            ->where('id', $request['id'])
            ->update([
                'mail' => $request['mail'],
                'remark' => $request['remark'],
                'status' => $request['status'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新封禁IP记录失败'];
        }

        return ['error' => ''];
    }

    public static function del(array $request): array
    {
        $affectedRows = Db::table('ext_block_mails')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除封禁IP记录失败'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $affectedRows = Db::table('ext_block_mails')
            ->where('id', $request['id'])
            ->update(['status' => $request['status']]);

        if ($affectedRows == 0) {
            return ['error' => '修改封禁IP状态失败'];
        }

        return ['error' => ''];
    }
}
