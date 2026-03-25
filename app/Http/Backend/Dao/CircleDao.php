<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Context\ApplicationContext;

class CircleDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_circles')
            ->leftJoin('ext_users', 'ext_users.user_id', '=', 'ext_circles.owner_id')
            ->select(
                'ext_circles.id',
                'ext_circles.circle_id',
                'ext_circles.circle_name',
                'ext_circles.circle_avatar_url',
                'ext_circles.circle_description',
                'ext_circles.circle_announcement',
                'ext_circles.circle_type',
                'ext_circles.owner_id',
                'ext_circles.owner_nickname',
                'ext_circles.owner_vanity_id',
                'ext_circles.allow_change_owner',
                'ext_circles.circle_status',
                'ext_circles.member_limit',
                'ext_circles.current_member_count',
                'ext_circles.current_member_count_vip',
                'ext_circles.today_new_members',
                'ext_circles.daily_active_members',
                'ext_circles.weekly_active_members',
                'ext_circles.monthly_active_members',
                'ext_circles.help_posts_24h',
                'ext_circles.total_help_posts',
                'ext_circles.invitation_code_count',
                'ext_circles.join_user_level_restriction',
                'ext_circles.help_message_view_restriction',
                'ext_circles.help_message_post_restriction',
                'ext_circles.admin_ids',
                'ext_circles.admin_nicknames',
                'ext_circles.allow_remove_admin',
                'ext_circles.allow_member_invite',
                'ext_circles.allow_member_edit_info',
                'ext_circles.auto_approve_join',
                'ext_circles.created_time',
                'ext_circles.last_activity_time',
                'ext_circles.last_help_post_time',
                'ext_circles.apply_time',
                'ext_circles.reviewed_at',
                'ext_circles.status',
                'ext_circles.reject_reason',
                'ext_circles.remark',
                'ext_circles.created_at',
                'ext_circles.updated_at',
                Db::raw('ext_users.id as owner_ext_user_id')
            )
            ->where('ext_circles.status', 1)
            ->orderBy('ext_circles.created_time', 'DESC');

        if (!empty($request['circle_name'])) {
            $query->where('ext_circles.circle_name', 'like', '%' . $request['circle_name'] . '%');
        }
        if (!empty($request['owner_vanity_id'])) {
            $query->where('ext_circles.owner_vanity_id', 'like', '%' . $request['owner_vanity_id'] . '%');
        }
        if (!empty($request['circle_id'])) {
            $query->where('ext_circles.circle_id', 'like', '%' . $request['circle_id'] . '%');
        }
        if (!empty($request['circle_status'])) {
            $query->where('ext_circles.circle_status', $request['circle_status']);
        }
        if (!empty($request['circle_type'])) {
            $query->where('ext_circles.circle_type', $request['circle_type']);
        }

        $total = $query->count();
        $offset = ($request['page'] - 1) * $request['pageSize'];
        $rows = $query->limit($request['pageSize'])->offset($offset)->get();
        // 与 pendingList/rejectedList 一致：转为纯数组，避免 stdClass/对象导致 JSON 序列化异常或前端收不到统一结构
        $rows = array_map(function ($r) {
            $arr = (array) $r;
            foreach ($arr as $k => $v) {
                if ($v instanceof \DateTimeInterface) {
                    $arr[$k] = $v->format('Y-m-d H:i:s');
                }
            }
            return $arr;
        }, $rows->all());

        $logger = ApplicationContext::getContainer()->get(StdoutLoggerInterface::class);
        $logger->info(sprintf(
            '[CircleDao::list] ext_circles status=1, total=%d, page=%d, pageSize=%d, returned=%d',
            $total,
            $request['page'],
            $request['pageSize'],
            count($rows)
        ));

        return [
            'error' => '',
            'data' => [
                'records' => $rows,
                'total' => $total,
            ],
        ];
    }

    /**
     * 更新圈子设置（入圈限制、帮办限制、成员权限等）。
     */
    public static function updateSettings(array $request): array
    {
        $circleId = (string)($request['circle_id'] ?? '');
        $circleId = trim($circleId);
        if ($circleId === '') {
            return ['error' => '圈子ID不能为空', 'data' => null];
        }

        $circle = Db::table('ext_circles')->where('circle_id', $circleId)->first();
        if (!$circle) {
            return ['error' => '圈子不存在', 'data' => null];
        }

        try {
            Db::table('ext_circles')
                ->where('circle_id', $circleId)
                ->update([
                    'invitation_code_count' => (int)($request['invitation_code_count'] ?? 5),
                    'join_user_level_restriction' => (string)($request['join_user_level_restriction'] ?? ''),
                    'help_message_view_restriction' => (string)($request['help_message_view_restriction'] ?? ''),
                    'help_message_post_restriction' => (string)($request['help_message_post_restriction'] ?? ''),
                    'allow_member_invite' => (bool)($request['allow_member_invite'] ?? true),
                    'allow_member_edit_info' => (bool)($request['allow_member_edit_info'] ?? false),
                    'auto_approve_join' => (bool)($request['auto_approve_join'] ?? false),
                    'allow_change_owner' => (bool)($request['allow_change_owner'] ?? true),
                    'allow_remove_admin' => (bool)($request['allow_remove_admin'] ?? true),
                ]);

            return ['error' => '', 'data' => null];
        } catch (\Throwable $e) {
            return ['error' => '更新圈子设置失败：' . $e->getMessage(), 'data' => null];
        }
    }

    /**
     * 更新圈子基本信息（名称、描述、公告、头像、成员上限、类型）。
     */
    public static function updateInfo(array $request): array
    {
        $circleId = trim((string)($request['circle_id'] ?? ''));
        if ($circleId === '') {
            return ['error' => '圈子ID不能为空', 'data' => null];
        }

        $circle = Db::table('ext_circles')->where('circle_id', $circleId)->first();
        if (!$circle) {
            return ['error' => '圈子不存在', 'data' => null];
        }

        try {
            Db::table('ext_circles')
                ->where('circle_id', $circleId)
                ->update([
                    'circle_name' => (string)($request['circle_name'] ?? ''),
                    'circle_description' => (string)($request['circle_description'] ?? ''),
                    'circle_announcement' => (string)($request['circle_announcement'] ?? ''),
                    'circle_avatar_url' => (string)($request['circle_avatar_url'] ?? ''),
                    'member_limit' => (int)($request['member_limit'] ?? 5000),
                    'circle_type' => in_array($request['circle_type'] ?? '', ['public', 'private'], true)
                        ? $request['circle_type'] : 'public',
                ]);

            return ['error' => '', 'data' => null];
        } catch (\Throwable $e) {
            return ['error' => '更新圈子信息失败：' . $e->getMessage(), 'data' => null];
        }
    }

    /**
     * 刷新单个圈子的统计字段（成员总数、今日新增、日活、24h帮办数）。
     * 会员数量暂固定为 0，待业务定义后再对接。
     * 供定时任务调用。
     */
    public static function refreshCircleStats(string $circleId): array
    {
        $circleId = trim($circleId);
        if ($circleId === '') {
            return ['error' => 'circle_id empty', 'data' => null];
        }

        try {
            $currentMemberCount = (int) Db::table('ext_circle_users')
                ->where('circle_id', $circleId)
                ->where('status', 1)
                ->where('member_status', 'active')
                ->count();

            $todayNewMembers = (int) Db::table('ext_circle_users')
                ->where('circle_id', $circleId)
                ->where('status', 1)
                ->where('member_status', 'active')
                ->whereRaw('join_time >= CURRENT_DATE')
                ->count();

            $dailyActiveMembers = (int) Db::table('ext_circle_users')
                ->where('circle_id', $circleId)
                ->where('status', 1)
                ->where('member_status', 'active')
                ->whereRaw('last_activity_time >= CURRENT_DATE')
                ->count();

            $helpPosts24h = 0;
            try {
                $ts24hAgo = time() - 86400;
                $helpPosts24h = (int) Db::table('ext_circle_content')
                    ->where('circle_id', $circleId)
                    ->where('created', '>=', $ts24hAgo)
                    ->count();
            } catch (\Throwable $e) {
                // ext_circle_content 可能不存在于部分环境，忽略则统计为 0
            }

            Db::table('ext_circles')
                ->where('circle_id', $circleId)
                ->update([
                    'current_member_count' => $currentMemberCount,
                    'current_member_count_vip' => 0,
                    'today_new_members' => $todayNewMembers,
                    'daily_active_members' => $dailyActiveMembers,
                    'help_posts_24h' => $helpPosts24h,
                ]);

            return ['error' => '', 'data' => null];
        } catch (\Throwable $e) {
            return ['error' => '刷新圈子统计失败：' . $e->getMessage(), 'data' => null];
        }
    }

    /**
     * 刷新所有有效圈子的统计字段，供定时任务每 2 分钟调用。
     */
    public static function refreshAllCirclesStats(): array
    {
        $circleIds = Db::table('ext_circles')
            ->where('status', 1)
            ->pluck('circle_id')
            ->toArray();

        $errors = [];
        foreach ($circleIds as $circleId) {
            $ret = self::refreshCircleStats((string) $circleId);
            if ($ret['error'] !== '') {
                $errors[] = $circleId . ': ' . $ret['error'];
            }
        }

        return [
            'error' => empty($errors) ? '' : implode('; ', $errors),
            'data' => ['processed' => count($circleIds), 'errors' => $errors],
        ];
    }

    /** 待审列表：status=0，按申请时间倒序；关联 ext_users 取 owner_ext_user_id（ext_users.id） */
    public static function pendingList(array $request): array
    {
        $query = Db::table('ext_circles')
            ->leftJoin('ext_users', 'ext_users.user_id', '=', 'ext_circles.owner_id')
            ->select(
                'ext_circles.id',
                'ext_circles.circle_id',
                'ext_circles.circle_name',
                'ext_circles.circle_avatar_url',
                'ext_circles.circle_description',
                'ext_circles.circle_announcement',
                'ext_circles.circle_type',
                'ext_circles.owner_id',
                'ext_circles.owner_nickname',
                'ext_circles.owner_vanity_id',
                'ext_circles.member_limit',
                'ext_circles.invitation_code_count',
                'ext_circles.join_user_level_restriction',
                'ext_circles.help_message_view_restriction',
                'ext_circles.help_message_post_restriction',
                'ext_circles.created_time',
                'ext_circles.apply_time',
                'ext_circles.reviewed_at',
                'ext_circles.status',
                'ext_circles.reject_reason',
                'ext_circles.remark',
                'ext_circles.created_at',
                'ext_circles.updated_at',
                Db::raw('ext_users.id as owner_ext_user_id')
            )
            ->where('ext_circles.status', 0)
            ->orderBy('ext_circles.apply_time', 'DESC');

        if (!empty($request['circle_name'])) {
            $query->where('ext_circles.circle_name', 'like', '%' . $request['circle_name'] . '%');
        }
        if (!empty($request['owner_id'])) {
            $query->where('ext_circles.owner_id', 'like', '%' . $request['owner_id'] . '%');
        }
        if (!empty($request['owner_nickname'])) {
            $query->where('ext_circles.owner_nickname', 'like', '%' . $request['owner_nickname'] . '%');
        }
        if (!empty($request['owner_vanity_id'])) {
            $query->where('ext_circles.owner_vanity_id', 'like', '%' . $request['owner_vanity_id'] . '%');
        }
        if (!empty($request['circle_type'])) {
            $query->where('ext_circles.circle_type', $request['circle_type']);
        }
        if (!empty($request['apply_time_start'])) {
            $query->where('ext_circles.apply_time', '>=', $request['apply_time_start']);
        }
        if (!empty($request['apply_time_end'])) {
            $query->where('ext_circles.apply_time', '<=', $request['apply_time_end']);
        }

        $total = $query->count();
        $offset = ($request['page'] - 1) * $request['pageSize'];
        $rows = $query->limit($request['pageSize'])->offset($offset)->get();
        $rows = array_map(fn($r) => (array) $r, $rows->all());

        foreach ($rows as &$row) {
            $row['review_status'] = 'pending';
        }
        unset($row);

        return ['error' => '', 'data' => ['records' => $rows, 'total' => $total]];
    }

    /** 已拒绝列表：status=3，按审核时间倒序；关联 ext_users 取 owner_ext_user_id（ext_users.id） */
    public static function rejectedList(array $request): array
    {
        $query = Db::table('ext_circles')
            ->leftJoin('ext_users', 'ext_users.user_id', '=', 'ext_circles.owner_id')
            ->select(
                'ext_circles.id',
                'ext_circles.circle_id',
                'ext_circles.circle_name',
                'ext_circles.circle_avatar_url',
                'ext_circles.circle_description',
                'ext_circles.circle_announcement',
                'ext_circles.circle_type',
                'ext_circles.owner_id',
                'ext_circles.owner_nickname',
                'ext_circles.owner_vanity_id',
                'ext_circles.member_limit',
                'ext_circles.invitation_code_count',
                'ext_circles.join_user_level_restriction',
                'ext_circles.help_message_view_restriction',
                'ext_circles.help_message_post_restriction',
                'ext_circles.created_time',
                'ext_circles.apply_time',
                'ext_circles.reviewed_at',
                'ext_circles.status',
                'ext_circles.reject_reason',
                'ext_circles.remark',
                'ext_circles.created_at',
                'ext_circles.updated_at',
                Db::raw('ext_users.id as owner_ext_user_id')
            )
            ->where('ext_circles.status', 3)
            ->orderBy('ext_circles.reviewed_at', 'DESC');

        if (!empty($request['circle_name'])) {
            $query->where('ext_circles.circle_name', 'like', '%' . $request['circle_name'] . '%');
        }
        if (!empty($request['owner_vanity_id'])) {
            $query->where('ext_circles.owner_vanity_id', 'like', '%' . $request['owner_vanity_id'] . '%');
        }
        if (!empty($request['circle_type'])) {
            $query->where('ext_circles.circle_type', $request['circle_type']);
        }

        $total = $query->count();
        $offset = ($request['page'] - 1) * $request['pageSize'];
        $rows = $query->limit($request['pageSize'])->offset($offset)->get();
        $rows = array_map(fn($r) => (array) $r, $rows->all());

        foreach ($rows as &$row) {
            $row['review_status'] = 'rejected';
        }
        unset($row);

        return ['error' => '', 'data' => ['records' => $rows, 'total' => $total]];
    }

    /** 审核通过：将 status 置为 1，写入 reviewed_at */
    public static function approve(array $request): array
    {
        $circleId = trim((string)($request['circle_id'] ?? ''));
        if ($circleId === '') {
            return ['error' => '圈子ID不能为空', 'data' => null];
        }
        $circle = Db::table('ext_circles')->where('circle_id', $circleId)->first();
        if (!$circle) {
            return ['error' => '圈子不存在', 'data' => null];
        }
        if ((int)($circle->status ?? 0) !== 0) {
            return ['error' => '当前状态不可审核通过', 'data' => null];
        }
        try {
            Db::table('ext_circles')
                ->where('circle_id', $circleId)
                ->update([
                    'status' => 1,
                    'reviewed_at' => date('Y-m-d H:i:s'),
                ]);
            return ['error' => '', 'data' => null];
        } catch (\Throwable $e) {
            return ['error' => '操作失败：' . $e->getMessage(), 'data' => null];
        }
    }

    /** 审核拒绝：将 status 置为 3，写入 reviewed_at、reject_reason */
    public static function reject(array $request): array
    {
        $circleId = trim((string)($request['circle_id'] ?? ''));
        if ($circleId === '') {
            return ['error' => '圈子ID不能为空', 'data' => null];
        }
        $circle = Db::table('ext_circles')->where('circle_id', $circleId)->first();
        if (!$circle) {
            return ['error' => '圈子不存在', 'data' => null];
        }
        if ((int)($circle->status ?? 0) !== 0) {
            return ['error' => '当前状态不可拒绝', 'data' => null];
        }
        $rejectReason = trim((string)($request['reject_reason'] ?? ''));
        try {
            Db::table('ext_circles')
                ->where('circle_id', $circleId)
                ->update([
                    'status' => 3,
                    'reviewed_at' => date('Y-m-d H:i:s'),
                    'reject_reason' => $rejectReason,
                ]);
            return ['error' => '', 'data' => null];
        } catch (\Throwable $e) {
            return ['error' => '操作失败：' . $e->getMessage(), 'data' => null];
        }
    }
}
