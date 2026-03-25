<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

/**
 * 后台笔记 Dao：表 ext_user_notes 与前台 NotesController 共用。
 * 表实际字段（以前台为准）：id, user_id, user_nickname, user_avatar, created, type, status, title, content,
 * note_avatar, verify_video, groups, city, remark, remark_friends, images, videos, pin, pin_time, label
 * 无：category_id, tags, note_type, view_count, today_view_count, last_viewed_at, like_count, collect_count,
 * share_count, comment_count, visibility, is_pinned, is_featured, allow_comment, rate_limit_*, audit_status,
 * auditor_id, audited_at, summary, cover_image, content_type, word_count, sort_order, recommend_score,
 * is_hot, source, device_info, ip_address, location, updated_at
 */
class UserNoteDao
{
    /** 表 ext_user_notes 实际存在的列（select 用） */
    private static function tableColumns(): array
    {
        return [
            'id', 'user_id', 'user_nickname', 'user_avatar', 'created', 'type', 'status', 'title', 'content',
            'note_avatar', 'verify_video', 'groups', 'city', 'remark', 'remark_friends', 'images', 'videos',
            'pin', 'pin_time', 'label',
        ];
    }

    /** 为管理后台兼容：给一条记录补全“伪字段”，与后台 UI 字段名一致 */
    private static function mapRowToAdminShape(object|array $row): array
    {
        $arr = is_object($row) ? (array)$row : $row;
        $created = $arr['created'] ?? null;
        return array_merge($arr, [
            'note_type' => (int)($arr['type'] ?? 0),
            'is_pinned' => (int)($arr['pin'] ?? 0),
            'is_featured' => 0,
            'visibility' => 0,
            'audit_status' => 0,
            'view_count' => 0,
            'today_view_count' => 0,
            'like_count' => 0,
            'collect_count' => 0,
            'category_id' => 0,
            'tags' => '',
            'created_at' => $created ? (is_numeric($created) ? date('Y-m-d H:i:s', (int)$created) : $created) : null,
            'updated_at' => null,
        ]);
    }

    public static function list(array $request): array
    {
        $cols = self::tableColumns();
        $query = Db::table('ext_user_notes')
            ->select($cols)
            ->orderBy('id', 'DESC');

        if (!empty($request['title'])) {
            $query->where('title', 'like', '%' . $request['title'] . '%');
        }
        if (!empty($request['content'])) {
            $query->where('content', 'like', '%' . $request['content'] . '%');
        }
        $userId = $request['user_id'] ?? null;
        if ($userId !== null && $userId !== '' && $userId !== 0) {
            $query->where('user_id', $userId);
        }
        if (($request['note_type'] ?? 0) > 0) {
            $query->where('type', $request['note_type']);
        }
        if (($request['status'] ?? 0) > 0) {
            $query->where('status', $request['status']);
        }
        if ($request['is_pinned'] ?? false) {
            $query->where('pin', 1);
        }
        if (!empty($request['start_date'])) {
            $query->where('created', '>=', is_numeric($request['start_date']) ? (int)$request['start_date'] : strtotime($request['start_date']));
        }
        if (!empty($request['end_date'])) {
            $query->where('created', '<=', is_numeric($request['end_date']) ? (int)$request['end_date'] : strtotime($request['end_date'] . ' 23:59:59'));
        }

        $total = $query->count();
        $offset = ($request['page'] - 1) * $request['pageSize'];
        $rows = $query->limit($request['pageSize'])->offset($offset)->get();
        $records = [];
        foreach ($rows as $row) {
            $records[] = self::mapRowToAdminShape($row);
        }

        return [
            'error' => '',
            'data' => [
                'records' => $records,
                'total' => $total,
            ],
        ];
    }

    public static function get(array $request): array
    {
        $row = Db::table('ext_user_notes')
            ->select(self::tableColumns())
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '用户笔记不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => self::mapRowToAdminShape($row),
        ];
    }

    public static function create(array $request): array
    {
        $noteType = (int)($request['note_type'] ?? 1);
        $affectedRows = Db::table('ext_user_notes')->insert([
            'user_id' => $request['user_id'],
            'title' => $request['title'] ?? '',
            'content' => $request['content'] ?? '',
            'type' => $noteType,
            'status' => (int)($request['status'] ?? 1),
            'user_nickname' => '',
            'user_avatar' => '',
            'created' => time(),
            'note_avatar' => '',
            'verify_video' => '',
            'groups' => '',
            'city' => '',
            'remark' => '',
            'remark_friends' => '',
            'images' => '',
            'videos' => '',
            'pin' => 0,
            'pin_time' => 0,
            'label' => '',
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建用户笔记失败'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        $exists = Db::table('ext_user_notes')->where('id', $request['id'])->first();
        if (!$exists) {
            return ['error' => '用户笔记不存在'];
        }

        $data = [
            'title' => $request['title'] ?? $exists->title,
            'content' => $request['content'] ?? $exists->content,
            'type' => (int)($request['note_type'] ?? $exists->type),
            'status' => (int)($request['status'] ?? $exists->status),
        ];
        if (isset($request['user_id'])) {
            $data['user_id'] = $request['user_id'];
        }

        Db::table('ext_user_notes')->where('id', $request['id'])->update($data);

        return ['error' => ''];
    }

    public static function del(array $request): array
    {
        // 检查是否存在
        $exists = Db::table('ext_user_notes')->where('id', $request['id'])->first();
        if (!$exists) {
            return ['error' => '用户笔记不存在'];
        }

        $affectedRows = Db::table('ext_user_notes')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除用户笔记失败'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        // 检查是否存在
        $exists = Db::table('ext_user_notes')->where('id', $request['id'])->first();
        if (!$exists) {
            return ['error' => '用户笔记不存在'];
        }

        $affectedRows = Db::table('ext_user_notes')
            ->where('id', $request['id'])
            ->update(['status' => $request['status']]);

        if ($affectedRows == 0) {
            return ['error' => '修改用户笔记状态失败'];
        }

        return ['error' => ''];
    }

    /** 表无 audit 相关列，仅做存在性检查 */
    public static function audit(array $request): array
    {
        $exists = Db::table('ext_user_notes')->where('id', $request['id'])->first();
        if (!$exists) {
            return ['error' => '用户笔记不存在'];
        }
        return ['error' => ''];
    }

    public static function pin(array $request): array
    {
        $affectedRows = Db::table('ext_user_notes')
            ->where('id', $request['id'])
            ->update([
                'pin' => $request['is_pinned'] ? 1 : 0,
                'pin_time' => $request['is_pinned'] ? time() : 0,
            ]);

        if ($affectedRows == 0) {
            return ['error' => '设置笔记置顶失败'];
        }

        return ['error' => ''];
    }

    /** 表无 is_featured 列，仅做存在性检查 */
    public static function feature(array $request): array
    {
        $exists = Db::table('ext_user_notes')->where('id', $request['id'])->first();
        if (!$exists) {
            return ['error' => '用户笔记不存在'];
        }
        return ['error' => ''];
    }

    /** 表无 is_hot 列，仅做存在性检查 */
    public static function hot(array $request): array
    {
        $exists = Db::table('ext_user_notes')->where('id', $request['id'])->first();
        if (!$exists) {
            return ['error' => '用户笔记不存在'];
        }
        return ['error' => ''];
    }

    /** 表无 rate_limit 列，仅做存在性检查 */
    public static function rateLimit(array $request): array
    {
        $exists = Db::table('ext_user_notes')->where('id', $request['id'])->first();
        if (!$exists) {
            return ['error' => '用户笔记不存在'];
        }
        return ['error' => ''];
    }

    /** 表无统计数字列，返回默认 0 */
    public static function updateStats(array $request): array
    {
        $exists = Db::table('ext_user_notes')->where('id', $request['id'])->first();
        if (!$exists) {
            return ['error' => '用户笔记不存在'];
        }
        return ['error' => ''];
    }

    public static function getStats(array $request): array
    {
        $exists = Db::table('ext_user_notes')->where('id', $request['id'])->first();
        if (!$exists) {
            return ['error' => '用户笔记不存在', 'data' => null];
        }
        return [
            'error' => '',
            'data' => [
                'view_count' => 0,
                'today_view_count' => 0,
                'like_count' => 0,
                'collect_count' => 0,
                'share_count' => 0,
                'comment_count' => 0,
            ],
        ];
    }

    /** 表无 audit 列，仅校验 id 存在 */
    public static function batchAudit(array $ids, int $auditStatus, int $auditorId): array
    {
        if (empty($ids)) {
            return ['error' => '笔记ID列表不能为空'];
        }
        $count = Db::table('ext_user_notes')->whereIn('id', $ids)->count();
        if ($count === 0) {
            return ['error' => '批量审核失败'];
        }
        return ['error' => ''];
    }

    public static function batchChangeStatus(array $ids, int $status): array
    {
        if (empty($ids)) {
            return ['error' => '笔记ID列表不能为空'];
        }
        $affectedRows = Db::table('ext_user_notes')
            ->whereIn('id', $ids)
            ->update(['status' => $status]);

        if ($affectedRows == 0) {
            return ['error' => '批量修改状态失败'];
        }

        return ['error' => ''];
    }

    public static function batchDelete(array $ids): array
    {
        if (empty($ids)) {
            return ['error' => '笔记ID列表不能为空'];
        }
        $affectedRows = Db::table('ext_user_notes')
            ->whereIn('id', $ids)
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '批量删除失败'];
        }

        return ['error' => ''];
    }

    public static function getNoteStats(): array
    {
        $row = Db::table('ext_user_notes')
            ->selectRaw('COUNT(*) as total_notes, 
                        SUM(CASE WHEN pin = 1 THEN 1 ELSE 0 END) as pinned_count')
            ->first();

        if (!$row) {
            return ['error' => '获取笔记统计失败', 'data' => null];
        }

        $data = (array)$row;
        $data['total_views'] = 0;
        $data['total_likes'] = 0;
        $data['total_collects'] = 0;
        $data['total_shares'] = 0;
        $data['total_comments'] = 0;
        $data['pending_audit'] = 0;
        $data['approved'] = 0;
        $data['rejected'] = 0;
        $data['featured_count'] = 0;
        $data['hot_count'] = 0;

        return [
            'error' => '',
            'data' => $data,
        ];
    }

    public static function getUserNoteStats(int $userId): array
    {
        $row = Db::table('ext_user_notes')
            ->selectRaw('COUNT(*) as user_total_notes')
            ->where('user_id', $userId)
            ->first();

        if (!$row) {
            return ['error' => '获取用户笔记统计失败', 'data' => null];
        }

        $data = (array)$row;
        $data['user_total_views'] = 0;
        $data['user_total_likes'] = 0;
        $data['user_total_collects'] = 0;
        $data['user_total_shares'] = 0;
        $data['user_total_comments'] = 0;

        return [
            'error' => '',
            'data' => $data,
        ];
    }

    /** 表无 category_id，按 user_id 返回零统计 */
    public static function getCategoryNoteStats(int $categoryId): array
    {
        return [
            'error' => '',
            'data' => [
                'category_total_notes' => 0,
                'category_total_views' => 0,
                'category_total_likes' => 0,
                'category_total_collects' => 0,
                'category_total_shares' => 0,
                'category_total_comments' => 0,
            ],
        ];
    }
}
