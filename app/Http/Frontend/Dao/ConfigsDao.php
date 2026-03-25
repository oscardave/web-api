<?php
declare(strict_types=1);

namespace App\Http\Frontend\Dao;

use Hyperf\DbConnection\Db;

class ConfigsDao
{
    /**
     * 获取启用的用户等级列表（用于 C 端下拉等）
     */
    public static function getLevels(): array
    {
        $rows = Db::table('ext_user_levels')
            ->select('id', 'name', 'avatar_url')
            ->where('status', 1)
            ->orderBy('sort', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->toArray();

        return array_map(fn ($r) => (array)$r, $rows);
    }

    /**
     * 获取启用的身份徽章列表（ext_user_badges 表 type=1），直接返回表数据
     */
    public static function getIdentityBadges(): array
    {
        $rows = Db::table('ext_user_badges')
            ->where('type', 1)
            ->where('status', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();

        return array_map(fn ($r) => (array)$r, $rows->toArray());
    }

    /**
     * 获取启用的诚信徽章列表（ext_user_badges 表 type=2），直接返回表数据
     */
    public static function getCreditBadges(): array
    {
        $rows = Db::table('ext_user_badges')
            ->where('type', 2)
            ->where('status', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();

        return array_map(fn ($r) => (array)$r, $rows->toArray());
    }

    /**
     * 获取启用的圈子徽章列表（ext_circle_badges 表 status=1），供 C 端根据 profile 的 circle_badge_id 取对应图标
     */
    public static function getCircleBadges(): array
    {
        $rows = Db::table('ext_circle_badges')
            ->select('id', 'name', 'icon_url', 'remark', 'sort_order')
            ->where('status', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();

        return array_map(fn ($r) => (array)$r, $rows->toArray());
    }

    /**
     * 根据圈子徽章 ID 获取徽章名称
     */
    public static function getCircleBadgeName(int $circleBadgeId): string
    {
        if ($circleBadgeId <= 0) {
            return '';
        }
        $row = Db::table('ext_circle_badges')->where('id', $circleBadgeId)->value('name');
        return $row !== null ? trim((string)$row) : '';
    }

    /**
     * 根据会员等级 ID 获取等级名称
     */
    public static function getMemberLevelName(int $memberLevelId): string
    {
        if ($memberLevelId <= 0) {
            return '';
        }
        $row = Db::table('ext_user_levels')->where('id', $memberLevelId)->first();
        return $row && !empty($row->name) ? trim((string)$row->name) : '';
    }

    /**
     * 根据徽章 ID 获取徽章名称
     */
    public static function getBadgeName(int $badgeId): string
    {
        if ($badgeId <= 0) {
            return '';
        }
        $row = Db::table('ext_user_badges')->where('id', $badgeId)->first();
        return $row && !empty($row->name) ? trim((string)$row->name) : '';
    }

    /**
     * 获取用户的会员等级与徽章信息（用于 circles 等接口）
     */
    public static function getUserBadgeInfo(string $userId): array
    {
        $extUser = Db::table('ext_users')
            ->select('member_level_id', 'identity_badge_id', 'credit_badge_id', 'pure_badge_enabled')
            ->where('user_id', $userId)
            ->where('status', 1)
            ->first();

        if (!$extUser) {
            return [
                'member_level_id' => 0,
                'member_level_name' => '',
                'identity_badge_id' => 0,
                'identity_badge_name' => '',
                'credit_badge_id' => 0,
                'credit_badge_name' => '',
                'pure_badge_enabled' => false,
            ];
        }

        $memberLevelId = (int)($extUser->member_level_id ?? 0);
        $identityBadgeId = (int)($extUser->identity_badge_id ?? 0);
        $creditBadgeId = (int)($extUser->credit_badge_id ?? 0);

        return [
            'member_level_id' => $memberLevelId,
            'member_level_name' => self::getMemberLevelName($memberLevelId),
            'identity_badge_id' => $identityBadgeId,
            'identity_badge_name' => self::getBadgeName($identityBadgeId),
            'credit_badge_id' => $creditBadgeId,
            'credit_badge_name' => self::getBadgeName($creditBadgeId),
            'pure_badge_enabled' => (bool)($extUser->pure_badge_enabled ?? false),
        ];
    }

    /**
     * 批量获取用户的会员等级与徽章信息，返回 [user_id => info]
     */
    public static function getUsersBadgeInfoBatch(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }
        $userIds = array_unique(array_filter($userIds));
        $rows = Db::table('ext_users')
            ->select('user_id', 'member_level_id', 'identity_badge_id', 'credit_badge_id', 'pure_badge_enabled')
            ->whereIn('user_id', $userIds)
            ->where('status', 1)
            ->get();

        $result = [];
        foreach ($rows as $r) {
            $mid = (int)($r->member_level_id ?? 0);
            $iid = (int)($r->identity_badge_id ?? 0);
            $cid = (int)($r->credit_badge_id ?? 0);
            $result[$r->user_id] = [
                'member_level_id' => $mid,
                'member_level_name' => self::getMemberLevelName($mid),
                'identity_badge_id' => $iid,
                'identity_badge_name' => self::getBadgeName($iid),
                'credit_badge_id' => $cid,
                'credit_badge_name' => self::getBadgeName($cid),
                'pure_badge_enabled' => (bool)($r->pure_badge_enabled ?? false),
            ];
        }
        foreach ($userIds as $uid) {
            if (!isset($result[$uid])) {
                $result[$uid] = [
                    'member_level_id' => 0,
                    'member_level_name' => '',
                    'identity_badge_id' => 0,
                    'identity_badge_name' => '',
                    'credit_badge_id' => 0,
                    'credit_badge_name' => '',
                    'pure_badge_enabled' => false,
                ];
            }
        }
        return $result;
    }
}
