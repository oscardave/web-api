<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class VanityNumberTypeDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_vanity_number_types')
            ->select('id', 'type_name', 'description', 'digit_counts', 'points_cost', 
                     'discount_type', 'invite_discount_rules', 'member_discount_rules', 
                     'is_hidden_forbidden_sale', 'is_frontend_display_default', 'account_type', 
                     'status', 'created_at', 'updated_at')
            ->orderBy('created_at', 'DESC');

        if (!empty($request['type_name'])) {
            $query->where('type_name', 'like', '%' . $request['type_name'] . '%');
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
        $row = Db::table('ext_vanity_number_types')
            ->select('id', 'type_name', 'description', 'digit_counts', 'points_cost', 
                     'discount_type', 'invite_discount_rules', 'member_discount_rules', 
                     'is_hidden_forbidden_sale', 'is_frontend_display_default', 'account_type', 
                     'status', 'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '靓号类型记录不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        $affectedRows = Db::table('ext_vanity_number_types')->insert([
            'type_name' => $request['type_name'],
            'description' => $request['description'],
            'digit_counts' => $request['digit_counts'],
            'points_cost' => $request['points_cost'],
            'discount_type' => $request['discount_type'],
            'invite_discount_rules' => $request['invite_discount_rules'],
            'member_discount_rules' => $request['member_discount_rules'],
            'is_hidden_forbidden_sale' => $request['is_hidden_forbidden_sale'] ? 1 : 0,
            'is_frontend_display_default' => $request['is_frontend_display_default'] ? 1 : 0,
            'account_type' => $request['account_type'],
            'status' => $request['status'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建靓号类型记录失败'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        $affectedRows = Db::table('ext_vanity_number_types')
            ->where('id', $request['id'])
            ->update([
                'type_name' => $request['type_name'],
                'description' => $request['description'],
                'digit_counts' => $request['digit_counts'],
                'points_cost' => $request['points_cost'],
                'discount_type' => $request['discount_type'],
                'invite_discount_rules' => $request['invite_discount_rules'],
                'member_discount_rules' => $request['member_discount_rules'],
                'is_hidden_forbidden_sale' => $request['is_hidden_forbidden_sale'] ? 1 : 0,
                'is_frontend_display_default' => $request['is_frontend_display_default'] ? 1 : 0,
                'account_type' => $request['account_type'],
                'status' => $request['status'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新靓号类型记录失败'];
        }

        return ['error' => ''];
    }

    public static function del(array $request): array
    {
        $affectedRows = Db::table('ext_vanity_number_types')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除靓号类型记录失败'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $affectedRows = Db::table('ext_vanity_number_types')
            ->where('id', $request['id'])
            ->update(['status' => $request['status']]);

        if ($affectedRows == 0) {
            return ['error' => '修改靓号类型状态失败'];
        }

        return ['error' => ''];
    }
}
