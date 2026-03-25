<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class GroupExtDao
{
    public static function list(array $request): array
    {
        $query = Db::table('rooms_v as rv')
            ->select(
                Db::raw('rv.room_id as group_id'),
                Db::raw('COALESCE(eg.group_name, rv.room_name, \'\') as group_name'),
                Db::raw('COALESCE(eg.owner_id, rv.creator) as owner_id'),
                Db::raw('COALESCE(eg.owner_nickname, rv.creator_nickname, \'\') as owner_nickname'),
                Db::raw('COALESCE(eg.current_member_count, rv.joined_members, 0)::int as member_count'),
                Db::raw('eg.member_limit as max_members'),
                Db::raw('eg.join_restriction_type as group_restriction'),
                Db::raw("CASE WHEN eg.group_status = 'banned' THEN 'banned' ELSE 'active' END as status"),
                Db::raw('rv.created_ts as created_at'),
                Db::raw('eg.last_activity_time as last_activity_time')
            )
            ->leftJoin('ext_groups as eg', function ($join) {
                $join->on('rv.room_id', '=', 'eg.group_id')->where('eg.status', '=', 1);
            })
            ->orderBy('rv.created_ts', 'DESC');

        if (!empty($request['group_name'])) {
            $query->where(function ($q) use ($request) {
                $q->where('rv.room_name', 'like', '%' . $request['group_name'] . '%')
                    ->orWhere('eg.group_name', 'like', '%' . $request['group_name'] . '%');
            });
        }
        if (!empty($request['owner_id'])) {
            $query->where(function ($q) use ($request) {
                $q->where('rv.creator', 'like', '%' . $request['owner_id'] . '%')
                    ->orWhere('rv.creator_user_id', 'like', '%' . $request['owner_id'] . '%')
                    ->orWhere('eg.owner_id', 'like', '%' . $request['owner_id'] . '%');
            });
        }
        if (!empty($request['owner_nickname'])) {
            $query->where(function ($q) use ($request) {
                $q->where('rv.creator_nickname', 'like', '%' . $request['owner_nickname'] . '%')
                    ->orWhere('eg.owner_nickname', 'like', '%' . $request['owner_nickname'] . '%');
            });
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

    /**
     * 确保 ext_groups 记录存在；不存在时从 rooms_v 初始化一条记录。
     */
    private static function ensureExtGroup(string $groupId): ?object
    {
        $row = Db::table('ext_groups')
            ->where('group_id', $groupId)
            ->where('status', 1)
            ->first();
        if ($row) {
            return $row;
        }

        $room = Db::table('rooms_v')->where('room_id', $groupId)->first();
        if (!$room) {
            return null;
        }

        Db::table('ext_groups')->insert([
            'group_id' => $groupId,
            'group_name' => $room->room_name ?? '',
            'owner_id' => $room->creator ?? '',
            'owner_nickname' => $room->creator_nickname ?? '',
            'current_member_count' => (int)($room->joined_members ?? 0),
        ]);

        return Db::table('ext_groups')
            ->where('group_id', $groupId)
            ->where('status', 1)
            ->first();
    }

    /**
     * 修改群组状态（封禁/解封）。
     * 前端传 active/banned，数据库存 normal/banned。
     * 同步调用 Synapse Admin API 封禁/解封房间（失败仅记日志，不阻断业务）。
     */
    public static function changeStatus(array $request): array
    {
        $groupId = trim((string)($request['group_id'] ?? ''));
        $status = trim((string)($request['status'] ?? ''));

        $dbStatus = ($status === 'banned') ? 'banned' : 'normal';

        $ext = self::ensureExtGroup($groupId);
        if (!$ext) {
            return ['error' => '群组不存在', 'data' => null];
        }

        try {
            Db::table('ext_groups')
                ->where('group_id', $groupId)
                ->where('status', 1)
                ->update(['group_status' => $dbStatus]);
        } catch (\Throwable $e) {
            return ['error' => '更新群状态失败：' . $e->getMessage(), 'data' => null];
        }

        $syncErr = self::syncRoomBlockToSynapse($groupId, $dbStatus === 'banned');
        if ($syncErr !== '') {
            error_log('[GroupExtDao::changeStatus] Synapse 房间封禁同步失败（数据库已更新）: ' . $syncErr);
        }

        return ['error' => '', 'data' => null];
    }

    /**
     * 解散群组：标记 ext_groups 记录无效，并调用 Synapse Admin API 删除房间。
     */
    public static function dissolve(array $request): array
    {
        $groupId = trim((string)($request['group_id'] ?? ''));

        $room = Db::table('rooms_v')->where('room_id', $groupId)->first();
        if (!$room) {
            return ['error' => '群组不存在', 'data' => null];
        }

        try {
            Db::table('ext_groups')
                ->where('group_id', $groupId)
                ->where('status', 1)
                ->update(['status' => 2]);
        } catch (\Throwable $e) {
            return ['error' => '标记群组无效失败：' . $e->getMessage(), 'data' => null];
        }

        $syncErr = self::syncRoomDeleteToSynapse($groupId);
        if ($syncErr !== '') {
            error_log('[GroupExtDao::dissolve] Synapse 删除房间失败: ' . $syncErr);
        }

        return ['error' => '', 'data' => null];
    }

    /**
     * 转让群主：更新 ext_groups 的 owner_id/owner_nickname，新群主昵称从 ext_users 查询。
     */
    public static function transferOwnership(array $request): array
    {
        $groupId = trim((string)($request['group_id'] ?? ''));
        $newOwnerId = trim((string)($request['new_owner_id'] ?? ''));

        $ext = self::ensureExtGroup($groupId);
        if (!$ext) {
            return ['error' => '群组不存在', 'data' => null];
        }

        $newOwner = Db::table('ext_users')->where('user_id', $newOwnerId)->first();
        $nickname = $newOwner ? ($newOwner->nickname ?? '') : '';

        try {
            Db::table('ext_groups')
                ->where('group_id', $groupId)
                ->where('status', 1)
                ->update([
                    'owner_id' => $newOwnerId,
                    'owner_nickname' => $nickname,
                ]);
        } catch (\Throwable $e) {
            return ['error' => '转让群主失败：' . $e->getMessage(), 'data' => null];
        }

        return ['error' => '', 'data' => null];
    }

    /**
     * 更新群信息（名称、头像、描述、人数上限）
     */
    public static function updateInfo(array $request): array
    {
        $groupId = trim((string)($request['group_id'] ?? ''));

        $ext = self::ensureExtGroup($groupId);
        if (!$ext) {
            return ['error' => '群组不存在', 'data' => null];
        }

        $update = [];
        if (isset($request['group_name'])) {
            $update['group_name'] = trim((string)$request['group_name']);
        }
        if (isset($request['avatar_url'])) {
            $update['group_avatar_url'] = trim((string)$request['avatar_url']);
        }
        if (isset($request['description'])) {
            $update['group_description'] = trim((string)$request['description']);
        }
        if (isset($request['max_members'])) {
            $update['member_limit'] = (int)$request['max_members'];
        }

        if (empty($update)) {
            return ['error' => '', 'data' => null];
        }

        try {
            Db::table('ext_groups')
                ->where('group_id', $groupId)
                ->where('status', 1)
                ->update($update);
        } catch (\Throwable $e) {
            return ['error' => '更新群信息失败：' . $e->getMessage(), 'data' => null];
        }

        return ['error' => '', 'data' => null];
    }

    /**
     * 更新群限制设置
     */
    public static function updateRestriction(array $request): array
    {
        $groupId = trim((string)($request['group_id'] ?? ''));

        $ext = self::ensureExtGroup($groupId);
        if (!$ext) {
            return ['error' => '群组不存在', 'data' => null];
        }

        $restrictionType = trim((string)($request['restriction_type'] ?? 'unlimited'));

        try {
            Db::table('ext_groups')
                ->where('group_id', $groupId)
                ->where('status', 1)
                ->update(['join_restriction_type' => $restrictionType]);
        } catch (\Throwable $e) {
            return ['error' => '更新群限制失败：' . $e->getMessage(), 'data' => null];
        }

        return ['error' => '', 'data' => null];
    }

    // ==================== Synapse Admin API ====================

    /**
     * 发送 HTTP 请求到 Synapse Admin API（stream_socket_client 直连，
     * 避免 cURL 解析 Matrix room_id 中的特殊字符出错）
     */
    private static function synapseRequest(string $method, string $path, ?array $bodyData = null, int $timeout = 10): array
    {
        $baseUrl = trim(rtrim((string) env('SYNAPSE_ADMIN_API_URL', ''), '/'));
        $token = (string) env('SYNAPSE_ADMIN_ACCESS_TOKEN', '');
        if ($baseUrl === '' || $token === '') {
            return ['error' => '未配置 SYNAPSE_ADMIN_API_URL 或 SYNAPSE_ADMIN_ACCESS_TOKEN', 'status' => 0];
        }

        $parsed = parse_url($baseUrl);
        $scheme = $parsed['scheme'] ?? 'http';
        $host = $parsed['host'] ?? null;
        $port = $parsed['port'] ?? null;
        if ($host === null && isset($parsed['path']) && $parsed['path'] !== '') {
            $p = $parsed['path'];
            if (strpos($p, ':') !== false) {
                [$host, $port] = explode(':', $p, 2);
                $port = (int) $port;
            } else {
                $host = $p;
            }
        }
        $host = $host ?? '127.0.0.1';
        $port = $port ?? ($scheme === 'https' ? 443 : 80);

        $body = '';
        if ($bodyData !== null) {
            $body = json_encode($bodyData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($body === false) {
                return ['error' => '请求体编码失败', 'status' => 0];
            }
        }

        $req = $method . ' ' . $path . " HTTP/1.1\r\n"
            . "Host: {$host}:{$port}\r\n"
            . "Authorization: Bearer " . $token . "\r\n"
            . "Content-Type: application/json\r\n"
            . "Content-Length: " . strlen($body) . "\r\n"
            . "Connection: close\r\n\r\n"
            . $body;

        $target = ($scheme === 'https' ? 'ssl://' : '') . $host . ':' . $port;
        $errNo = 0;
        $errStr = '';
        $ctx = stream_context_create([
            'socket' => ['timeout' => $timeout],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $fp = @stream_socket_client($target, $errNo, $errStr, $timeout, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            return ['error' => '无法连接 ' . $target . ' (' . $errStr . ')', 'status' => 0];
        }

        if (fwrite($fp, $req) === false) {
            fclose($fp);
            return ['error' => '写入请求失败', 'status' => 0];
        }

        $line = fgets($fp);
        fclose($fp);
        if ($line === false || !preg_match('#^HTTP/\d\.\d\s+(\d+)#', $line, $m)) {
            return ['error' => '无效响应', 'status' => 0];
        }
        return ['error' => '', 'status' => (int) $m[1]];
    }

    private static function encodeRoomId(string $roomId): string
    {
        return str_replace(
            ['/', '?', '#', ' ', '%', "\0"],
            ['%2F', '%3F', '%23', '%20', '%25', ''],
            $roomId
        );
    }

    /**
     * Synapse: 封禁/解封房间
     * PUT /_synapse/admin/v1/rooms/:room_id/block
     */
    private static function syncRoomBlockToSynapse(string $roomId, bool $blocked): string
    {
        $path = '/_synapse/admin/v1/rooms/' . self::encodeRoomId($roomId) . '/block';
        $result = self::synapseRequest('PUT', $path, ['block' => $blocked]);
        if ($result['error'] !== '') {
            return 'Synapse 房间封禁同步失败：' . $result['error'];
        }
        $status = $result['status'];
        if ($status < 200 || $status >= 300) {
            return 'Synapse 房间封禁同步失败：HTTP ' . $status;
        }
        return '';
    }

    /**
     * Synapse: 删除房间（同步 v1 端点，超时 60 秒）
     * POST /_synapse/admin/v1/rooms/:room_id/delete
     */
    private static function syncRoomDeleteToSynapse(string $roomId): string
    {
        $path = '/_synapse/admin/v1/rooms/' . self::encodeRoomId($roomId) . '/delete';
        $result = self::synapseRequest('POST', $path, ['purge' => true], 60);
        if ($result['error'] !== '') {
            return 'Synapse 删除房间失败：' . $result['error'];
        }
        $status = $result['status'];
        if ($status < 200 || $status >= 300) {
            return 'Synapse 删除房间失败：HTTP ' . $status;
        }
        return '';
    }
}
