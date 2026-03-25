<?php
declare(strict_types=1);

namespace App\Http\Backend\Validations;

class UserExtValidation
{
    private const ALLOWED_STATUSES = ['normal', 'banned', 'suspended', 'limited'];

    public static function validate(array $request): string
    {
        return '';
    }

    public static function validateChangeStatus(array $request): string
    {
        if (empty($request['user_id']) || trim((string)$request['user_id']) === '') {
            return '用户ID不能为空';
        }
        if (empty($request['account_status']) || trim((string)$request['account_status']) === '') {
            return '账号状态不能为空';
        }
        if (!in_array($request['account_status'], self::ALLOWED_STATUSES, true)) {
            return '账号状态必须是：normal、banned、suspended、limited 之一';
        }
        return '';
    }

    public static function validateSetPenalty(array $request): string
    {
        if (empty($request['user_id']) || trim((string)$request['user_id']) === '') {
            return '用户ID不能为空';
        }
        return '';
    }

    /**
     * 校验修改密码请求：user_id 必填，new_password 长度 6～512（与 Synapse 一致）。
     */
    public static function validateSetPassword(array $request): string
    {
        if (empty($request['user_id']) || trim((string)$request['user_id']) === '') {
            return '用户ID不能为空';
        }
        $pwd = (string)($request['new_password'] ?? '');
        if (strlen($pwd) < 6) {
            return '新密码长度不能少于6位';
        }
        if (strlen($pwd) > 512) {
            return '新密码长度不能超过512位';
        }
        return '';
    }

    /**
     * 校验更新会员/徽章请求：user_id 必填；member_level_id、identity_badge_id、credit_badge_id 为非负整数；
     * pure_badge_enabled 为布尔；vanity_number 纯数字、最多20位。
     */
    public static function validateUpdateMembership(array $request): string
    {
        if (empty($request['user_id']) || trim((string)$request['user_id']) === '') {
            return '用户ID不能为空';
        }
        $memberLevelId = (int)($request['member_level_id'] ?? 0);
        if ($memberLevelId < 0) {
            return '会员等级ID无效';
        }
        $identityBadgeId = (int)($request['identity_badge_id'] ?? 0);
        if ($identityBadgeId < 0) {
            return '身份徽章ID无效';
        }
        $creditBadgeId = (int)($request['credit_badge_id'] ?? 0);
        if ($creditBadgeId < 0) {
            return '诚信徽章ID无效';
        }
        $circleBadgeId = (int)($request['circle_badge_id'] ?? 0);
        if ($circleBadgeId < 0) {
            return '圈子徽章ID无效';
        }
        $vanityNumber = isset($request['vanity_number']) ? trim((string)$request['vanity_number']) : '';
        if ($vanityNumber !== '' && $vanityNumber !== '0') {
            if (!preg_match('/^\d{1,20}$/', $vanityNumber)) {
                return '靓号须为纯数字，最多20位';
            }
        }
        return '';
    }

    /**
     * 校验奖励请求：user_id 必填；note_limit_increase / super_seat_limit_increase 为 0~1000 / 0~100 的非负整数。
     */
    public static function validateSetReward(array $request): string
    {
        if (empty($request['user_id']) || trim((string)$request['user_id']) === '') {
            return '用户ID不能为空';
        }
        $noteInc = (int)($request['note_limit_increase'] ?? 0);
        $superInc = (int)($request['super_seat_limit_increase'] ?? 0);
        if ($noteInc < 0 || $noteInc > 1000) {
            return '笔记上限增加数量须在 0~1000 之间';
        }
        if ($superInc < 0 || $superInc > 100) {
            return '超级坐席笔记上限增加数量须在 0~100 之间';
        }
        if ($noteInc === 0 && $superInc === 0) {
            return '至少需要增加一项奖励';
        }
        return '';
    }

    /**
     * 校验发送系统消息请求：user_id 必填；message 1~5000 字符。
     */
    public static function validateSendMessage(array $request): string
    {
        if (empty($request['user_id']) || trim((string)$request['user_id']) === '') {
            return '用户ID不能为空';
        }
        $msg = (string)($request['message'] ?? '');
        if (mb_strlen($msg) < 1) {
            return '消息内容不能为空';
        }
        if (mb_strlen($msg) > 5000) {
            return '消息内容长度不能超过5000字符';
        }
        return '';
    }

    /**
     * 校验更新头像/昵称/个人描述请求：user_id 必填；avatar_url 最大 1000 字符；nickname 最大 100 字符；personal_description 最大 2000 字符。
     */
    public static function validateUpdateProfile(array $request): string
    {
        if (empty($request['user_id']) || trim((string)$request['user_id']) === '') {
            return '用户ID不能为空';
        }
        if (isset($request['avatar_url']) && mb_strlen((string)$request['avatar_url']) > 1000) {
            return '头像URL长度不能超过1000字符';
        }
        if (isset($request['nickname']) && mb_strlen((string)$request['nickname']) > 100) {
            return '昵称长度不能超过100字符';
        }
        if (isset($request['personal_description']) && mb_strlen((string)$request['personal_description']) > 2000) {
            return '个人描述长度不能超过2000字符';
        }
        return '';
    }
}
