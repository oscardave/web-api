<?php

declare(strict_types=1);

namespace App\Http\Frontend\Dao;

use App\Helpers\PhoneHelper;
use App\Http\Backend\Dao\UserExtDao;
use App\Service\ProfileSyncService;
use App\Common\Cache;
use Hyperf\DbConnection\Db;

class Users
{
    /**
     * 根据 access_token 获取用户信息
     * @param string $accessToken
     * @return array ['error' => string, 'data' => array]
     */
    public static function getUserByToken(string $accessToken): array
    {
        if (empty($accessToken)) {
            return ['error' => 'Access token is required', 'data' => []];
        }

        $tokenRecord = Db::table('access_tokens')
            ->select('user_id', 'device_id')
            ->where('token', $accessToken)
            ->first();

        if (!$tokenRecord) {
            return ['error' => 'Invalid access token', 'data' => []];
        }

        return [
            'error' => '',
            'data' => [
                'user_id' => $tokenRecord->user_id,
                'device_id' => $tokenRecord->device_id
            ]
        ];
    }

    /**
     * 根据 ext_users.id（数字ID）获取 user_id（Matrix ID）
     * @param int $id ext_users 表的数字ID
     * @return array ['error' => string, 'user_id' => string]
     */
    public static function getUserIdById(int $id): array
    {
        if ($id <= 0) {
            return ['error' => 'Invalid user id', 'user_id' => ''];
        }

        $extUser = Db::table('ext_users')
            ->select('user_id')
            ->where('id', $id)
            ->where('status', 1)
            ->first();

        if (!$extUser) {
            return ['error' => '用户不存在', 'user_id' => ''];
        }

        return [
            'error' => '',
            'user_id' => $extUser->user_id
        ];
    }

    /**
     * 获取用户账户最新信息（ext_user_accounts 表）
     * @param int $extUserId ext_users.id
     * @return array ['error' => string, 'data' => array]
     */
    public static function getAccount(int $extUserId): array
    {
        if ($extUserId <= 0) {
            return ['error' => '用户ID无效', 'data' => []];
        }

        $account = Db::table('ext_user_accounts')
            ->select('point_amount', 'point_frozen_amount', 'credit_amount', 'credit_frozen_amount', 'updated_at')
            ->where('user_id', $extUserId)
            ->first();

        if (!$account) {
            return [
                'error' => '',
                'data' => [
                    'point_amount' => 0,
                    'point_frozen_amount' => 0,
                    'credit_amount' => 0,
                    'credit_frozen_amount' => 0,
                    'updated_at' => null,
                ],
            ];
        }

        return [
            'error' => '',
            'data' => [
                'point_amount' => (int)($account->point_amount ?? 0),
                'point_frozen_amount' => (int)($account->point_frozen_amount ?? 0),
                'credit_amount' => (int)($account->credit_amount ?? 0),
                'credit_frozen_amount' => (int)($account->credit_frozen_amount ?? 0),
                'updated_at' => $account->updated_at ?? null,
            ],
        ];
    }

    /**
     * 初始化 ext_users 记录（如果 profiles 存在但 ext_users 不存在）
     * @param string $userId Matrix ID，例如: @u10010:im-sq01.bleiworc.xyz
     * @return array ['error' => string, 'ext_user_id' => int] 成功时返回 ext_users.id
     */
    public static function initializeExtUser(string $userId): array
    {
        // 检查 ext_users 是否已存在
        $extUser = Db::table('ext_users')
            ->select('id')
            ->where('user_id', $userId)
            ->where('status', 1)
            ->first();

        if ($extUser) {
            // 已存在，直接返回 id
            return ['error' => '', 'ext_user_id' => $extUser->id];
        }

        // 检查 profiles 表是否存在该用户
        $profile = Db::table('profiles')
            ->select('avatar_url')
            ->where('full_user_id', $userId)
            ->first();

        if (!$profile) {
            return ['error' => '用户不存在于 profiles 表，无法初始化', 'ext_user_id' => 0];
        }

        try {
            // 从 user_id 提取信息：@u10010:im-sq01.bleiworc.xyz 或 @alice:im-sq01.bleiworc.xyz
            $localpart = self::extractLocalpart($userId);

            // 提取域名：@u10010:im-sq01.bleiworc.xyz -> im-sq01.bleiworc.xyz
            $userIdWithoutAt = ltrim($userId, '@');
            $domainParts = explode(':', $userIdWithoutAt, 2);
            $domain = $domainParts[1] ?? '';

            if (empty($domain)) {
                return ['error' => '无法提取域名', 'ext_user_id' => 0];
            }

            // 生成邮箱：localpart@domain，例如 u10010@im-sq01.bleiworc.xyz 或 alice@im-sq01.bleiworc.xyz
            $generatedEmail = $localpart . '@' . $domain;

            // 插入 ext_users 表（使用数据库自增ID，不指定 id 字段）
            Db::beginTransaction();
            try {
                // 插入新记录，让数据库自动生成 id
                $extUserId = Db::table('ext_users')->insertGetId([
                    'user_id' => $userId,
                    'nickname' => $localpart, // 使用 localpart 作为 nickname（可能是 u10010、alice 等任意格式）
                    'avatar_url' => $profile->avatar_url ?? '',
                    'bound_type' => 'mail', // 默认为 mail
                    'bound_value' => $generatedEmail,
                    'bound_email' => $generatedEmail,
                    'bound_phone' => '',
                    'login_ip' => '',
                    'device_id' => '',
                    'device_model' => '',
                    'gender' => 'unknown', // 未设置时默认为未知
                    'status' => 1
                ]);

                // 使用原生SQL更新时间字段为数据库时间
                Db::update(
                    "UPDATE ext_users SET registration_time = CURRENT_TIMESTAMP, last_login_time = CURRENT_TIMESTAMP WHERE id = ?",
                    [$extUserId]
                );

                Db::commit();

                return ['error' => '', 'ext_user_id' => $extUserId];
            } catch (\Exception $e) {
                Db::rollBack();
                return ['error' => '补全用户信息失败：' . $e->getMessage(), 'ext_user_id' => 0];
            }
        } catch (\Exception $e) {
            return ['error' => '处理用户信息失败：' . $e->getMessage(), 'ext_user_id' => 0];
        }
    }

    /**
     * 获取用户完整资料
     * @param string $userId 目标用户ID
     * @param string $currentUserId 当前登录用户ID（用于查询 remark_name）
     * @return array ['error' => string, 'data' => array]
     */
    public static function profile(string $userId, string $currentUserId = ''): array
    {
        // 从 profiles 表获取 Synapse 标准字段
        $profile = Db::table('profiles')
            ->select('displayname', 'avatar_url')
            ->where('full_user_id', $userId)
            ->first();

        // 从 ext_users 表获取扩展字段
        $extUser = Db::table('ext_users')
            ->select(
                'id',
                'nickname',
                'avatar_url',
                'personal_description',
                'remark',
                'chat_prohibited',
                'note_creation_prohibited',
                'bound_phone',
                'bound_email',
                'gender',
                'vanity_badge_id',
                'reputation_value',
                'member_level_id',
                'member_expiration_time',
                'identity_badge_id',
                'credit_badge_id',
                'pure_badge_enabled',
                'circle_badge_id'
            )
            ->where('user_id', $userId)
            ->where('status', 1)
            ->first();

        // 如果 profile 存在但 extUser 不存在，说明是通过 Synapse 直接注册的用户，需要补全 ext_users 记录
        // 修改时间：2024-12-19
        // 修改内容：当通过 Synapse 直接注册的用户查询资料时，自动补全 ext_users 表记录
        if ($profile && !$extUser) {
            $initResult = self::initializeExtUser($userId);
            if ($initResult['error'] !== '') {
                return ['error' => $initResult['error'], 'data' => []];
            }

            // 重新查询 extUser
            $extUser = Db::table('ext_users')
                ->select(
                    'id',
                    'nickname',
                    'avatar_url',
                    'personal_description',
                    'remark',
                    'chat_prohibited',
                    'note_creation_prohibited',
                    'bound_phone',
                    'bound_email',
                    'gender',
                    'vanity_badge_id',
                    'reputation_value',
                    'member_level_id',
                    'member_expiration_time',
                    'identity_badge_id',
                    'credit_badge_id',
                    'pure_badge_enabled',
                    'circle_badge_id'
                )
                ->where('user_id', $userId)
                ->where('status', 1)
                ->first();
        }

        if (!$extUser) {
            return ['error' => '用户不存在', 'data' => []];
        }

        // 判断是否靓号并获取靓号数值：vanity_badge_id > 0 且对应记录 status=1（正常使用）；已回收/已转让不展示
        $isVanity = false;
        $vanityNumber = '0';
        if (!empty($extUser->vanity_badge_id) && (int)$extUser->vanity_badge_id > 0) {
            $vanityRow = Db::table('ext_user_vanity_numbers')
                ->where('id', (int)$extUser->vanity_badge_id)
                ->where('status', 1)
                ->value('vanity_number');
            if ($vanityRow !== null) {
                $isVanity = true;
                $vanityNumber = (string)$vanityRow;
            }
        }
        if (!$isVanity) {
            // 方法2：查询 ext_user_vanity_numbers 表，根据 ext_users.id 查找是否存在 status=1 的记录
            $vanityRecord = Db::table('ext_user_vanity_numbers')
                ->where('user_id', $extUser->id)
                ->where('status', 1)
                ->first();
            if ($vanityRecord && !empty($vanityRecord->vanity_number)) {
                $isVanity = true;
                $vanityNumber = (string)$vanityRecord->vanity_number;
            }
        }

        // 查询诚信保申请状态（是否有待审核的申请）
        $hasPendingEnsureApplication = false;
        try {
            $ensureRecord = Db::table('ext_user_ensures')
                ->where('user_id', $extUser->id)
                ->where('status', 1)  // 1-未审核
                ->first();
            $hasPendingEnsureApplication = !empty($ensureRecord);
        } catch (\Exception $e) {
            // 查询失败不影响主流程，默认为 false
        }

        // 查询 remark_name（当前用户对目标用户的备注名称）
        $remarkName = '';
        // 如果当前用户查看的不是自己的资料，从 ext_user_remarks 表查询备注名称
        if (!empty($currentUserId) && $userId !== $currentUserId) {
            try {
                $remarkRecord = Db::table('ext_user_remarks')
                    ->select('remark_name')
                    ->where('user_id', $currentUserId)
                    ->where('remark_user_id', $userId)
                    ->where('status', 1)
                    ->first();

                if ($remarkRecord) {
                    $remarkName = $remarkRecord->remark_name ?? '';
                }
            } catch (\Exception $e) {
                // 查询失败不影响主流程，默认为空字符串
            }
        }

        // 查询可用积分、诚信保（不论本人或他人资料均返回；ext_user_accounts.user_id = ext_users.id）
        $pointAmount = 0;
        $creditAmount = 0;
        $pointFrozenAmount = 0;
        $creditFrozenAmount = 0;
        try {
            $account = Db::table('ext_user_accounts')
                ->select('point_amount', 'point_frozen_amount', 'credit_amount', 'credit_frozen_amount')
                ->where('user_id', $extUser->id)
                ->first();
            if ($account) {
                $pointAmount = (int)($account->point_amount ?? 0);
                $pointFrozenAmount = (int)($account->point_frozen_amount ?? 0);
                $creditAmount = (int)($account->credit_amount ?? 0);
                $creditFrozenAmount = (int)($account->credit_frozen_amount ?? 0);
            }
        } catch (\Exception $e) {
            // 查询失败不影响主流程，默认为 0
        }

        // 查询 note_access（当前用户对目标用户的笔记访问权限）
        $noteAccess = null;
        // 如果当前用户查看的不是自己的资料，从 ext_user_permissions 表查询笔记访问权限
        if (!empty($currentUserId) && $userId !== $currentUserId) {
            try {
                $noteAccess = \App\Http\Frontend\Dao\UserPermissions::getNoteAccess($currentUserId, $userId);
                // 如果返回 null，表示未设置权限，默认为 true（允许访问）
                if ($noteAccess === null) {
                    $noteAccess = true;
                }
            } catch (\Exception $e) {
                // 查询失败不影响主流程，默认为 true（允许访问）
                $noteAccess = true;
            }
        }

        // 合并数据（$profile 可能为空，如仅存在 ext_users 无 profiles 时）
        $data = [
            'id' => $extUser->id,
            'user_id' => $userId,
            'displayname' => $profile?->displayname ?? $extUser->nickname,
            // web-api 返回 HTTP 路径供 C 端拼接展示；ext_users 存路径，profiles 可能被 Synapse 写为 mxc
            'avatar_url' => $extUser->avatar_url ?: ($profile?->avatar_url ?? ''),
            'nickname' => $extUser->nickname,
            'personal_description' => $extUser->personal_description,
            'remark' => $extUser->remark,
            'remark_name' => $remarkName,
            'chat_prohibited' => $extUser->chat_prohibited,
            'note_creation_prohibited' => $extUser->note_creation_prohibited,
            'note_access' => $noteAccess, // 当前用户对目标用户的笔记访问权限
            'bound_phone' => $extUser->bound_phone,
            'bound_email' => $extUser->bound_email,
            'has_bound_phone' => !empty($extUser->bound_phone),
            'has_bound_email' => !empty($extUser->bound_email),
            'gender' => !empty($extUser->gender) ? $extUser->gender : 'unknown',
            'is_vanity' => $isVanity,
            'vanity_number' => $vanityNumber,
            'reputation_value' => $extUser->reputation_value ?? 0,
            'has_pending_ensure_application' => $hasPendingEnsureApplication,
            'member_level_id' => (($mid = (int)($extUser->member_level_id ?? 0)) === 10000) ? 0 : $mid,
            'member_level_type' => self::getMemberLevelName((int)($extUser->member_level_id ?? 0)),
            'member_level_name' => self::getMemberLevelName((int)($extUser->member_level_id ?? 0)),
            'member_expiration_time' => $extUser->member_expiration_time ?? null,
            'identity_badge_id' => (int)($extUser->identity_badge_id ?? 0),
            'identity_badge_name' => self::getBadgeName((int)($extUser->identity_badge_id ?? 0)),
            'credit_badge_id' => (int)($extUser->credit_badge_id ?? 0),
            'credit_badge_name' => self::getBadgeName((int)($extUser->credit_badge_id ?? 0)),
            'integrity_score_required' => self::getCreditBadgeIntegrityScoreRequired((int)($extUser->credit_badge_id ?? 0)),
            'pure_badge_enabled' => (bool)($extUser->pure_badge_enabled ?? false),
            'circle_badge_id' => (int)($extUser->circle_badge_id ?? 0),
            'circle_badge_name' => \App\Http\Frontend\Dao\ConfigsDao::getCircleBadgeName((int)($extUser->circle_badge_id ?? 0)),
            'point_amount' => $pointAmount,
            'point_frozen_amount' => $pointFrozenAmount,
            'credit_amount' => $creditAmount,
            'credit_frozen_amount' => $creditFrozenAmount
        ];

        $isOwnProfile = !empty($currentUserId) && $userId === $currentUserId;
        if (!$isOwnProfile) {
            $data['bound_phone'] = self::maskPhone($data['bound_phone']);
            $data['bound_email'] = self::maskEmail($data['bound_email']);
        }

        return ['error' => '', 'data' => $data];
    }

    /**
     * 对手机号进行脱敏：保留前3位和后4位，中间替换为 ****
     * E.164 格式（如 +8613812345678）保留国家码 + 前3位 + **** + 后4位
     */
    private static function maskPhone(string $phone): string
    {
        $phone = trim($phone);
        if ($phone === '') {
            return '';
        }

        $digits = $phone;
        $prefix = '';

        if (str_starts_with($phone, '+')) {
            $plusPart = substr($phone, 1);
            if (strlen($plusPart) >= 2 && $plusPart[0] === '8' && $plusPart[1] === '6') {
                $prefix = '+86';
                $digits = substr($plusPart, 2);
            } elseif (strlen($plusPart) >= 1) {
                for ($i = 1; $i <= min(3, strlen($plusPart)); $i++) {
                    $candidate = substr($plusPart, 0, $i);
                    if (strlen($plusPart) - $i >= 7) {
                        $prefix = '+' . $candidate;
                        $digits = substr($plusPart, $i);
                    }
                }
                if ($prefix === '') {
                    $prefix = '+';
                    $digits = $plusPart;
                }
            }
        }

        $len = strlen($digits);
        if ($len <= 4) {
            return $prefix . str_repeat('*', $len);
        }
        if ($len <= 7) {
            return $prefix . substr($digits, 0, 1) . '****' . substr($digits, -2);
        }
        return $prefix . substr($digits, 0, 3) . '****' . substr($digits, -4);
    }

    /**
     * 对邮箱进行脱敏：保留首字符和 @domain，中间替换为 ****
     * 例如 john@example.com → j****@example.com
     */
    private static function maskEmail(string $email): string
    {
        $email = trim($email);
        if ($email === '') {
            return '';
        }

        $atPos = strrpos($email, '@');
        if ($atPos === false) {
            return '****';
        }

        $local = substr($email, 0, $atPos);
        $domain = substr($email, $atPos);

        if (strlen($local) <= 1) {
            return $local . '****' . $domain;
        }
        return $local[0] . '****' . $domain;
    }

    /**
     * 根据会员等级 ID 获取等级名称（ext_user_levels.name）
     */
    private static function getMemberLevelName(int $memberLevelId): string
    {
        if ($memberLevelId <= 0) {
            return '';
        }
        $row = Db::table('ext_user_levels')->where('id', $memberLevelId)->first();
        return $row && !empty($row->name) ? trim((string)$row->name) : '';
    }

    /**
     * 根据徽章 ID 获取徽章名称（ext_user_badges.name）
     */
    private static function getBadgeName(int $badgeId): string
    {
        if ($badgeId <= 0) {
            return '';
        }
        $row = Db::table('ext_user_badges')->where('id', $badgeId)->first();
        return $row && !empty($row->name) ? trim((string)$row->name) : '';
    }

    /**
     * 根据诚信徽章 ID 获取该徽章所需诚信保分值（ext_user_badges.integrity_score_required）
     * 用于 profile 接口返回当前佩戴的诚信徽章对应的分值要求。
     */
    private static function getCreditBadgeIntegrityScoreRequired(int $creditBadgeId): int
    {
        if ($creditBadgeId <= 0) {
            return 0;
        }
        $row = Db::table('ext_user_badges')->where('id', $creditBadgeId)->first();
        return $row ? (int)($row->integrity_score_required ?? 0) : 0;
    }

    /**
     * 设置显示名（同步更新 profiles 和 ext_users）
     * @param string $userId
     * @param string $displayname
     * @return array ['error' => string, 'data' => array]
     */
    public static function setDisplayname(string $userId, string $displayname): array
    {
        // 验证长度
        if (mb_strlen($displayname) > 256) {
            return ['error' => 'Displayname is too long (max 256)', 'data' => []];
        }

        $displaynameToSet = trim($displayname);
        if ($displaynameToSet === '') {
            $displaynameToSet = null;
        }

        Db::beginTransaction();
        try {
            // 提取 user_id（去掉 @ 和域名）
            $localpart = self::extractLocalpart($userId);

            // 更新 profiles 表
            Db::table('profiles')
                ->where('full_user_id', $userId)
                ->update(['displayname' => $displaynameToSet]);

            // 同步更新 ext_users 表
            Db::table('ext_users')
                ->where('user_id', $userId)
                ->update(['nickname' => $displaynameToSet ?? '']);

            Db::commit();

            return ['error' => '', 'data' => []];
        } catch (\Exception $e) {
            Db::rollBack();
            return ['error' => '设置显示名失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 设置头像（同步更新 profiles、ext_users、业务表及 Synapse）。
     * 委托 UserExtDao::updateProfile 实现，确保管理后台与 C 端修改头像时均同步到 Synapse（1v1 聊天、群聊、通讯录）。
     *
     * @param string $userId
     * @param string $avatarUrl 头像 URL（HTTP 路径或 mxc://）
     * @return array ['error' => string, 'data' => array]
     */
    public static function setAvatar(string $userId, string $avatarUrl): array
    {
        if (mb_strlen($avatarUrl) > 1000) {
            return ['error' => 'Avatar URL is too long (max 1000)', 'data' => []];
        }

        $result = UserExtDao::updateProfile([
            'user_id' => $userId,
            'avatar_url' => trim($avatarUrl),
        ]);

        return $result['error'] !== '' ? ['error' => $result['error'], 'data' => []] : ['error' => '', 'data' => []];
    }

    /**
     * 设置昵称（同步更新 ext_users 和 users）
     * @param string $userId
     * @param string $nickname
     * @return array ['error' => string, 'data' => array]
     */
    public static function setNickname(string $userId, string $nickname): array
    {
        // 验证长度（ext_users.nickname 和 users.nickname 都是 VARCHAR(100)）
        if (mb_strlen($nickname) > 100) {
            return ['error' => 'Nickname is too long (max 100)', 'data' => []];
        }

        $nicknameToSet = trim($nickname);
        if ($nicknameToSet === '') {
            return ['error' => 'Nickname cannot be empty', 'data' => []];
        }

        Db::beginTransaction();
        try {
            // 更新 ext_users 表
            Db::table('ext_users')
                ->where('user_id', $userId)
                ->update(['nickname' => $nicknameToSet]);

            // 同步更新 users 表（users.name 存储完整的 Matrix ID，与 ext_users.user_id 相同）
            Db::table('users')
                ->where('name', $userId)
                ->update(['nickname' => $nicknameToSet]);

            ProfileSyncService::syncNickname($userId, $nicknameToSet);

            Db::commit();

            return ['error' => '', 'data' => []];
        } catch (\Exception $e) {
            Db::rollBack();
            return ['error' => '设置昵称失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 设置性别
     * @param string $userId
     * @param string $gender
     * @return array ['error' => string, 'data' => array]
     */
    public static function setGender(string $userId, string $gender): array
    {
        // 验证长度（ext_users.gender 是 VARCHAR(20)）
        if (mb_strlen($gender) > 20) {
            return ['error' => 'Gender is too long (max 20)', 'data' => []];
        }

        $genderToSet = trim($gender);

        // 验证性别值：允许 'male', 'female', 'other' 或空字符串
        $allowedGenders = ['male', 'female', 'other', ''];
        if (!in_array($genderToSet, $allowedGenders, true)) {
            return ['error' => 'Invalid gender value. Allowed values: male, female, other, or empty string', 'data' => []];
        }

        try {
            Db::table('ext_users')
                ->where('user_id', $userId)
                ->update(['gender' => $genderToSet]);

            return ['error' => '', 'data' => []];
        } catch (\Exception $e) {
            return ['error' => '设置性别失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 设置个性签名
     * @param string $userId
     * @param string $sign
     * @return array ['error' => string, 'data' => array]
     */
    public static function setSign(string $userId, string $sign): array
    {
        try {
            Db::table('ext_users')
                ->where('user_id', $userId)
                ->update(['personal_description' => trim($sign)]);

            return ['error' => '', 'data' => []];
        } catch (\Exception $e) {
            return ['error' => '设置个性签名失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 设置备注（旧方法，保留用于向后兼容）
     * @param string $userId
     * @param string $remark
     * @return array ['error' => string, 'data' => array]
     */
    public static function setRemark(string $userId, string $remark): array
    {
        try {
            Db::table('ext_users')
                ->where('user_id', $userId)
                ->update(['remark' => trim($remark)]);

            return ['error' => '', 'data' => []];
        } catch (\Exception $e) {
            return ['error' => '设置备注失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 绑定手机号（需先调用发送手机验证码接口，verify_type=bind_account），支持多国家
     * @param string $userId 当前登录用户 Matrix ID
     * @param string $phone 手机号（E.164 或中国 11 位）
     * @param string $verifyCode 6 位验证码
     * @param string|null $countryCode 可选国家码（如 86、1）
     * @return array ['error' => string, 'data' => array]
     */
    public static function bindPhone(string $userId, string $phone, string $verifyCode, ?string $countryCode = null): array
    {
        $phone = trim($phone);
        $verifyCode = trim($verifyCode);

        if ($phone === '') {
            return ['error' => '手机号不能为空', 'data' => []];
        }
        $e164 = PhoneHelper::normalizeToE164($phone, $countryCode);
        if ($e164 === '') {
            return ['error' => '手机号格式不正确', 'data' => []];
        }
        if ($verifyCode === '') {
            return ['error' => '验证码不能为空', 'data' => []];
        }

        try {
            $codeRecord = Db::table('ext_verify_codes')
                ->where('contact_type', 'phone')
                ->where('contact_value', $e164)
                ->where('verify_type', 'bind_account')
                ->where('verify_code', $verifyCode)
                ->where('status', 1)
                ->where('is_used', false)
                ->where('is_expired', false)
                ->whereRaw('expired_at > CURRENT_TIMESTAMP')
                ->first();

            if (!$codeRecord) {
                return ['error' => '验证码无效、已过期或已使用', 'data' => []];
            }

            $phoneValues = PhoneHelper::boundPhoneQueryValues($phone, $countryCode);
            $existing = $phoneValues !== []
                ? Db::table('ext_users')
                    ->where('user_id', '!=', $userId)
                    ->whereIn('bound_phone', $phoneValues)
                    ->where('status', 1)
                    ->first()
                : null;

            if ($existing) {
                return ['error' => '该手机号已被其他用户绑定', 'data' => []];
            }

            Db::beginTransaction();
            try {
                Db::table('ext_users')
                    ->where('user_id', $userId)
                    ->update(['bound_phone' => $e164]);

                Db::update(
                    'UPDATE ext_verify_codes SET is_used = true, used_at = CURRENT_TIMESTAMP WHERE id = ?',
                    [$codeRecord->id]
                );

                Db::commit();
                return ['error' => '', 'data' => []];
            } catch (\Exception $e) {
                Db::rollBack();
                return ['error' => '绑定失败：' . $e->getMessage(), 'data' => []];
            }
        } catch (\Exception $e) {
            return ['error' => '系统错误，请稍后重试', 'data' => []];
        }
    }

    /**
     * 绑定邮箱（需先调用发送邮箱验证码接口，verify_type=bind_account）
     * @param string $userId 当前登录用户 Matrix ID
     * @param string $email 邮箱地址
     * @param string $verifyCode 6 位验证码
     * @return array ['error' => string, 'data' => array]
     */
    public static function bindEmail(string $userId, string $email, string $verifyCode): array
    {
        $email = strtolower(trim($email));
        $verifyCode = trim($verifyCode);

        if ($email === '') {
            return ['error' => '邮箱不能为空', 'data' => []];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => '邮箱格式不正确', 'data' => []];
        }
        if ($verifyCode === '') {
            return ['error' => '验证码不能为空', 'data' => []];
        }

        try {
            $codeRecord = Db::table('ext_verify_codes')
                ->where('contact_type', 'mail')
                ->whereRaw('LOWER(contact_value) = ?', [$email])
                ->where('verify_type', 'bind_account')
                ->where('verify_code', $verifyCode)
                ->where('status', 1)
                ->where('is_used', false)
                ->where('is_expired', false)
                ->whereRaw('expired_at > CURRENT_TIMESTAMP')
                ->first();

            if (!$codeRecord) {
                return ['error' => '验证码无效、已过期或已使用', 'data' => []];
            }

            $existing = Db::table('ext_users')
                ->where('user_id', '!=', $userId)
                ->where('bound_email', $email)
                ->where('status', 1)
                ->first();

            if ($existing) {
                return ['error' => '该邮箱已被其他用户绑定', 'data' => []];
            }

            Db::beginTransaction();
            try {
                Db::table('ext_users')
                    ->where('user_id', $userId)
                    ->update(['bound_email' => $email]);

                Db::update(
                    'UPDATE ext_verify_codes SET is_used = true, used_at = CURRENT_TIMESTAMP WHERE id = ?',
                    [$codeRecord->id]
                );

                Db::commit();
                return ['error' => '', 'data' => []];
            } catch (\Exception $e) {
                Db::rollBack();
                return ['error' => '绑定失败：' . $e->getMessage(), 'data' => []];
            }
        } catch (\Exception $e) {
            return ['error' => '系统错误，请稍后重试', 'data' => []];
        }
    }

    /**
     * 换绑手机第一步：验证旧手机验证码
     * 验证成功后在 Redis 存储换绑会话（10分钟有效），返回 rebind_token 供第二步使用
     * @param string $userId 当前登录用户 Matrix ID
     * @param string $verifyCode 旧手机收到的验证码
     * @return array ['error' => string, 'data' => array]
     */
    public static function verifyOldPhone(string $userId, string $verifyCode): array
    {
        $verifyCode = trim($verifyCode);
        if ($verifyCode === '') {
            return ['error' => '验证码不能为空', 'data' => []];
        }

        $extUser = Db::table('ext_users')
            ->select('id', 'bound_phone')
            ->where('user_id', $userId)
            ->where('status', 1)
            ->first();

        if (!$extUser || empty($extUser->bound_phone)) {
            return ['error' => '当前账号未绑定手机号', 'data' => []];
        }

        $oldPhone = $extUser->bound_phone;

        try {
            $codeRecord = Db::table('ext_verify_codes')
                ->where('contact_type', 'phone')
                ->where('contact_value', $oldPhone)
                ->where('verify_type', 'rebind_verify_old')
                ->where('verify_code', $verifyCode)
                ->where('status', 1)
                ->where('is_used', false)
                ->where('is_expired', false)
                ->whereRaw('expired_at > CURRENT_TIMESTAMP')
                ->first();

            if (!$codeRecord) {
                return ['error' => '验证码无效、已过期或已使用', 'data' => []];
            }

            Db::update(
                'UPDATE ext_verify_codes SET is_used = true, used_at = CURRENT_TIMESTAMP WHERE id = ?',
                [$codeRecord->id]
            );

            $rebindToken = bin2hex(random_bytes(32));
            $redis = Cache::get();
            $redis->set("rebind_session:{$userId}:phone", json_encode([
                'old_value' => $oldPhone,
                'rebind_token' => $rebindToken,
            ]), 600);

            return ['error' => '', 'data' => ['rebind_token' => $rebindToken]];
        } catch (\Exception $e) {
            return ['error' => '系统错误，请稍后重试', 'data' => []];
        }
    }

    /**
     * 换绑手机第二步：用新手机验证码 + rebind_token 完成换绑
     * @param string $userId 当前登录用户 Matrix ID
     * @param string $phone 新手机号
     * @param string $verifyCode 新手机收到的验证码
     * @param string $rebindToken 第一步返回的 rebind_token
     * @param string|null $countryCode 可选国家码
     * @return array ['error' => string, 'data' => array]
     */
    public static function rebindPhone(string $userId, string $phone, string $verifyCode, string $rebindToken, ?string $countryCode = null): array
    {
        $phone = trim($phone);
        $verifyCode = trim($verifyCode);
        $rebindToken = trim($rebindToken);

        if ($phone === '') {
            return ['error' => '新手机号不能为空', 'data' => []];
        }
        $e164 = PhoneHelper::normalizeToE164($phone, $countryCode);
        if ($e164 === '') {
            return ['error' => '手机号格式不正确', 'data' => []];
        }
        if ($verifyCode === '') {
            return ['error' => '验证码不能为空', 'data' => []];
        }
        if ($rebindToken === '') {
            return ['error' => '缺少换绑凭证，请先完成旧手机验证', 'data' => []];
        }

        try {
            $redis = Cache::get();
            $sessionJson = $redis->get("rebind_session:{$userId}:phone");
            if (!$sessionJson) {
                return ['error' => '换绑会话已过期，请重新验证旧手机', 'data' => []];
            }
            $session = json_decode($sessionJson, true);
            if (($session['rebind_token'] ?? '') !== $rebindToken) {
                return ['error' => '换绑凭证无效', 'data' => []];
            }

            $codeRecord = Db::table('ext_verify_codes')
                ->where('contact_type', 'phone')
                ->where('contact_value', $e164)
                ->where('verify_type', 'rebind_bind_new')
                ->where('verify_code', $verifyCode)
                ->where('status', 1)
                ->where('is_used', false)
                ->where('is_expired', false)
                ->whereRaw('expired_at > CURRENT_TIMESTAMP')
                ->first();

            if (!$codeRecord) {
                return ['error' => '验证码无效、已过期或已使用', 'data' => []];
            }

            $phoneValues = PhoneHelper::boundPhoneQueryValues($phone, $countryCode);
            $existing = $phoneValues !== []
                ? Db::table('ext_users')
                    ->where('user_id', '!=', $userId)
                    ->whereIn('bound_phone', $phoneValues)
                    ->where('status', 1)
                    ->first()
                : null;

            if ($existing) {
                return ['error' => '该手机号已被其他用户绑定', 'data' => []];
            }

            Db::beginTransaction();
            try {
                Db::table('ext_users')
                    ->where('user_id', $userId)
                    ->update(['bound_phone' => $e164]);

                Db::update(
                    'UPDATE ext_verify_codes SET is_used = true, used_at = CURRENT_TIMESTAMP WHERE id = ?',
                    [$codeRecord->id]
                );

                Db::commit();
                $redis->del("rebind_session:{$userId}:phone");
                return ['error' => '', 'data' => []];
            } catch (\Exception $e) {
                Db::rollBack();
                return ['error' => '换绑失败：' . $e->getMessage(), 'data' => []];
            }
        } catch (\Exception $e) {
            return ['error' => '系统错误，请稍后重试', 'data' => []];
        }
    }

    /**
     * 换绑邮箱第一步：验证旧邮箱验证码
     * 验证成功后在 Redis 存储换绑会话（10分钟有效），返回 rebind_token 供第二步使用
     * @param string $userId 当前登录用户 Matrix ID
     * @param string $verifyCode 旧邮箱收到的验证码
     * @return array ['error' => string, 'data' => array]
     */
    public static function verifyOldEmail(string $userId, string $verifyCode): array
    {
        $verifyCode = trim($verifyCode);
        if ($verifyCode === '') {
            return ['error' => '验证码不能为空', 'data' => []];
        }

        $extUser = Db::table('ext_users')
            ->select('id', 'bound_email')
            ->where('user_id', $userId)
            ->where('status', 1)
            ->first();

        if (!$extUser || empty($extUser->bound_email)) {
            return ['error' => '当前账号未绑定邮箱', 'data' => []];
        }

        $oldEmail = strtolower($extUser->bound_email);

        try {
            $codeRecord = Db::table('ext_verify_codes')
                ->where('contact_type', 'mail')
                ->whereRaw('LOWER(contact_value) = ?', [$oldEmail])
                ->where('verify_type', 'rebind_verify_old')
                ->where('verify_code', $verifyCode)
                ->where('status', 1)
                ->where('is_used', false)
                ->where('is_expired', false)
                ->whereRaw('expired_at > CURRENT_TIMESTAMP')
                ->first();

            if (!$codeRecord) {
                return ['error' => '验证码无效、已过期或已使用', 'data' => []];
            }

            Db::update(
                'UPDATE ext_verify_codes SET is_used = true, used_at = CURRENT_TIMESTAMP WHERE id = ?',
                [$codeRecord->id]
            );

            $rebindToken = bin2hex(random_bytes(32));
            $redis = Cache::get();
            $redis->set("rebind_session:{$userId}:email", json_encode([
                'old_value' => $oldEmail,
                'rebind_token' => $rebindToken,
            ]), 600);

            return ['error' => '', 'data' => ['rebind_token' => $rebindToken]];
        } catch (\Exception $e) {
            return ['error' => '系统错误，请稍后重试', 'data' => []];
        }
    }

    /**
     * 换绑邮箱第二步：用新邮箱验证码 + rebind_token 完成换绑
     * @param string $userId 当前登录用户 Matrix ID
     * @param string $email 新邮箱地址
     * @param string $verifyCode 新邮箱收到的验证码
     * @param string $rebindToken 第一步返回的 rebind_token
     * @return array ['error' => string, 'data' => array]
     */
    public static function rebindEmail(string $userId, string $email, string $verifyCode, string $rebindToken): array
    {
        $email = strtolower(trim($email));
        $verifyCode = trim($verifyCode);
        $rebindToken = trim($rebindToken);

        if ($email === '') {
            return ['error' => '新邮箱不能为空', 'data' => []];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => '邮箱格式不正确', 'data' => []];
        }
        if ($verifyCode === '') {
            return ['error' => '验证码不能为空', 'data' => []];
        }
        if ($rebindToken === '') {
            return ['error' => '缺少换绑凭证，请先完成旧邮箱验证', 'data' => []];
        }

        try {
            $redis = Cache::get();
            $sessionJson = $redis->get("rebind_session:{$userId}:email");
            if (!$sessionJson) {
                return ['error' => '换绑会话已过期，请重新验证旧邮箱', 'data' => []];
            }
            $session = json_decode($sessionJson, true);
            if (($session['rebind_token'] ?? '') !== $rebindToken) {
                return ['error' => '换绑凭证无效', 'data' => []];
            }

            $codeRecord = Db::table('ext_verify_codes')
                ->where('contact_type', 'mail')
                ->whereRaw('LOWER(contact_value) = ?', [$email])
                ->where('verify_type', 'rebind_bind_new')
                ->where('verify_code', $verifyCode)
                ->where('status', 1)
                ->where('is_used', false)
                ->where('is_expired', false)
                ->whereRaw('expired_at > CURRENT_TIMESTAMP')
                ->first();

            if (!$codeRecord) {
                return ['error' => '验证码无效、已过期或已使用', 'data' => []];
            }

            $existing = Db::table('ext_users')
                ->where('user_id', '!=', $userId)
                ->where('bound_email', $email)
                ->where('status', 1)
                ->first();

            if ($existing) {
                return ['error' => '该邮箱已被其他用户绑定', 'data' => []];
            }

            Db::beginTransaction();
            try {
                Db::table('ext_users')
                    ->where('user_id', $userId)
                    ->update(['bound_email' => $email]);

                Db::update(
                    'UPDATE ext_verify_codes SET is_used = true, used_at = CURRENT_TIMESTAMP WHERE id = ?',
                    [$codeRecord->id]
                );

                Db::commit();
                $redis->del("rebind_session:{$userId}:email");
                return ['error' => '', 'data' => []];
            } catch (\Exception $e) {
                Db::rollBack();
                return ['error' => '换绑失败：' . $e->getMessage(), 'data' => []];
            }
        } catch (\Exception $e) {
            return ['error' => '系统错误，请稍后重试', 'data' => []];
        }
    }

    /**
     * 设置用户备注（保存到 ext_user_remarks 表）
     * @param string $userId 保存备注的用户ID（当前登录用户，matrix_id格式）
     * @param string $remarkUserId 被备注的用户ID（目标用户，matrix_id格式）
     * @param string $remarkName 备注名称
     * @return array ['error' => string, 'data' => array]
     */
    public static function setUserRemark(string $userId, string $remarkUserId, string $remarkName): array
    {
        try {
            // 验证参数
            if (empty($remarkUserId)) {
                return ['error' => '被备注的用户ID不能为空', 'data' => []];
            }

            if (empty(trim($remarkName))) {
                return ['error' => '备注名称不能为空', 'data' => []];
            }

            // 验证备注名称长度
            if (mb_strlen(trim($remarkName)) > 255) {
                return ['error' => '备注名称过长（最大255字符）', 'data' => []];
            }

            // 检查是否已经存在备注记录
            $existingRemark = Db::table('ext_user_remarks')
                ->where('user_id', $userId)
                ->where('remark_user_id', $remarkUserId)
                ->where('status', 1) // 只检查有效记录
                ->first();

            if ($existingRemark) {
                // 如果记录存在，更新备注名称
                Db::table('ext_user_remarks')
                    ->where('id', $existingRemark->id)
                    ->update([
                        'remark_name' => trim($remarkName)
                    ]);

                return [
                    'error' => '',
                    'data' => [
                        'id' => $existingRemark->id,
                        'user_id' => $userId,
                        'remark_user_id' => $remarkUserId,
                        'remark_name' => trim($remarkName)
                    ]
                ];
            }

            // 插入新记录
            $id = Db::table('ext_user_remarks')->insertGetId([
                'user_id' => $userId,
                'remark_user_id' => $remarkUserId,
                'remark_name' => trim($remarkName),
                'status' => 1
            ]);

            return [
                'error' => '',
                'data' => [
                    'id' => $id,
                    'user_id' => $userId,
                    'remark_user_id' => $remarkUserId,
                    'remark_name' => trim($remarkName)
                ]
            ];
        } catch (\Exception $e) {
            return ['error' => '设置用户备注失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 设置聊天禁用状态
     * @param string $userId
     * @param bool $prohibitedg
     * @return array ['error' => string, 'data' => array]
     */
    public static function setChatProhibited(string $userId, bool $prohibited): array
    {
        try {
            Db::table('ext_users')
                ->where('user_id', $userId)
                ->update(['chat_prohibited' => $prohibited]);

            return ['error' => '', 'data' => []];
        } catch (\Exception $e) {
            return ['error' => '设置聊天状态失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 设置笔记权限
     * @param string $userId 当前用户ID
     * @param bool $noteAccess 是否禁止创建笔记
     * @param string $targetUserId 可选，目标用户ID（如果提供，则设置对目标用户的笔记访问权限）
     * @return array ['error' => string, 'data' => array]
     */
    public static function setNotePermission(string $userId, bool $noteAccess, string $targetUserId = ''): array
    {
        try {
            // 如果提供了 targetUserId，则设置对目标用户的笔记访问权限
            if (!empty($targetUserId)) {
                // 使用 UserPermissions DAO 设置笔记访问权限
                // note_access = !prohibited（如果 prohibited=true，则 note_access=false）
                $result = \App\Http\Frontend\Dao\UserPermissions::setNoteAccess($userId, $targetUserId, $noteAccess);

                if ($result['error'] !== '') {
                    return ['error' => $result['error'], 'data' => []];
                }

                return ['error' => '', 'data' => $result['data']];
            }

            // 如果没有提供 targetUserId，则设置自己的笔记创建权限
            Db::table('ext_users')
                ->where('user_id', $userId)
                ->update(['note_creation_prohibited' => $noteAccess]);

            return ['error' => '', 'data' => []];
        } catch (\Exception $e) {
            return ['error' => '设置笔记权限失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 获取客服用户列表
     * @return array ['error' => string, 'data' => array]
     */
    public static function getCustomerServiceList(): array
    {
        try {
            $users = Db::table('ext_users')
                ->select('id', 'user_id', 'gender', 'personal_description', 'avatar_url', 'nickname', 'remark')
                ->where('is_customer_service', true)
                ->where('status', 1)
                ->orderBy('sort', 'DESC')
                ->limit(20)
                ->get()
                ->toArray();

            return [
                'error' => '',
                'data' => $users
            ];
        } catch (\Exception $e) {
            return ['error' => '获取客服列表失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 从完整的 user_id 中提取 localpart
     * @param string $userId 例如: @u10010:matrix.bleiworc.xyz
     * @return string 例如: u10010
     */
    private static function extractLocalpart(string $userId): string
    {
        // 去掉 @ 前缀
        $userId = ltrim($userId, '@');
        // 分割获取域名前的部分
        $parts = explode(':', $userId, 2);
        return $parts[0];
    }
}
