<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class UserBadgeDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_user_badges')
            ->select('id', 'name', 'type', 'icon_url', 'points_exchange_cost', 
                     'membership_level_required', 'integrity_score_required', 
                     'invited_users_required', 'monthly_help_posts_required', 
                     'is_exchangeable', 'description', 'sort_order', 'status', 
                     'created_at', 'updated_at')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC');

        if (!empty($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%');
        }
        if (($request['type'] ?? 0) != 0) {
            $query->where('type', $request['type']);
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

    public static function get(array $request): array
    {
        $row = Db::table('ext_user_badges')
            ->select('id', 'name', 'type', 'icon_url', 'points_exchange_cost', 
                     'membership_level_required', 'integrity_score_required', 
                     'invited_users_required', 'monthly_help_posts_required', 
                     'is_exchangeable', 'description', 'sort_order', 'status', 
                     'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '用户徽章不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        // 检查名称是否已存在
        $exists = Db::table('ext_user_badges')->where('name', $request['name'])->first();
        if ($exists) {
            return ['error' => '用户徽章名称已存在'];
        }

        $affectedRows = Db::table('ext_user_badges')->insert([
            'name' => $request['name'],
            'type' => $request['type'],
            'icon_url' => $request['icon_url'],
            'points_exchange_cost' => $request['points_exchange_cost'],
            'membership_level_required' => $request['membership_level_required'],
            'integrity_score_required' => $request['integrity_score_required'],
            'invited_users_required' => $request['invited_users_required'],
            'monthly_help_posts_required' => $request['monthly_help_posts_required'],
            'is_exchangeable' => $request['is_exchangeable'] ? 1 : 0,
            'description' => $request['description'],
            'sort_order' => $request['sort_order'],
            'status' => $request['status'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建用户徽章失败'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        // 检查是否存在
        $exists = Db::table('ext_user_badges')->where('id', $request['id'])->first();
        if (!$exists) {
            return ['error' => '用户徽章不存在'];
        }

        // 检查名称是否已被其他记录使用
        $nameExists = Db::table('ext_user_badges')
            ->where('name', $request['name'])
            ->where('id', '!=', $request['id'])
            ->first();
        if ($nameExists) {
            return ['error' => '用户徽章名称已存在'];
        }

        $affectedRows = Db::table('ext_user_badges')
            ->where('id', $request['id'])
            ->update([
                'name' => $request['name'],
                'type' => $request['type'],
                'icon_url' => $request['icon_url'],
                'points_exchange_cost' => $request['points_exchange_cost'],
                'membership_level_required' => $request['membership_level_required'],
                'integrity_score_required' => $request['integrity_score_required'],
                'invited_users_required' => $request['invited_users_required'],
                'monthly_help_posts_required' => $request['monthly_help_posts_required'],
                'is_exchangeable' => $request['is_exchangeable'] ? 1 : 0,
                'description' => $request['description'],
                'sort_order' => $request['sort_order'],
                'status' => $request['status'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新用户徽章失败'];
        }

        return ['error' => ''];
    }

    public static function delete(array $request): array
    {
        $id = (int)($request['id'] ?? 0);
        if ($id <= 0) {
            return ['error' => '徽章ID无效'];
        }
        $exists = Db::table('ext_user_badges')->where('id', $id)->first();
        if (!$exists) {
            return ['error' => '用户徽章不存在'];
        }
        $affected = Db::table('ext_user_badges')->where('id', $id)->delete();
        if ($affected <= 0) {
            return ['error' => '删除用户徽章失败'];
        }
        return ['error' => ''];
    }
}
