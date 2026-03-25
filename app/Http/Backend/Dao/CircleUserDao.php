<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class CircleUserDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_circle_users')
            ->select('id', 'circle_id', 'user_id', 'user_nickname', 'user_avatar_url', 'user_vanity_id', 
                     'role_type', 'permissions', 'can_view_help', 'can_post_help', 
                     'help_view_prohibited', 'help_view_prohibit_duration', 'help_view_prohibit_expire_time', 
                     'help_post_prohibited', 'help_post_prohibit_duration', 'help_post_prohibit_expire_time', 
                     'member_status', 'join_method', 'last_login_time', 'last_activity_time', 
                     'is_over_month_inactive', 'is_weekly_active', 'is_monthly_active', 
                     'help_post_count', 'last_help_post_time', 'daily_active_count', 
                     'weekly_active_count', 'monthly_active_count', 'inviter_id', 
                     'inviter_nickname', 'inviter_vanity_id', 'circle_recommendation', 
                     'circle_relationship', 'join_time', 'created_time', 'status', 'remark', 
                     'created_at', 'updated_at')
            ->where('status', 1)
            ->orderBy('join_time', 'DESC');

        if (!empty($request['circle_id'])) {
            $query->where('circle_id', $request['circle_id']);
        }
        $keyword = $request['keyword'] ?? '';
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('user_nickname', 'like', '%' . $keyword . '%')
                    ->orWhere('user_id', 'like', '%' . $keyword . '%')
                    ->orWhere('user_vanity_id', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request['user_nickname'])) {
            $query->where('user_nickname', 'like', '%' . $request['user_nickname'] . '%');
        }
        if (!empty($request['user_id'])) {
            $query->where('user_id', 'like', '%' . $request['user_id'] . '%');
        }
        if (!empty($request['user_vanity_id'])) {
            $query->where('user_vanity_id', 'like', '%' . $request['user_vanity_id'] . '%');
        }
        if (!empty($request['role_type'])) {
            $query->where('role_type', $request['role_type']);
        }
        if (!empty($request['member_status'])) {
            $query->where('member_status', $request['member_status']);
        }
        if (!empty($request['join_method'])) {
            $query->where('join_method', $request['join_method']);
        }
        if ($request['is_over_month_inactive'] ?? false) {
            $query->where('is_over_month_inactive', true);
        }
        if ($request['is_weekly_active'] ?? false) {
            $query->where('is_weekly_active', true);
        }
        if ($request['is_monthly_active'] ?? false) {
            $query->where('is_monthly_active', true);
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
}
