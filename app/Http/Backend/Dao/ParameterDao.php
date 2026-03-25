<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class ParameterDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_parameters_v')
            ->select('id', 'group_id', 'group_name', 'name', 'code', 'remark', 'status', 'created_at', 'updated_at')
            ->orderBy('created_at', 'DESC');

        if (!empty($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%');
        }
        if (!empty($request['code'])) {
            $query->where('code', 'like', '%' . $request['code'] . '%');
        }
        if (($request['group_id'] ?? 0) > 0) {
            $query->where('group_id', $request['group_id']);
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
        $row = Db::table('ext_parameters_v')
            ->select('id', 'group_id', 'group_name', 'name', 'code', 'remark', 'status', 'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '参数记录不存在，请检查ID是否正确', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        $affectedRows = Db::table('ext_parameters')->insert([
            'group_id' => $request['group_id'],
            'name' => $request['name'],
            'code' => $request['code'],
            'remark' => $request['remark'],
            'status' => $request['status'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建参数失败，请检查数据是否有效或编码是否重复'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        $affectedRows = Db::table('ext_parameters')
            ->where('id', $request['id'])
            ->update([
                'group_id' => $request['group_id'],
                'name' => $request['name'],
                'code' => $request['code'],
                'remark' => $request['remark'],
                'status' => $request['status'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新参数失败，记录可能不存在或数据未变更'];
        }

        return ['error' => ''];
    }

    public static function del(array $request): array
    {
        $affectedRows = Db::table('ext_parameters')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除参数失败，记录不存在或已被删除'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $affectedRows = Db::table('ext_parameters')
            ->where('id', $request['id'])
            ->update(['status' => $request['status']]);

        if ($affectedRows == 0) {
            return ['error' => '修改参数状态失败，记录不存在或状态值无效'];
        }

        return ['error' => ''];
    }
}
