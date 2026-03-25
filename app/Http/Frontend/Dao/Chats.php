<?php

declare(strict_types=1);

namespace App\Http\Frontend\Dao;

use Hyperf\DbConnection\Db;

class Chats
{
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
     * 批量获取房间的最后一条消息
     * @param array $roomIds 房间ID数组
     * @return array 以 room_id 为键的数组，包含最后一条消息信息
     */
    private static function getLastMessages(array $roomIds): array
    {
        if (empty($roomIds)) {
            return [];
        }

        $lastMessages = [];

        // 使用循环查询每个房间的最后一条消息
        // 可以考虑后续优化为批量查询
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
                $content = json_decode($lastMsg->content, true);
                $lastMessages[$roomId] = [
                    'event_id' => $lastMsg->event_id ?? null,
                    'sender' => $lastMsg->sender,
                    'sender_nickname' => $lastMsg->sender_nickname,
                    'origin_server_ts' => $lastMsg->origin_server_ts,
                    'body' => $content['body'] ?? '',
                    'msgtype' => $content['msgtype'] ?? '',
                    'content' => $content
                ];
            }
        }

        return $lastMessages;
    }

    /**
     * 获取房间的头像URL（优先使用 ext_groups.group_avatar_url）
     * @param string $roomId 房间ID
     * @return string 头像URL
     */
    private static function getRoomAvatar(string $roomId): string
    {
        // 优先查询 ext_groups 表
        $group = Db::table('ext_groups')
            ->select('group_avatar_url')
            ->where('group_id', $roomId)
            ->where('status', 1)
            ->first();

        if ($group && !empty($group->group_avatar_url)) {
            return $group->group_avatar_url;
        }

        // 如果没有，从 rooms_v 或其他地方获取
        // 这里可以根据实际需求调整
        return '';
    }

    /**
     * 格式化房间数据，添加最后消息和头像信息
     * @param array $rooms 房间数据数组
     * @return array 格式化后的房间数据数组
     */
    private static function formatRooms(array $rooms): array
    {
        if (empty($rooms)) {
            return [];
        }

        $roomIds = array_column($rooms, 'room_id');
        $lastMessages = self::getLastMessages($roomIds);

        $result = [];
        foreach ($rooms as $room) {
            $roomId = $room->room_id ?? $room['room_id'] ?? '';
            $roomData = is_object($room) ? (array)$room : $room;

            // 添加头像
            $roomData['avatar_url'] = self::getRoomAvatar($roomId);

            // 添加最后一条消息
            if (isset($lastMessages[$roomId])) {
                $roomData['last_message'] = $lastMessages[$roomId];
                $roomData['last_message_time'] = $lastMessages[$roomId]['origin_server_ts'] ?? 0;
            } else {
                $roomData['last_message'] = null;
                $roomData['last_message_time'] = 0;
            }

            $result[] = $roomData;
        }

        // 按最后消息时间倒序排序
        usort($result, function ($a, $b) {
            $timeA = $a['last_message_time'] ?? 0;
            $timeB = $b['last_message_time'] ?? 0;
            return $timeB <=> $timeA; // 倒序
        });

        return $result;
    }

    /**
     * 获取我创建的聊天室
     * @param string $matrixUserId Matrix 用户ID
     * @param int $page 页码，从1开始
     * @param int $limit 每页数量，默认50
     * @return array ['error' => string, 'data' => array, 'total' => int, 'page' => int, 'limit' => int]
     */
    public static function getCreatedRooms(string $matrixUserId, int $page = 1, int $limit = 50): array
    {
        if ($page < 1) {
            $page = 1;
        }
        if ($limit < 1) {
            $limit = 50;
        }

        $offset = ($page - 1) * $limit;

        // 查询我创建的聊天室
        $query = Db::table('rooms_v')
            ->select(
                'rooms_v.room_id',
                'rooms_v.room_name',
                'rooms_v.creator',
                'rooms_v.creator_user_id',
                'rooms_v.creator_nickname',
                'rooms_v.is_public',
                'rooms_v.join_rules',
                'rooms_v.topic',
                'rooms_v.joined_members',
                'rooms_v.invited_members',
                'rooms_v.created_ts'
            )
            ->where('rooms_v.creator', $matrixUserId);

        // 获取总数
        $total = $query->count();

        // 先获取所有房间（不限制数量，因为需要按最后消息时间排序）
        $rooms = $query->get()->toArray();

        // 格式化房间数据（包括最后消息和头像）
        $formattedRooms = self::formatRooms($rooms);

        // 分页
        $paginatedRooms = array_slice($formattedRooms, $offset, $limit);

        return [
            'error' => '',
            'data' => $paginatedRooms,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
    }

    /**
     * 获取我管理的聊天（包括群主和管理员）
     * @param string $matrixUserId Matrix 用户ID
     * @param int $page 页码，从1开始
     * @param int $limit 每页数量，默认50
     * @return array ['error' => string, 'data' => array, 'total' => int, 'page' => int, 'limit' => int]
     */
    public static function getManagedRooms(string $matrixUserId, int $page = 1, int $limit = 50): array
    {
        if ($page < 1) {
            $page = 1;
        }
        if ($limit < 1) {
            $limit = 50;
        }

        $offset = ($page - 1) * $limit;

        // 查询我管理的聊天（通过 ext_group_users 表）
        $query = Db::table('ext_group_users')
            ->select(
                'ext_group_users.group_id as room_id',
                'ext_group_users.role_type',
                'ext_group_users.member_status',
                'rooms_v.room_name',
                'rooms_v.creator',
                'rooms_v.creator_user_id',
                'rooms_v.creator_nickname',
                'rooms_v.is_public',
                'rooms_v.join_rules',
                'rooms_v.topic',
                'rooms_v.joined_members',
                'rooms_v.invited_members',
                'rooms_v.created_ts'
            )
            ->leftJoin('rooms_v', 'ext_group_users.group_id', '=', 'rooms_v.room_id')
            ->where('ext_group_users.user_id', $matrixUserId)
            ->whereIn('ext_group_users.role_type', ['owner', 'admin'])
            ->where('ext_group_users.member_status', 'active')
            ->where('ext_group_users.status', 1);

        // 获取总数
        $total = $query->count();

        // 先获取所有房间
        $rooms = $query->get()->toArray();

        // 格式化房间数据
        $formattedRooms = self::formatRooms($rooms);

        // 分页
        $paginatedRooms = array_slice($formattedRooms, $offset, $limit);

        return [
            'error' => '',
            'data' => $paginatedRooms,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
    }

    /**
     * 获取我加入的聊天
     * @param string $matrixUserId Matrix 用户ID
     * @param int $page 页码，从1开始
     * @param int $limit 每页数量，默认50
     * @return array ['error' => string, 'data' => array, 'total' => int, 'page' => int, 'limit' => int]
     */
    public static function getJoinedRooms(string $matrixUserId, int $page = 1, int $limit = 50): array
    {
        if ($page < 1) {
            $page = 1;
        }
        if ($limit < 1) {
            $limit = 50;
        }

        $offset = ($page - 1) * $limit;

        // 查询我加入的聊天
        // 通过 current_state_events 表查询 m.room.member 事件
        // 需要关联 event_json 表查询 JSON 字段中的 membership 字段

        // 先查询用户加入的所有房间ID
        // 使用 JOIN event_json 表查询 JSON 字段
        try {
            $roomIds = Db::select("
                SELECT DISTINCT cse.room_id 
                FROM current_state_events cse
                LEFT JOIN event_json ej ON cse.event_id = ej.event_id
                WHERE cse.type = 'm.room.member' 
                AND cse.state_key = ? 
                AND ej.json->>'membership' = 'join'
            ", [$matrixUserId]);

            $roomIds = array_column($roomIds, 'room_id');
        } catch (\Exception $e) {
            // 如果查询失败，尝试直接查询 current_state_events 表的 json 字段
            try {
                $roomIds = Db::select("
                    SELECT DISTINCT room_id 
                    FROM current_state_events 
                    WHERE type = 'm.room.member' 
                    AND state_key = ? 
                    AND json->>'membership' = 'join'
                ", [$matrixUserId]);

                $roomIds = array_column($roomIds, 'room_id');
            } catch (\Exception $e2) {
                // 如果都失败，返回空数组
                $roomIds = [];
            }
        }

        if (empty($roomIds)) {
            return [
                'error' => '',
                'data' => [],
                'total' => 0,
                'page' => $page,
                'limit' => $limit
            ];
        }

        // 查询房间详细信息
        $query = Db::table('rooms_v')
            ->select(
                'rooms_v.room_id',
                'rooms_v.room_name',
                'rooms_v.creator',
                'rooms_v.creator_user_id',
                'rooms_v.creator_nickname',
                'rooms_v.is_public',
                'rooms_v.join_rules',
                'rooms_v.topic',
                'rooms_v.joined_members',
                'rooms_v.invited_members',
                'rooms_v.created_ts'
            )
            ->whereIn('rooms_v.room_id', $roomIds);

        // 获取总数
        $total = $query->count();

        // 先获取所有房间
        $rooms = $query->get()->toArray();

        // 格式化房间数据
        $formattedRooms = self::formatRooms($rooms);

        // 分页
        $paginatedRooms = array_slice($formattedRooms, $offset, $limit);

        return [
            'error' => '',
            'data' => $paginatedRooms,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
    }
}
