<?php

declare(strict_types=1);

namespace App\Http\Frontend\Dao;

use Hyperf\DbConnection\Db;
use App\Common\Cache;

class ChatGroups
{
    // 缓存过期时间：1小时（3600秒）
    private const CACHE_EXPIRE = 3600;

    // 缓存键前缀
    private const CACHE_KEY_GROUP_STATS = 'chat_group_stats_';
    private const CACHE_KEY_USER_GROUPS = 'chat_groups_user_';

    /**
     * 获取用户的 ext_users.id（通过 Matrix user_id）
     * @param string $matrixUserId Matrix 用户ID，例如: @u10010:im-sq01.bleiworc.xyz
     * @return int 返回 ext_users.id，如果不存在返回 0
     */
    private static function getExtUserId(string $matrixUserId): int
    {
        $user = Db::table('ext_users')
            ->select('id')
            ->where('user_id', $matrixUserId)
            ->where('status', 1)
            ->first();

        return $user ? (int)$user->id : 0;
    }

    /**
     * 获取分组统计信息（带缓存）
     * @param int $groupId 分组ID
     * @return array ['chat_count' => int, 'participant_count' => int]
     */
    public static function getGroupStats(int $groupId): array
    {
        $cacheKey = self::CACHE_KEY_GROUP_STATS . $groupId;
        $cache = Cache::get();

        // 尝试从缓存获取
        $cached = $cache->get($cacheKey);
        if ($cached !== false && $cached !== null) {
            $data = json_decode($cached, true);
            if (is_array($data) && isset($data['chat_count']) && isset($data['participant_count'])) {
                return $data;
            }
        }

        // 计算聊天数量
        $chatCount = Db::table('ext_chat_group_rooms')
            ->where('group_id', $groupId)
            ->where('status', 1)
            ->count();

        // 获取该分组下的所有房间ID
        $roomIds = Db::table('ext_chat_group_rooms')
            ->select('room_id')
            ->where('group_id', $groupId)
            ->where('status', 1)
            ->pluck('room_id')
            ->toArray();

        // 计算参与者总数（通过 room_stats_current 表）
        $participantCount = 0;
        if (!empty($roomIds)) {
            $participantCount = (int)Db::table('room_stats_current')
                ->whereIn('room_id', $roomIds)
                ->sum('joined_members');
        }

        $stats = [
            'chat_count' => $chatCount,
            'participant_count' => $participantCount
        ];

        // 存入缓存（1小时）
        $cache->setex($cacheKey, self::CACHE_EXPIRE, json_encode($stats));

        return $stats;
    }

    /**
     * 清除分组统计缓存
     * @param int $groupId 分组ID
     */
    private static function clearGroupStatsCache(int $groupId): void
    {
        $cacheKey = self::CACHE_KEY_GROUP_STATS . $groupId;
        Cache::get()->del($cacheKey);
    }

    /**
     * 清除用户分组列表缓存
     * @param int $userId 用户ID（ext_users.id）
     */
    private static function clearUserGroupsCache(int $userId): void
    {
        $cacheKey = self::CACHE_KEY_USER_GROUPS . $userId;
        Cache::get()->del($cacheKey);
    }

    /**
     * 获取分组列表
     * @param string $matrixUserId Matrix 用户ID
     * @return array ['error' => string, 'data' => array]
     */
    public static function list(string $matrixUserId): array
    {
        $extUserId = self::getExtUserId($matrixUserId);
        if ($extUserId === 0) {
            return ['error' => '用户不存在', 'data' => []];
        }

        $cacheKey = self::CACHE_KEY_USER_GROUPS . $extUserId;
        $cache = Cache::get();

        // 尝试从缓存获取
        $cached = $cache->get($cacheKey);
        if ($cached !== false && $cached !== null) {
            $data = json_decode($cached, true);
            if (is_array($data)) {
                return ['error' => '', 'data' => $data];
            }
        }

        // 从数据库查询
        $groups = Db::table('ext_chat_groups')
            ->select(
                'id',
                'user_id',
                'icon',
                'name',
                'chat_count',
                'participant_count',
                'created_at',
                'updated_at'
            )
            ->where('user_id', $extUserId)
            ->where('status', 1)
            ->orderBy('updated_at', 'DESC') // 按最后修改时间倒序
            ->get()
            ->toArray();

        // 为每个分组计算实时统计信息
        foreach ($groups as &$group) {
            $stats = self::getGroupStats((int)$group->id);
            $group->chat_count = $stats['chat_count'];
            $group->participant_count = $stats['participant_count'];
        }

        $result = array_map(function ($item) {
            return (array)$item;
        }, $groups);

        // 存入缓存（1小时）
        $cache->setex($cacheKey, self::CACHE_EXPIRE, json_encode($result));

        return ['error' => '', 'data' => $result];
    }

    /**
     * 创建分组
     * @param string $matrixUserId Matrix 用户ID
     * @param string $name 分组名称
     * @param string $icon 分组图标
     * @return array ['error' => string, 'data' => array]
     */
    public static function create(string $matrixUserId, string $name, string $icon = ''): array
    {
        $extUserId = self::getExtUserId($matrixUserId);
        if ($extUserId === 0) {
            return ['error' => '用户不存在', 'data' => []];
        }

        if (empty(trim($name))) {
            return ['error' => '分组名称不能为空', 'data' => []];
        }

        // 检查名称长度
        if (mb_strlen(trim($name)) > 200) {
            return ['error' => '分组名称过长（最大200字符）', 'data' => []];
        }

        try {
            // 让数据库自动设置时间字段（使用 DEFAULT CURRENT_TIMESTAMP）
            $groupId = Db::table('ext_chat_groups')->insertGetId([
                'user_id' => $extUserId,
                'name' => trim($name),
                'icon' => trim($icon),
                'chat_count' => 0,
                'participant_count' => 0,
                'status' => 1
            ]);

            // 清除用户分组列表缓存
            self::clearUserGroupsCache($extUserId);

            // 查询创建的分组
            $group = Db::table('ext_chat_groups')
                ->select(
                    'id',
                    'user_id',
                    'icon',
                    'name',
                    'chat_count',
                    'participant_count',
                    'created_at',
                    'updated_at'
                )
                ->where('id', $groupId)
                ->first();

            return [
                'error' => '',
                'data' => (array)$group
            ];
        } catch (\Exception $e) {
            return ['error' => '创建分组失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 修改分组
     * @param string $matrixUserId Matrix 用户ID
     * @param int $groupId 分组ID
     * @param string|null $name 分组名称（可选）
     * @param string|null $icon 分组图标（可选）
     * @return array ['error' => string, 'data' => array]
     */
    public static function update(string $matrixUserId, int $groupId, ?string $name = null, ?string $icon = null): array
    {
        $extUserId = self::getExtUserId($matrixUserId);
        if ($extUserId === 0) {
            return ['error' => '用户不存在', 'data' => []];
        }

        // 检查分组是否存在且属于当前用户
        $group = Db::table('ext_chat_groups')
            ->where('id', $groupId)
            ->where('user_id', $extUserId)
            ->where('status', 1)
            ->first();

        if (!$group) {
            return ['error' => '分组不存在或无权限', 'data' => []];
        }

        // 构建更新数据
        $updateData = [];
        if ($name !== null) {
            $name = trim($name);
            if (empty($name)) {
                return ['error' => '分组名称不能为空', 'data' => []];
            }
            if (mb_strlen($name) > 200) {
                return ['error' => '分组名称过长（最大200字符）', 'data' => []];
            }
            $updateData['name'] = $name;
        }

        if ($icon !== null) {
            $updateData['icon'] = trim($icon);
        }

        if (empty($updateData)) {
            return ['error' => '没有需要更新的字段', 'data' => []];
        }

        try {
            // 更新分组（触发器会自动更新 updated_at）
            Db::table('ext_chat_groups')
                ->where('id', $groupId)
                ->update($updateData);

            // 清除缓存
            self::clearUserGroupsCache($extUserId);
            self::clearGroupStatsCache($groupId);

            // 查询更新后的分组
            $updatedGroup = Db::table('ext_chat_groups')
                ->select(
                    'id',
                    'user_id',
                    'icon',
                    'name',
                    'chat_count',
                    'participant_count',
                    'created_at',
                    'updated_at'
                )
                ->where('id', $groupId)
                ->first();

            return [
                'error' => '',
                'data' => (array)$updatedGroup
            ];
        } catch (\Exception $e) {
            return ['error' => '更新分组失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 删除分组
     * @param string $matrixUserId Matrix 用户ID
     * @param int $groupId 分组ID
     * @return array ['error' => string, 'data' => array]
     */
    public static function delete(string $matrixUserId, int $groupId): array
    {
        $extUserId = self::getExtUserId($matrixUserId);
        if ($extUserId === 0) {
            return ['error' => '用户不存在', 'data' => []];
        }

        // 检查分组是否存在且属于当前用户
        $group = Db::table('ext_chat_groups')
            ->where('id', $groupId)
            ->where('user_id', $extUserId)
            ->where('status', 1)
            ->first();

        if (!$group) {
            return ['error' => '分组不存在或无权限', 'data' => []];
        }

        try {
            Db::beginTransaction();

            // 软删除分组
            Db::table('ext_chat_groups')
                ->where('id', $groupId)
                ->update(['status' => 2]);

            // 软删除关联的房间记录
            Db::table('ext_chat_group_rooms')
                ->where('group_id', $groupId)
                ->update(['status' => 2]);

            Db::commit();

            // 清除缓存
            self::clearUserGroupsCache($extUserId);
            self::clearGroupStatsCache($groupId);

            return ['error' => '', 'data' => []];
        } catch (\Exception $e) {
            Db::rollBack();
            return ['error' => '删除分组失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 将聊天添加到分组
     * @param string $matrixUserId Matrix 用户ID
     * @param int $groupId 分组ID
     * @param string $roomId 房间ID
     * @return array ['error' => string, 'data' => array]
     */
    public static function addRoom(string $matrixUserId, int $groupId, string $roomId): array
    {
        $extUserId = self::getExtUserId($matrixUserId);
        if ($extUserId === 0) {
            return ['error' => '用户不存在', 'data' => []];
        }

        if (empty(trim($roomId))) {
            return ['error' => '房间ID不能为空', 'data' => []];
        }

        // 检查分组是否存在且属于当前用户
        $group = Db::table('ext_chat_groups')
            ->where('id', $groupId)
            ->where('user_id', $extUserId)
            ->where('status', 1)
            ->first();

        if (!$group) {
            return ['error' => '分组不存在或无权限', 'data' => []];
        }

        // 检查房间是否已经在该分组中
        $exists = Db::table('ext_chat_group_rooms')
            ->where('group_id', $groupId)
            ->where('room_id', $roomId)
            ->where('status', 1)
            ->exists();

        if ($exists) {
            return ['error' => '房间已在该分组中', 'data' => []];
        }

        try {
            // 检查是否有已删除的记录，如果有则恢复
            $deleted = Db::table('ext_chat_group_rooms')
                ->where('group_id', $groupId)
                ->where('room_id', $roomId)
                ->where('status', 2)
                ->first();

            if ($deleted) {
                // 恢复记录（让数据库自动设置 created_at）
                Db::table('ext_chat_group_rooms')
                    ->where('id', $deleted->id)
                    ->update(['status' => 1]);
            } else {
                // 插入新记录（让数据库自动设置 created_at）
                Db::table('ext_chat_group_rooms')->insert([
                    'group_id' => $groupId,
                    'room_id' => $roomId,
                    'status' => 1
                ]);
            }

            // 更新分组的 updated_at（触发器会自动更新，这里显式更新以确保触发）
            Db::update(
                "UPDATE ext_chat_groups SET updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$groupId]
            );

            // 清除缓存
            self::clearUserGroupsCache($extUserId);
            self::clearGroupStatsCache($groupId);

            return ['error' => '', 'data' => []];
        } catch (\Exception $e) {
            return ['error' => '添加房间失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 将聊天从分组中移除
     * @param string $matrixUserId Matrix 用户ID
     * @param int $groupId 分组ID
     * @param string $roomId 房间ID
     * @return array ['error' => string, 'data' => array]
     */
    public static function removeRoom(string $matrixUserId, int $groupId, string $roomId): array
    {
        $extUserId = self::getExtUserId($matrixUserId);
        if ($extUserId === 0) {
            return ['error' => '用户不存在', 'data' => []];
        }

        if (empty(trim($roomId))) {
            return ['error' => '房间ID不能为空', 'data' => []];
        }

        // 检查分组是否存在且属于当前用户
        $group = Db::table('ext_chat_groups')
            ->where('id', $groupId)
            ->where('user_id', $extUserId)
            ->where('status', 1)
            ->first();

        if (!$group) {
            return ['error' => '分组不存在或无权限', 'data' => []];
        }

        // 检查房间是否在该分组中
        $exists = Db::table('ext_chat_group_rooms')
            ->where('group_id', $groupId)
            ->where('room_id', $roomId)
            ->where('status', 1)
            ->first();

        if (!$exists) {
            return ['error' => '房间不在该分组中', 'data' => []];
        }

        try {
            // 软删除关联记录
            Db::table('ext_chat_group_rooms')
                ->where('id', $exists->id)
                ->update(['status' => 2]);

            // 更新分组的 updated_at（触发器会自动更新，这里显式更新以确保触发）
            Db::update(
                "UPDATE ext_chat_groups SET updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$groupId]
            );

            // 清除缓存
            self::clearUserGroupsCache($extUserId);
            self::clearGroupStatsCache($groupId);

            return ['error' => '', 'data' => []];
        } catch (\Exception $e) {
            return ['error' => '移除房间失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 获取分组中的聊天列表
     * @param string $matrixUserId Matrix 用户ID
     * @param int $groupId 分组ID
     * @return array ['error' => string, 'data' => array]
     */
    public static function listRooms(string $matrixUserId, int $groupId): array
    {
        $extUserId = self::getExtUserId($matrixUserId);
        if ($extUserId === 0) {
            return ['error' => '用户不存在', 'data' => []];
        }

        // 检查分组是否存在且属于当前用户
        $group = Db::table('ext_chat_groups')
            ->where('id', $groupId)
            ->where('user_id', $extUserId)
            ->where('status', 1)
            ->first();

        if (!$group) {
            return ['error' => '分组不存在或无权限', 'data' => []];
        }

        // 查询分组中的房间列表，关联 rooms_v 视图获取房间详细信息
        $rooms = Db::table('ext_chat_group_rooms')
            ->select(
                'ext_chat_group_rooms.room_id',
                'ext_chat_group_rooms.created_at as added_at',
                'rooms_v.is_public',
                'rooms_v.creator',
                'rooms_v.creator_user_id',
                'rooms_v.creator_nickname',
                'rooms_v.room_name',
                'rooms_v.join_rules',
                'rooms_v.topic',
                'rooms_v.joined_members',
                'rooms_v.invited_members',
                'rooms_v.left_members',
                'rooms_v.banned_members',
                'rooms_v.knocked_members',
                'rooms_v.created_ts'
            )
            ->leftJoin('rooms_v', 'ext_chat_group_rooms.room_id', '=', 'rooms_v.room_id')
            ->where('ext_chat_group_rooms.group_id', $groupId)
            ->where('ext_chat_group_rooms.status', 1)
            ->orderBy('ext_chat_group_rooms.created_at', 'DESC')
            ->get()
            ->toArray();

        // 获取所有房间ID
        $roomIds = array_column($rooms, 'room_id');

        // 为每个房间查询最后一条消息
        $lastMessages = [];
        if (!empty($roomIds)) {
            // 为每个房间查询最后一条消息（使用 DISTINCT ON 或子查询优化）
            // 由于需要兼容性，我们使用循环查询，但限制每个房间只查询一条
            foreach ($roomIds as $roomId) {
                $lastMsg = Db::table('chats_v')
                    ->select(
                        'event_id',
                        'room_id',
                        'origin_server_ts',
                        'sender',
                        'nickname as sender_nickname',
                        'content'
                    )
                    ->where('room_id', $roomId)
                    ->orderBy('origin_server_ts', 'DESC')
                    ->limit(1)
                    ->first();

                if ($lastMsg) {
                    // 解析消息内容 JSON
                    $content = json_decode($lastMsg->content, true);
                    $lastMessages[$roomId] = [
                        'last_message' => [
                            'event_id' => $lastMsg->event_id ?? null,
                            'sender' => $lastMsg->sender,
                            'sender_nickname' => $lastMsg->sender_nickname,
                            'origin_server_ts' => $lastMsg->origin_server_ts,
                            'body' => $content['body'] ?? '',
                            'msgtype' => $content['msgtype'] ?? '',
                            'content' => $content
                        ]
                    ];
                } else {
                    $lastMessages[$roomId] = [
                        'last_message' => null
                    ];
                }
            }
        }

        // 合并房间信息和最后一条消息
        $result = array_map(function ($item) use ($lastMessages) {
            $roomId = $item->room_id;
            $roomData = (array)$item;

            // 添加最后一条消息信息
            if (isset($lastMessages[$roomId])) {
                $roomData = array_merge($roomData, $lastMessages[$roomId]);
            } else {
                $roomData['last_message'] = null;
            }

            return $roomData;
        }, $rooms);

        return ['error' => '', 'data' => $result];
    }
}
