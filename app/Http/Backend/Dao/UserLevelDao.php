<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class UserLevelDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_user_levels')
            ->select('id', 'name', 'avatar_url', 'remark', 'description', 'validity_period', 'payment_point',
                     'translation_enabled', 'max_groups_joined', 'max_groups_created',
                     'max_members_in_group', 'max_assisted_city', 'max_notes_created',
                     'max_notes_per_wall', 'max_super_note_wall', 'friend_limit', 'avatar_frame_enabled',
                     'sort', 'is_exchangeable', 'status', 'created_at', 'updated_at')
            ->orderBy('sort', 'ASC')
            ->orderBy('id', 'DESC');

        if (!empty($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%');
        }
        if (($request['status'] ?? 0) != 0) {
            $query->where('status', $request['status']);
        }
        if (!empty($request['only_exchangeable'])) {
            $query->where('is_exchangeable', 1);
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
        $row = Db::table('ext_user_levels')
            ->select('id', 'name', 'avatar_url', 'remark', 'description', 'validity_period', 'payment_point',
                     'translation_enabled', 'max_groups_joined', 'max_groups_created',
                     'max_members_in_group', 'max_assisted_city', 'max_notes_created',
                     'max_notes_per_wall', 'max_super_note_wall', 'friend_limit', 'avatar_frame_enabled',
                     'sort', 'is_exchangeable', 'status', 'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '用户等级不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        // 检查名称是否已存在
        $exists = Db::table('ext_user_levels')->where('name', $request['name'])->first();
        if ($exists) {
            return ['error' => '用户等级名称已存在'];
        }

        // 检查排序值是否已存在
        $sort = (int) ($request['sort'] ?? 0);
        $sortExists = Db::table('ext_user_levels')->where('sort', $sort)->first();
        if ($sortExists) {
            return ['error' => '该排序值已存在，请使用其他排序值'];
        }

        $affectedRows = Db::table('ext_user_levels')->insert([
            'name' => $request['name'],
            'avatar_url' => $request['avatar_url'] ?? '',
            'sort' => $sort,
            'remark' => $request['remark'],
            'description' => $request['description'],
            'validity_period' => $request['validity_period'],
            'payment_point' => $request['payment_point'],
            'translation_enabled' => $request['translation_enabled'] ? 1 : 0,
            'max_groups_joined' => $request['max_groups_joined'],
            'max_groups_created' => $request['max_groups_created'],
            'max_members_in_group' => $request['max_members_in_group'],
            'max_assisted_city' => $request['max_assisted_city'],
            'max_notes_created' => $request['max_notes_created'],
            'max_notes_per_wall' => $request['max_notes_per_wall'],
            'max_super_note_wall' => $request['max_super_note_wall'],
            'friend_limit' => (int)($request['friend_limit'] ?? 100),
            'avatar_frame_enabled' => $request['avatar_frame_enabled'] ? 1 : 0,
            'is_exchangeable' => !empty($request['is_exchangeable']) ? 1 : 0,
            'status' => $request['status'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建用户等级失败'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        // 检查是否存在
        $exists = Db::table('ext_user_levels')->where('id', $request['id'])->first();
        if (!$exists) {
            return ['error' => '用户等级不存在'];
        }

        // 检查名称是否已被其他记录使用
        $nameExists = Db::table('ext_user_levels')
            ->where('name', $request['name'])
            ->where('id', '!=', $request['id'])
            ->first();
        if ($nameExists) {
            return ['error' => '用户等级名称已存在'];
        }

        // 若排序值变更，检查新排序值是否已被其他记录使用
        $newSort = (int) ($request['sort'] ?? 0);
        $oldSort = (int) ($exists->sort ?? 0);
        if ($newSort !== $oldSort) {
            $sortExists = Db::table('ext_user_levels')
                ->where('sort', $newSort)
                ->where('id', '!=', $request['id'])
                ->first();
            if ($sortExists) {
                return ['error' => '该排序值已存在，请使用其他排序值'];
            }
        }

        $affectedRows = Db::table('ext_user_levels')
            ->where('id', $request['id'])
            ->update([
                'name' => $request['name'],
                'avatar_url' => $request['avatar_url'] ?? '',
                'sort' => $newSort,
                'remark' => $request['remark'],
                'description' => $request['description'],
                'validity_period' => $request['validity_period'],
                'payment_point' => $request['payment_point'],
                'translation_enabled' => $request['translation_enabled'] ? 1 : 0,
                'max_groups_joined' => $request['max_groups_joined'],
                'max_groups_created' => $request['max_groups_created'],
                'max_members_in_group' => $request['max_members_in_group'],
                'max_assisted_city' => $request['max_assisted_city'],
                'max_notes_created' => $request['max_notes_created'],
                'max_notes_per_wall' => $request['max_notes_per_wall'],
                'max_super_note_wall' => $request['max_super_note_wall'],
                'friend_limit' => (int)($request['friend_limit'] ?? 100),
                'avatar_frame_enabled' => $request['avatar_frame_enabled'] ? 1 : 0,
                'is_exchangeable' => !empty($request['is_exchangeable']) ? 1 : 0,
                'status' => $request['status'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新用户等级失败'];
        }

        return ['error' => ''];
    }

    public static function del(array $request): array
    {
        // 检查是否存在
        $exists = Db::table('ext_user_levels')->where('id', $request['id'])->first();
        if (!$exists) {
            return ['error' => '用户等级不存在'];
        }

        $affectedRows = Db::table('ext_user_levels')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除用户等级失败'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        // 检查是否存在
        $exists = Db::table('ext_user_levels')->where('id', $request['id'])->first();
        if (!$exists) {
            return ['error' => '用户等级不存在'];
        }

        $affectedRows = Db::table('ext_user_levels')
            ->where('id', $request['id'])
            ->update(['status' => $request['status']]);

        if ($affectedRows == 0) {
            return ['error' => '修改用户等级状态失败'];
        }

        return ['error' => ''];
    }
}
