<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use App\Service\ProfileSyncService;
use App\Service\SynapseMediaService;
use Hyperf\DbConnection\Db;

class UserExtDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_users')
            ->leftJoin('ext_user_levels as l', 'ext_users.member_level_id', '=', 'l.id')
            ->leftJoin('ext_user_vanity_numbers as ref_vanity', function ($join) {
                $join->on('ref_vanity.user_id', '=', 'ext_users.referrer_id')
                    ->where('ref_vanity.status', '=', 1);
            })
            ->leftJoin('ext_user_extends as ex', 'ex.user_id', '=', 'ext_users.user_id')
            ->select(
                'ext_users.id',
                'ext_users.user_id',
                'ext_users.nickname',
                'ext_users.avatar_url',
                'ext_users.personal_description',
                'ext_users.login_ip',
                'ext_users.device_model',
                'ext_users.current_version',
                'ext_users.referrer_id',
                Db::raw("COALESCE(ext_users.referrer_vanity_id, ref_vanity.vanity_number) as referrer_vanity_id"),
                'ext_users.subordinate_referrals_count',
                'ext_users.registration_time',
                'ext_users.last_login_time',
                'ext_users.bound_phone',
                'ext_users.bound_email',
                'ext_users.account_status',
                Db::raw("COALESCE(l.name, '普通用户') as user_level"),
                Db::raw('COALESCE(ex.current_note_count, ext_users.current_note_count) as current_note_count'),
                Db::raw('COALESCE(ex.current_super_seat_note_count, ext_users.current_super_seat_note_count) as current_super_seat_note_count'),
                Db::raw('COALESCE(ex.note_count_limit, ext_users.note_count_limit) as note_count_limit'),
                Db::raw('COALESCE(ex.super_seat_note_limit, ext_users.super_seat_note_limit) as super_seat_note_limit'),
                Db::raw('COALESCE(ex.friends_count, ext_users.friends_count) as friends_count')
            )
            ->orderBy('ext_users.registration_time', 'DESC');

        // 用户ID：ext_users 表主键 id（数字，精确匹配）
        if (!empty($request['id']) && (int)$request['id'] > 0) {
            $query->where('ext_users.id', (int)$request['id']);
        }
        if (!empty($request['nickname'])) {
            $query->where('ext_users.nickname', 'like', '%' . $request['nickname'] . '%');
        }
        if (!empty($request['login_ip'])) {
            $query->where('ext_users.login_ip', 'like', '%' . $request['login_ip'] . '%');
        }
        if (isset($request['referrer_id']) && $request['referrer_id'] !== '') {
            $query->where('ext_users.referrer_id', $request['referrer_id']);
        }
        if (!empty($request['registration_time_start'])) {
            $query->where('ext_users.registration_time', '>=', $request['registration_time_start']);
        }
        if (!empty($request['registration_time_end'])) {
            $query->where('ext_users.registration_time', '<=', $request['registration_time_end'] . ' 23:59:59');
        }
        if (!empty($request['last_login_time_start'])) {
            $query->where('ext_users.last_login_time', '>=', $request['last_login_time_start']);
        }
        if (!empty($request['last_login_time_end'])) {
            $query->where('ext_users.last_login_time', '<=', $request['last_login_time_end'] . ' 23:59:59');
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
     * 用户详情：以 ext_users 为主，合并 ext_user_extends 的笔记/好友等字段（业务实际写入在 ext_user_extends）
     */
    public static function get(array $request): array
    {
        $userId = $request['user_id'] ?? '';
        $row = Db::table('ext_users')
            ->select('id', 'user_id', 'nickname', 'avatar_url', 'personal_description',
                     'login_ip', 'device_id', 'device_model', 'current_version', 'referrer_id',
                     'referrer_vanity_id', 'subordinate_referrals_count', 'registration_time',
                     'last_login_time', 'bound_phone', 'bound_email', 'member_level_id',
                     'member_expiration_time', 'identity_badge_id', 'vanity_badge_id',
                     'credit_badge_id', 'pure_badge_enabled', 'circle_badge_id', 'reputation_value', 'account_status',
                     'penalty_type', 'chat_prohibited', 'note_creation_prohibited',
                     'super_seat_note_prohibited', 'current_note_count',
                     'current_super_seat_note_count', 'note_count_limit',
                     'super_seat_note_limit', 'joined_circles_list', 'friends_count',
                     'status', 'remark', 'created_at', 'updated_at')
            ->where('user_id', $userId)
            ->first();

        if (!$row) {
            return ['error' => '用户不存在', 'data' => null];
        }

        $data = (array)$row;

        // 靓号：vanity_badge_id 对应 ext_user_vanity_numbers.id，仅 status=1（正常使用）时展示；已回收/已转让显示 "0"
        $vanityBadgeId = (int)($data['vanity_badge_id'] ?? 0);
        if ($vanityBadgeId > 0) {
            $vanityRow = Db::table('ext_user_vanity_numbers')
                ->where('id', $vanityBadgeId)
                ->where('status', 1)
                ->value('vanity_number');
            $data['vanity_number'] = $vanityRow !== null ? (string)$vanityRow : '0';
        } else {
            $data['vanity_number'] = '0';
        }

        // 合并 ext_user_extends：笔记数量、超级坐席笔记、好友数量等以业务表为准
        $extend = Db::table('ext_user_extends')
            ->where('user_id', $userId)
            ->first();
        if ($extend) {
            $extend = (array)$extend;
            $data['current_note_count'] = $extend['current_note_count'] ?? $data['current_note_count'];
            $data['current_super_seat_note_count'] = $extend['current_super_seat_note_count'] ?? $data['current_super_seat_note_count'];
            $data['note_count_limit'] = $extend['note_count_limit'] ?? $data['note_count_limit'];
            $data['super_seat_note_limit'] = $extend['super_seat_note_limit'] ?? $data['super_seat_note_limit'];
            $data['friends_count'] = $extend['friends_count'] ?? $data['friends_count'];
        }

        return [
            'error' => '',
            'data' => $data,
        ];
    }

    /**
     * 更新用户会员与徽章信息（更新 ext_users 的 member_level_id、member_expiration_time、identity_badge_id、credit_badge_id、vanity_badge_id）
     * 会员等级截止时间：根据所选等级的 validity_period（天）自动计算，0 则置 NULL。
     * 靓号：vanity_number 为 0 或空则清除；否则检查 ext_user_vanity_numbers 占用，无则新建并更新 vanity_badge_id。
     *
     * @param array $request user_id（必填）, member_level_id, identity_badge_id, credit_badge_id, vanity_number
     */
    public static function updateMembership(array $request): array
    {
        $userId = trim((string)($request['user_id'] ?? ''));
        if ($userId === '') {
            return ['error' => '用户ID不能为空', 'data' => null];
        }

        $ext = Db::table('ext_users')->where('user_id', $userId)->first();
        if (!$ext) {
            return ['error' => '用户不存在', 'data' => null];
        }

        $extUserId = (int)$ext->id;
        $memberLevelId = (int)($request['member_level_id'] ?? 0);
        $identityBadgeId = (int)($request['identity_badge_id'] ?? 0);
        $creditBadgeId = (int)($request['credit_badge_id'] ?? 0);
        $pureBadgeEnabled = isset($request['pure_badge_enabled']) ? (bool)$request['pure_badge_enabled'] : false;
        $circleBadgeId = (int)($request['circle_badge_id'] ?? 0);
        $vanityNumber = trim((string)($request['vanity_number'] ?? ''));
        if ($vanityNumber === '') {
            $vanityNumber = '0';
        }

        // 根据所选等级的 validity_period 计算 member_expiration_time（有效期以天为单位）
        $expirationTime = null;
        if ($memberLevelId > 0) {
            $level = Db::table('ext_user_levels')->where('id', $memberLevelId)->first();
            if ($level && isset($level->validity_period) && (int)$level->validity_period > 0) {
                $expirationTime = date('Y-m-d H:i:s', strtotime('+' . (int)$level->validity_period . ' days'));
            }
        }

        $vanityBadgeId = 0;
        if ($vanityNumber !== '0') {
            // 检查 ext_user_vanity_numbers 中该靓号是否已被占用（status=1）
            $existing = Db::table('ext_user_vanity_numbers')
                ->where('vanity_number', $vanityNumber)
                ->where('status', 1)
                ->first();
            if ($existing) {
                $existingUserId = (int)$existing->user_id;
                if ($existingUserId !== $extUserId) {
                    return ['error' => '该靓号已被占用', 'data' => null];
                }
                // 已是当前用户，保持原 vanity_badge_id
                $vanityBadgeId = (int)$existing->id;
            } else {
                // 新建靓号记录，type_id=10000（内部保留号禁售）
                $vanityBadgeId = (int)Db::table('ext_user_vanity_numbers')->insertGetId([
                    'user_id' => $extUserId,
                    'vanity_number' => $vanityNumber,
                    'type_id' => 10000,
                    'is_in_selected_pool' => false,
                    'is_frontend_display' => true,
                    'points_paid' => 0,
                    'discount_applied' => 1.00,
                    'purchase_method' => '后台直接分配',
                    'purchase_reason' => '',
                    'status' => 1,
                ]);
            }
        }

        // 一用户一靓号：确定新靓号后，将该用户下其余 status=1 的靓号置为已回收（与靓号管理添加/修改效果一致）
        if ($vanityBadgeId > 0) {
            Db::table('ext_user_vanity_numbers')
                ->where('user_id', $extUserId)
                ->where('id', '!=', $vanityBadgeId)
                ->where('status', 1)
                ->update(['status' => 2]);
        }

        try {
            $update = [
                'member_level_id' => $memberLevelId,
                'member_expiration_time' => $expirationTime,
                'identity_badge_id' => $identityBadgeId,
                'credit_badge_id' => $creditBadgeId,
                'pure_badge_enabled' => $pureBadgeEnabled,
                'circle_badge_id' => $circleBadgeId,
                'vanity_badge_id' => $vanityBadgeId,
            ];
            Db::table('ext_users')->where('user_id', $userId)->update($update);
        } catch (\Throwable $e) {
            return ['error' => '更新失败：' . $e->getMessage(), 'data' => null];
        }

        return ['error' => '', 'data' => null];
    }

    /**
     * 更新用户头像、昵称与个人描述（同步更新 ext_users、profiles 及所有展示头像/昵称的业务表）。
     * 头像/昵称变更时通过 ProfileSyncService 同步：ext_circle_users、ext_group_users、ext_circle_content、
     * ext_circle_notice、ext_circle_invite、circle_content_report、ext_user_notes，并调用 Synapse Admin API 更新头像。
     *
     * @param array $request user_id（必填）, avatar_url, nickname, personal_description
     */
    public static function updateProfile(array $request): array
    {
        $userId = trim((string)($request['user_id'] ?? ''));
        if ($userId === '') {
            return ['error' => '用户ID不能为空', 'data' => null];
        }

        $ext = Db::table('ext_users')->where('user_id', $userId)->first();
        if (!$ext) {
            return ['error' => '用户不存在', 'data' => null];
        }

        $avatarUrl = isset($request['avatar_url']) ? trim((string)$request['avatar_url']) : null;
        $nickname = isset($request['nickname']) ? trim((string)$request['nickname']) : null;
        $personalDescription = isset($request['personal_description']) ? trim((string)$request['personal_description']) : null;

        try {
            $extUpdate = [];
            if ($avatarUrl !== null) {
                $extUpdate['avatar_url'] = $avatarUrl;
            }
            if ($nickname !== null) {
                $extUpdate['nickname'] = $nickname;
            }
            if ($personalDescription !== null) {
                $extUpdate['personal_description'] = $personalDescription;
            }
            if (!empty($extUpdate)) {
                Db::table('ext_users')->where('user_id', $userId)->update($extUpdate);
            }

            if ($avatarUrl !== null) {
                $profileAvatar = $avatarUrl === '' ? null : $avatarUrl;
                Db::table('profiles')->where('full_user_id', $userId)->update(['avatar_url' => $profileAvatar]);

                ProfileSyncService::syncAvatar($userId, $avatarUrl === '' ? '' : $avatarUrl);

                // 同步到 Synapse（room_memberships、user_directory）：需 mxc 格式，非 mxc 时上传到 Synapse 媒体库获取
                $mxcForSynapse = $avatarUrl;
                if ($avatarUrl !== '' && !str_starts_with($avatarUrl, 'mxc://')) {
                    $localFile = SynapseMediaService::resolvePathToFile($avatarUrl);
                    if ($localFile !== null) {
                        $mime = SynapseMediaService::getMimeFromPath($avatarUrl);
                        $uploadResult = SynapseMediaService::uploadFile($localFile, $mime);
                        if ($uploadResult['error'] === '' && $uploadResult['mxc_url'] !== null) {
                            $mxcForSynapse = $uploadResult['mxc_url'];
                        } else {
                            error_log('[updateProfile] Synapse 媒体上传失败（将跳过 room_memberships 同步）: ' . ($uploadResult['error'] ?? ''));
                        }
                    } else {
                        error_log('[updateProfile] 无法解析头像路径为本地文件，跳过 Synapse 同步: ' . $avatarUrl);
                    }
                }
                $syncErr = self::syncProfileToSynapse($userId, $mxcForSynapse);
                if ($syncErr !== '') {
                    error_log('[updateProfile] Synapse 头像同步失败（业务表已更新）: ' . $syncErr);
                }
            }

            if ($nickname !== null) {
                ProfileSyncService::syncNickname($userId, $nickname);
            }
        } catch (\Throwable $e) {
            return ['error' => '更新失败：' . $e->getMessage(), 'data' => null];
        }

        return ['error' => '', 'data' => null];
    }

    /**
     * 同步用户头像到 Synapse（PUT /_synapse/admin/v2/users/:user_id）。
     * 更新 room_memberships 与 user_directory，使 1对1 聊天、群聊、通讯录即时显示新头像。
     * 仅当 avatar_url 为 mxc:// 格式时传递（Synapse 要求），否则跳过并记录日志。
     *
     * @param string $userId Matrix 用户 ID
     * @param string $avatarUrl 头像 URL（路径或 mxc://）
     * @return string 错误信息，空串表示成功
     */
    private static function syncProfileToSynapse(string $userId, string $avatarUrl): string
    {
        $baseUrl = trim(rtrim((string) env('SYNAPSE_ADMIN_API_URL', ''), '/'));
        $token = (string) env('SYNAPSE_ADMIN_ACCESS_TOKEN', '');
        if ($baseUrl === '' || $token === '') {
            return '未配置 SYNAPSE_ADMIN_API_URL 或 SYNAPSE_ADMIN_ACCESS_TOKEN';
        }

        // Synapse 仅接受 mxc:// 格式头像，非 mxc 时跳过（业务表已更新，主页等可正常显示）
        $bodyData = [];
        if (str_starts_with($avatarUrl, 'mxc://')) {
            $bodyData['avatar_url'] = $avatarUrl;
        } elseif ($avatarUrl === '') {
            $bodyData['avatar_url'] = null;
        }

        if (empty($bodyData)) {
            return '';
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

        $pathUserId = str_replace(['/', '?', '#', ' ', '%', "\0"], ['%2F', '%3F', '%23', '%20', '%25', ''], $userId);
        $path = '/_synapse/admin/v2/users/' . $pathUserId;

        $body = json_encode($bodyData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($body === false) {
            return 'Synapse 头像同步失败：请求体编码失败';
        }

        $req = "PUT " . $path . " HTTP/1.1\r\n"
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
            'socket' => ['timeout' => 10],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $fp = @stream_socket_client($target, $errNo, $errStr, 10, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            return '无法连接 ' . $target . ' (' . $errStr . ')';
        }

        if (fwrite($fp, $req) === false) {
            fclose($fp);
            return '写入请求失败';
        }

        $line = fgets($fp);
        fclose($fp);
        if ($line === false || !preg_match('#^HTTP/\d\.\d\s+(\d+)#', $line, $m)) {
            return 'Synapse 响应无效';
        }
        $statusCode = (int) $m[1];
        if ($statusCode < 200 || $statusCode >= 300) {
            return 'Synapse 返回 HTTP ' . $statusCode;
        }
        return '';
    }

    /**
     * 从 HEADS_LIST_FILE 随机读取一行，返回以 / 开头的头像路径（不含域名，前端自行拼接）。
     */
    public static function getRandomAvatarPath(): array
    {
        $file = trim((string)env('HEADS_LIST_FILE', ''));
        if ($file === '') {
            error_log('[getRandomAvatarPath] HEADS_LIST_FILE 未配置或为空');
            return ['error' => '头像列表文件未配置或不可读', 'data' => null];
        }
        if (!file_exists($file)) {
            error_log('[getRandomAvatarPath] 文件不存在: ' . $file);
            return ['error' => '头像列表文件未配置或不可读', 'data' => null];
        }
        if (!is_readable($file)) {
            $currentUser = (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) ? (posix_getpwuid(posix_geteuid())['name'] ?? 'unknown') : 'unknown';
            error_log('[getRandomAvatarPath] 文件不可读: ' . $file . ', 当前进程用户: ' . $currentUser);
            return ['error' => '头像列表文件未配置或不可读', 'data' => null];
        }

        $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines || count($lines) === 0) {
            return ['error' => '头像列表为空', 'data' => null];
        }

        $line = trim($lines[array_rand($lines)]);
        if ($line === '') {
            return ['error' => '读取到空行', 'data' => null];
        }

        if ($line[0] !== '/') {
            $line = '/' . $line;
        }

        return ['error' => '', 'data' => ['url' => $line, 'path' => $line]];
    }

    /**
     * 修改用户扩展状态，并同步 Synapse users.deactivated（同库）
     *
     * account_status 映射（与管理后台「权限状态操作」一致）：
     * - normal：可登录、可聊天，清除所有处罚标记（chat / note / super）
     * - banned：可登录，对应「坐席禁用」= 禁止超级坐席笔记（super_seat_note_prohibited = true）
     * - suspended：对应「暂停服务」，禁止登录（deactivated = 1）
     * - limited：对应「限制功能」，禁止聊天 + 禁止创建笔记（chat_prohibited + note_creation_prohibited = true）
     *
     * 禁止聊天统一走 applyChatProhibit（更新 ext_users.chat_prohibited + Synapse shadow_ban）。
     */
    public static function changeStatus(array $request): array
    {
        $userId = $request['user_id'] ?? '';
        $accountStatus = $request['account_status'] ?? '';

        $ext = Db::table('ext_users')->where('user_id', $userId)->first();
        if (!$ext) {
            return ['error' => '用户不存在', 'data' => null];
        }

        Db::beginTransaction();
        try {
            // 根据 account_status 计算笔记/超级坐席笔记的处罚标记
            $noteProhibited = in_array($accountStatus, ['limited'], true);
            $superSeatProhibited = in_array($accountStatus, ['banned'], true);

            Db::table('ext_users')
                ->where('user_id', $userId)
                ->update([
                    'account_status' => $accountStatus,
                    'note_creation_prohibited' => $noteProhibited,
                    'super_seat_note_prohibited' => $superSeatProhibited,
                ]);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            return ['error' => '更新失败：' . $e->getMessage(), 'data' => null];
        }

        // 仅 suspended（暂停服务）视为禁止登录；其余状态恢复/保持可登录
        $deactivated = ($accountStatus === 'suspended');
        $deactivateError = self::syncDeactivateToSynapse($userId, $deactivated);
        if ($deactivateError !== '') {
            return ['error' => $deactivateError, 'data' => null];
        }

        // 仅 limited（限制功能）映射为禁止聊天；normal / banned / suspended 恢复或保持可聊天
        $shouldProhibitChat = in_array($accountStatus, ['limited'], true);
        $syncError = self::applyChatProhibit($userId, $shouldProhibitChat);
        if ($syncError !== '') {
            return ['error' => $syncError, 'data' => null];
        }

        return ['error' => '', 'data' => null];
    }

    /**
     * 设置用户处罚项；禁止聊天/笔记/超级坐席笔记
     * 禁止聊天统一通过 applyChatProhibit 处理（更新 ext_users.chat_prohibited + Synapse shadow_ban），避免多处重复代码。
     */
    public static function setPenalty(array $request): array
    {
        $userId = (string)($request['user_id'] ?? '');
        $chatProhibited = (bool)($request['chat_prohibited'] ?? false);
        $noteProhibited = (bool)($request['note_creation_prohibited'] ?? false);
        $superSeatProhibited = (bool)($request['super_seat_note_prohibited'] ?? false);

        $ext = Db::table('ext_users')->where('user_id', $userId)->first();
        if (!$ext) {
            return ['error' => '用户不存在', 'data' => null];
        }

        Db::beginTransaction();
        try {
            Db::table('ext_users')
                ->where('user_id', $userId)
                ->update([
                    'note_creation_prohibited' => $noteProhibited,
                    'super_seat_note_prohibited' => $superSeatProhibited,
                ]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            return ['error' => '设置处罚失败：' . $e->getMessage(), 'data' => null];
        }

        // 禁止聊天交给统一方法处理（含 ext_users.chat_prohibited + Synapse shadow_ban）
        $syncError = self::applyChatProhibit($userId, $chatProhibited);
        if ($syncError !== '') {
            return ['error' => $syncError, 'data' => null];
        }

        return ['error' => '', 'data' => null];
    }

    /**
     * 统一处理聊天禁用：更新 ext_users.chat_prohibited，并同步 Synapse shadow_ban
     */
    private static function applyChatProhibit(string $userId, bool $prohibited): string
    {
        try {
            Db::table('ext_users')
                ->where('user_id', $userId)
                ->update(['chat_prohibited' => $prohibited]);
        } catch (\Throwable $e) {
            return '更新聊天禁用状态失败：' . $e->getMessage();
        }

        $syncError = self::syncShadowBanToSynapse($userId, $prohibited);
        if ($syncError !== '') {
            return $syncError;
        }

        return '';
    }

    /**
     * 同步登录禁用状态到 Synapse（使用 locked，避免 deactivated 清空密码导致恢复后无法登录）。
     * 暂停时设置 locked=true；恢复时 locked=false 并清除可能存在的 deactivated。
     * 使用 Admin API PUT /_synapse/admin/v2/users/:user_id。
     */
    private static function syncDeactivateToSynapse(string $userId, bool $deactivated): string
    {
        $baseUrl = trim(rtrim((string) env('SYNAPSE_ADMIN_API_URL', ''), '/'));
        $token = (string) env('SYNAPSE_ADMIN_ACCESS_TOKEN', '');
        if ($baseUrl === '' || $token === '') {
            return '未配置 SYNAPSE_ADMIN_API_URL 或 SYNAPSE_ADMIN_ACCESS_TOKEN，暂停服务无法同步到 Synapse';
        }

        $parsed = parse_url($baseUrl);
        $scheme = $parsed['scheme'] ?? 'http';
        $host = $parsed['host'] ?? null;
        $port = $parsed['port'] ?? null;
        // 无 scheme 时 parse_url 会把 127.0.0.1:8072 放进 path，需拆成 host:port
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

        // path 中 user_id 仅编码会破坏请求行的字符，保留 @ 和 :
        $pathUserId = str_replace(['/', '?', '#', ' ', '%', "\0"], ['%2F', '%3F', '%23', '%20', '%25', ''], $userId);
        $path = '/_synapse/admin/v2/users/' . $pathUserId;

        // 使用 locked 而非 deactivated：Synapse 的 deactivated 会清空密码，恢复后用户无法用原密码登录；
        // locked 仅禁止登录，不清空密码，恢复后原密码仍可用。
        // 恢复时同时传 deactivated: false，以清除可能存在的旧 deactivated 状态（历史暂停）。
        $body = json_encode(
            $deactivated
                ? ['locked' => true]
                : ['locked' => false, 'deactivated' => false],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        if ($body === false) {
            return 'Synapse 登录禁用同步失败：请求体编码失败';
        }

        $req = "PUT " . $path . " HTTP/1.1\r\n"
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
            'socket' => ['timeout' => 10],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $fp = @stream_socket_client($target, $errNo, $errStr, 10, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            return 'Synapse 登录禁用同步失败：无法连接 ' . $target . ' (' . $errStr . ')';
        }

        if (fwrite($fp, $req) === false) {
            fclose($fp);
            return 'Synapse 登录禁用同步失败：写入请求失败';
        }

        $line = fgets($fp);
        fclose($fp);
        if ($line === false || !preg_match('#^HTTP/\d\.\d\s+(\d+)#', $line, $m)) {
            return 'Synapse 登录禁用同步失败：无效响应';
        }
        $statusCode = (int) $m[1];
        if ($statusCode < 200 || $statusCode >= 300) {
            return 'Synapse 登录禁用同步失败：HTTP ' . $statusCode;
        }
        // 暂停时踢掉所有设备，使 token 失效
        if ($deactivated) {
            $kickErr = self::kickUserDevicesFromSynapse($userId);
            if ($kickErr !== '') {
                return $kickErr;
            }
        }
        return '';
    }

    /**
     * 踢掉用户所有设备（使 access token 失效），不修改密码。
     * 直接删除 access_tokens 和 refresh_tokens 表记录（web-api 与 Synapse 共用同一数据库）。
     */
    private static function kickUserDevicesFromSynapse(string $userId): string
    {
        try {
            Db::delete('DELETE FROM access_tokens WHERE user_id = ?', [$userId]);
            Db::delete('DELETE FROM refresh_tokens WHERE user_id = ?', [$userId]);
        } catch (\Throwable $e) {
            return 'Synapse 踢下线失败：' . $e->getMessage();
        }
        return '';
    }

    /**
     * 调用 Synapse Admin API 设置/取消 shadow_ban（POST=禁言，DELETE=取消）
     * 使用 stream 直连发原始 HTTP，避免 cURL 解析 path 中的 : 导致 "URL rejected: Malformed input"。
     * 若前置 Nginx 未放行 DELETE 会返回 405，可将 SYNAPSE_ADMIN_API_URL 设为内网（如 http://127.0.0.1:8072）直连。
     */
    private static function syncShadowBanToSynapse(string $userId, bool $shadowBanned): string
    {
        $baseUrl = trim(rtrim((string) env('SYNAPSE_ADMIN_API_URL', ''), '/'));
        $token = (string) env('SYNAPSE_ADMIN_ACCESS_TOKEN', '');
        if ($baseUrl === '' || $token === '') {
            return '未配置 SYNAPSE_ADMIN_API_URL 或 SYNAPSE_ADMIN_ACCESS_TOKEN，禁止聊天无法同步到 Synapse';
        }

        $parsed = parse_url($baseUrl);
        $scheme = $parsed['scheme'] ?? 'http';
        $host = $parsed['host'] ?? null;
        $port = $parsed['port'] ?? null;
        // 无 scheme 时 parse_url 会把 127.0.0.1:8072 放进 path，需拆成 host:port
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

        // path 中 user_id 仅编码会破坏请求行的字符，保留 @ 和 :
        $pathUserId = str_replace(['/', '?', '#', ' ', '%', "\0"], ['%2F', '%3F', '%23', '%20', '%25', ''], $userId);
        $path = '/_synapse/admin/v1/users/' . $pathUserId . '/shadow_ban';

        $method = $shadowBanned ? 'POST' : 'DELETE';
        $req = $method . ' ' . $path . " HTTP/1.1\r\n"
            . "Host: {$host}:{$port}\r\n"
            . "Authorization: Bearer " . $token . "\r\n"
            . "Content-Type: application/json\r\n"
            . "Content-Length: 0\r\n"
            . "Connection: close\r\n\r\n";

        $target = ($scheme === 'https' ? 'ssl://' : '') . $host . ':' . $port;
        $errNo = 0;
        $errStr = '';
        $ctx = stream_context_create([
            'socket' => ['timeout' => 10],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $fp = @stream_socket_client($target, $errNo, $errStr, 10, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            return 'Synapse 禁言同步失败：无法连接 ' . $target . ' (' . $errStr . ')';
        }

        if (fwrite($fp, $req) === false) {
            fclose($fp);
            return 'Synapse 禁言同步失败：写入请求失败';
        }

        $line = fgets($fp);
        fclose($fp);
        if ($line === false || !preg_match('#^HTTP/\d\.\d\s+(\d+)#', $line, $m)) {
            return 'Synapse 禁言同步失败：无效响应';
        }
        $statusCode = (int) $m[1];
        if ($statusCode >= 200 && $statusCode < 300) {
            return '';
        }
        return 'Synapse 禁言同步失败：HTTP ' . $statusCode;
    }

    /**
     * 管理员修改用户密码：仅通过 Synapse Admin API reset_password 更新 users.password_hash 并踢掉该用户所有设备，强制重新登录。
     */
    public static function setPassword(array $request): array
    {
        $userId = trim((string)($request['user_id'] ?? ''));
        $newPassword = (string)($request['new_password'] ?? '');

        $ext = Db::table('ext_users')->where('user_id', $userId)->first();
        if (!$ext) {
            return ['error' => '用户不存在', 'data' => null];
        }

        $err = self::syncSetPasswordToSynapse($userId, $newPassword);
        if ($err !== '') {
            return ['error' => $err, 'data' => null];
        }
        return ['error' => '', 'data' => null];
    }

    /**
     * 调用 Synapse Admin API 重置用户密码并踢掉所有设备（POST /_synapse/admin/v1/reset_password/:user_id）。
     * Synapse 会更新 users.password_hash 并使该用户所有 access token 失效。
     */
    private static function syncSetPasswordToSynapse(string $userId, string $newPassword): string
    {
        $baseUrl = trim(rtrim((string) env('SYNAPSE_ADMIN_API_URL', ''), '/'));
        $token = (string) env('SYNAPSE_ADMIN_ACCESS_TOKEN', '');
        if ($baseUrl === '' || $token === '') {
            return '未配置 SYNAPSE_ADMIN_API_URL 或 SYNAPSE_ADMIN_ACCESS_TOKEN，无法修改密码';
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

        $pathUserId = str_replace(['/', '?', '#', ' ', '%', "\0"], ['%2F', '%3F', '%23', '%20', '%25', ''], $userId);
        $path = '/_synapse/admin/v1/reset_password/' . $pathUserId;

        $body = json_encode([
            'new_password' => $newPassword,
            'logout_devices' => true,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($body === false) {
            return '修改密码失败：请求体编码失败';
        }

        $req = "POST " . $path . " HTTP/1.1\r\n"
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
            'socket' => ['timeout' => 10],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $fp = @stream_socket_client($target, $errNo, $errStr, 10, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            return '修改密码失败：无法连接 Synapse（' . $errStr . '）';
        }

        if (fwrite($fp, $req) === false) {
            fclose($fp);
            return '修改密码失败：写入请求失败';
        }

        $line = fgets($fp);
        fclose($fp);
        if ($line === false || !preg_match('#^HTTP/\d\.\d\s+(\d+)#', $line, $m)) {
            return '修改密码失败：无效响应';
        }
        $statusCode = (int) $m[1];
        if ($statusCode >= 200 && $statusCode < 300) {
            return '';
        }
        return '修改密码失败：Synapse 返回 HTTP ' . $statusCode;
    }

    /**
     * 用户奖励：增加笔记上限 / 超级坐席笔记上限。
     * 优先更新 ext_user_extends（业务表），同时同步 ext_users 保持一致。
     */
    public static function setReward(array $request): array
    {
        $userId = trim((string)($request['user_id'] ?? ''));
        if ($userId === '') {
            return ['error' => '用户ID不能为空', 'data' => null];
        }

        $ext = Db::table('ext_users')->where('user_id', $userId)->first();
        if (!$ext) {
            return ['error' => '用户不存在', 'data' => null];
        }

        $noteInc = (int)($request['note_limit_increase'] ?? 0);
        $superInc = (int)($request['super_seat_limit_increase'] ?? 0);

        Db::beginTransaction();
        try {
            $extend = Db::table('ext_user_extends')->where('user_id', $userId)->first();

            if ($extend) {
                $updates = [];
                if ($noteInc > 0) {
                    $updates['note_count_limit'] = (int)$extend->note_count_limit + $noteInc;
                }
                if ($superInc > 0) {
                    $updates['super_seat_note_limit'] = (int)$extend->super_seat_note_limit + $superInc;
                }
                if (!empty($updates)) {
                    Db::table('ext_user_extends')->where('user_id', $userId)->update($updates);
                }
            }

            $extUpdates = [];
            if ($noteInc > 0) {
                $extUpdates['note_count_limit'] = (int)$ext->note_count_limit + $noteInc;
            }
            if ($superInc > 0) {
                $extUpdates['super_seat_note_limit'] = (int)$ext->super_seat_note_limit + $superInc;
            }
            if (!empty($extUpdates)) {
                Db::table('ext_users')->where('user_id', $userId)->update($extUpdates);
            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            return ['error' => '奖励设置失败：' . $e->getMessage(), 'data' => null];
        }

        return ['error' => '', 'data' => null];
    }

    /**
     * 通过 Synapse Admin API 发送系统消息（Server Notice）给指定用户。
     * POST /_synapse/admin/v1/send_server_notice
     */
    public static function sendSystemMessage(array $request): array
    {
        $userId = trim((string)($request['user_id'] ?? ''));
        $messageText = (string)($request['message'] ?? '');

        if ($userId === '') {
            return ['error' => '用户ID不能为空', 'data' => null];
        }

        $ext = Db::table('ext_users')->where('user_id', $userId)->first();
        if (!$ext) {
            return ['error' => '用户不存在', 'data' => null];
        }

        $baseUrl = trim(rtrim((string) env('SYNAPSE_ADMIN_API_URL', ''), '/'));
        $token = (string) env('SYNAPSE_ADMIN_ACCESS_TOKEN', '');
        if ($baseUrl === '' || $token === '') {
            return ['error' => '未配置 SYNAPSE_ADMIN_API_URL 或 SYNAPSE_ADMIN_ACCESS_TOKEN，无法发送系统消息', 'data' => null];
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

        $path = '/_synapse/admin/v1/send_server_notice';

        $body = json_encode([
            'user_id' => $userId,
            'content' => [
                'msgtype' => 'm.text',
                'body' => $messageText,
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($body === false) {
            return ['error' => '发送系统消息失败：请求体编码失败', 'data' => null];
        }

        $req = "POST " . $path . " HTTP/1.1\r\n"
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
            'socket' => ['timeout' => 10],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $fp = @stream_socket_client($target, $errNo, $errStr, 10, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            return ['error' => '发送系统消息失败：无法连接 Synapse（' . $errStr . '）', 'data' => null];
        }

        if (fwrite($fp, $req) === false) {
            fclose($fp);
            return ['error' => '发送系统消息失败：写入请求失败', 'data' => null];
        }

        $line = fgets($fp);
        fclose($fp);
        if ($line === false || !preg_match('#^HTTP/\d\.\d\s+(\d+)#', $line, $m)) {
            return ['error' => '发送系统消息失败：Synapse 响应无效', 'data' => null];
        }
        $statusCode = (int) $m[1];
        if ($statusCode < 200 || $statusCode >= 300) {
            return ['error' => '发送系统消息失败：Synapse 返回 HTTP ' . $statusCode, 'data' => null];
        }

        return ['error' => '', 'data' => null];
    }

    /**
     * 将已过期的会员降级为普通用户（member_level_id=10000，member_expiration_time=null）。
     * 由定时任务每 5 分钟调用。
     *
     * @return array{error: string, data: array{affected: int}}
     */
    public static function expireMembers(): array
    {
        $affected = Db::table('ext_users')
            ->whereNotNull('member_expiration_time')
            ->where('member_expiration_time', '<', date('Y-m-d H:i:s'))
            ->where('member_level_id', '>', 10000)
            ->update([
                'member_level_id' => 10000,
                'member_expiration_time' => null,
            ]);
        return ['error' => '', 'data' => ['affected' => $affected]];
    }
}
