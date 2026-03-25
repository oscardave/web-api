<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class UserVanityNumberDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_user_vanity_numbers_v')
            ->select('id', 'user_id', 'vanity_number', 'type_id', 'type_name', 'type_description', 
                     'digit_counts', 'type_points_cost', 'discount_type', 'invite_discount_rules', 
                     'member_discount_rules', 'type_is_hidden_forbidden_sale', 
                     'type_is_frontend_display_default', 'account_type', 'type_status', 
                     'is_in_selected_pool', 'is_frontend_display', 'points_paid', 'discount_applied', 
                     'purchase_method', 'purchase_reason', 'status', 'created_at', 'updated_at')
            ->orderBy('created_at', 'DESC');

        if (($request['user_id'] ?? 0) > 0) {
            $query->where('user_id', $request['user_id']);
        }
        if (!empty($request['vanity_number'])) {
            $query->where('vanity_number', 'like', '%' . $request['vanity_number'] . '%');
        }
        if (($request['type_id'] ?? 0) > 0) {
            $query->where('type_id', $request['type_id']);
        }
        if (($request['status'] ?? 0) > 0) {
            $query->where('status', $request['status']);
        }

        $total = $query->count();
        $offset = ($request['page'] - 1) * $request['pageSize'];
        $rows = $query->limit($request['pageSize'])->offset($offset)->get()->toArray();

        // 处理可能为 NULL 的字段
        foreach ($rows as &$row) {
            $row = (array)$row;
            // PHP中NULL值处理，在JSON序列化时会自动转为null
        }

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
        $row = Db::table('ext_user_vanity_numbers_v')
            ->select('id', 'user_id', 'vanity_number', 'type_id', 'type_name', 'type_description', 
                     'digit_counts', 'type_points_cost', 'discount_type', 'invite_discount_rules', 
                     'member_discount_rules', 'type_is_hidden_forbidden_sale', 
                     'type_is_frontend_display_default', 'account_type', 'type_status', 
                     'is_in_selected_pool', 'is_frontend_display', 'points_paid', 'discount_applied', 
                     'purchase_method', 'purchase_reason', 'status', 'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '用户靓号记录不存在', 'data' => null];
        }

        $row = (array)$row;
        // 处理可能为NULL的字段
        if ($row['type_id'] === null) {
            $row['type_id'] = null;
        }

        return [
            'error' => '',
            'data' => $row,
        ];
    }

    /** 靓号状态：1=正常使用，2=已回收，3=已转让 */
    private const STATUS_IN_USE = 1;
    private const STATUS_RECLAIMED = 2;

    /**
     * 一用户一靓号：将该用户下除指定 id 外所有 status=1 的靓号置为已回收
     */
    private static function releaseOtherVanityNumbersForUser(int $userId, int $keepId): void
    {
        Db::table('ext_user_vanity_numbers')
            ->where('user_id', $userId)
            ->where('id', '!=', $keepId)
            ->where('status', self::STATUS_IN_USE)
            ->update(['status' => self::STATUS_RECLAIMED]);
    }

    /**
     * 创建用户靓号记录，并同步 ext_users.vanity_badge_id；一用户一靓号，自动释放该用户其余靓号
     */
    public static function create(array $request): array
    {
        $userId = (int)$request['user_id'];
        $newId = (int)Db::table('ext_user_vanity_numbers')->insertGetId([
            'user_id' => $userId,
            'vanity_number' => $request['vanity_number'],
            'type_id' => $request['type_id'],
            'is_in_selected_pool' => $request['is_in_selected_pool'] ? 1 : 0,
            'is_frontend_display' => $request['is_frontend_display'] ? 1 : 0,
            'points_paid' => $request['points_paid'],
            'discount_applied' => $request['discount_applied'],
            'purchase_method' => $request['purchase_method'],
            'purchase_reason' => $request['purchase_reason'],
            'status' => $request['status'],
        ]);

        if ($newId <= 0) {
            return ['error' => '创建用户靓号记录失败'];
        }

        // 一用户一靓号：将该用户下其余 status=1 的靓号置为已回收
        self::releaseOtherVanityNumbersForUser($userId, $newId);
        // 同步 ext_users.vanity_badge_id
        Db::table('ext_users')->where('id', $userId)->update(['vanity_badge_id' => $newId]);

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        $id = (int)$request['id'];
        $userId = (int)$request['user_id'];
        $status = (int)($request['status'] ?? 1);

        $affectedRows = Db::table('ext_user_vanity_numbers')
            ->where('id', $id)
            ->update([
                'user_id' => $userId,
                'vanity_number' => $request['vanity_number'],
                'type_id' => $request['type_id'],
                'is_in_selected_pool' => $request['is_in_selected_pool'] ? 1 : 0,
                'is_frontend_display' => $request['is_frontend_display'] ? 1 : 0,
                'points_paid' => $request['points_paid'],
                'discount_applied' => $request['discount_applied'],
                'purchase_method' => $request['purchase_method'],
                'purchase_reason' => $request['purchase_reason'],
                'status' => $status,
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新用户靓号记录失败'];
        }

        if ($status === self::STATUS_IN_USE) {
            // 一用户一靓号：将该用户下其余 status=1 的靓号置为已回收，并同步 ext_users.vanity_badge_id
            self::releaseOtherVanityNumbersForUser($userId, $id);
            Db::table('ext_users')->where('id', $userId)->update(['vanity_badge_id' => $id]);
        } else {
            // 改为已回收/已转让时，清除仍指向本记录的 ext_users.vanity_badge_id
            Db::table('ext_users')->where('vanity_badge_id', $id)->update(['vanity_badge_id' => 0]);
        }

        return ['error' => ''];
    }

    /**
     * 删除用户靓号记录，并清除 ext_users 中指向该记录的 vanity_badge_id
     */
    public static function del(array $request): array
    {
        $id = (int)$request['id'];
        $affectedRows = Db::table('ext_user_vanity_numbers')
            ->where('id', $id)
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除用户靓号记录失败'];
        }

        // 清除 ext_users 中指向该靓号记录的 vanity_badge_id
        Db::table('ext_users')->where('vanity_badge_id', $id)->update(['vanity_badge_id' => 0]);

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $id = (int)$request['id'];
        $newStatus = (int)$request['status'];

        $row = Db::table('ext_user_vanity_numbers')->where('id', $id)->first();
        if (!$row) {
            return ['error' => '用户靓号记录不存在'];
        }

        $affectedRows = Db::table('ext_user_vanity_numbers')
            ->where('id', $id)
            ->update(['status' => $newStatus]);

        if ($affectedRows == 0) {
            return ['error' => '修改用户靓号状态失败'];
        }

        $userId = (int)$row->user_id;

        if ($newStatus === self::STATUS_IN_USE) {
            // 设为正常使用时：该用户只保留本条，其余置为已回收，并同步 ext_users.vanity_badge_id
            self::releaseOtherVanityNumbersForUser($userId, $id);
            Db::table('ext_users')->where('id', $userId)->update(['vanity_badge_id' => $id]);
        } else {
            // 设为已回收/已转让时：清除仍指向本记录的 ext_users.vanity_badge_id
            Db::table('ext_users')->where('vanity_badge_id', $id)->update(['vanity_badge_id' => 0]);
        }

        return ['error' => ''];
    }
}
