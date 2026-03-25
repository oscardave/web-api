<?php
declare(strict_types=1);

namespace App\Service;

use Hyperf\DbConnection\Db;

/**
 * 用户资料同步服务：将 ext_users 的头像、昵称及 ext_user_accounts 的诚信保余额
 * 同步到 ext_circle_users、ext_group_users、ext_circle_content、ext_circle_notice、
 * ext_circle_invite、circle_content_report、ext_user_notes 等业务表。
 * 不内部启事务，由调用方在已有事务内调用。
 */
class ProfileSyncService
{
    /**
     * 同步头像到所有展示头像的业务表
     *
     * @param string $matrixUserId Matrix 用户 ID（ext_users.user_id）
     * @param string $avatarUrl 头像 URL
     */
    public static function syncAvatar(string $matrixUserId, string $avatarUrl): void
    {
        $avatarForTables = $avatarUrl === '' ? '' : $avatarUrl;
        Db::table('ext_circle_users')->where('user_id', $matrixUserId)->update(['user_avatar_url' => $avatarForTables]);
        Db::table('ext_group_users')->where('user_id', $matrixUserId)->update(['user_avatar_url' => $avatarForTables]);
        Db::table('ext_circle_content')->where('user_id', $matrixUserId)->update(['user_avatar' => $avatarForTables]);
        Db::table('ext_circle_notice')->where('user_id', $matrixUserId)->update(['user_avatar' => $avatarForTables]);
        Db::table('ext_circle_invite')->where('invite_id', $matrixUserId)->update(['invite_avatar' => $avatarForTables]);
        Db::table('ext_circle_invite')->where('user_id', $matrixUserId)->update(['user_avatar' => $avatarForTables]);
        Db::table('circle_content_report')->where('user_id', $matrixUserId)->update(['user_avatar' => $avatarForTables]);
        Db::table('ext_user_notes')->where('user_id', $matrixUserId)->update(['user_avatar' => $avatarForTables]);
    }

    /**
     * 同步昵称到所有展示昵称的业务表
     *
     * @param string $matrixUserId Matrix 用户 ID（ext_users.user_id）
     * @param string $nickname 昵称
     */
    public static function syncNickname(string $matrixUserId, string $nickname): void
    {
        $nicknameForTables = $nickname === '' ? '' : $nickname;
        Db::table('ext_circle_users')->where('user_id', $matrixUserId)->update(['user_nickname' => $nicknameForTables]);
        Db::table('ext_group_users')->where('user_id', $matrixUserId)->update(['user_nickname' => $nicknameForTables]);
        Db::table('ext_circle_content')->where('user_id', $matrixUserId)->update(['user_nickname' => $nicknameForTables]);
        Db::table('ext_circle_notice')->where('user_id', $matrixUserId)->update(['user_nickname' => $nicknameForTables]);
        Db::table('ext_circle_invite')->where('invite_id', $matrixUserId)->update(['invite_nickname' => $nicknameForTables]);
        Db::table('ext_circle_invite')->where('user_id', $matrixUserId)->update(['user_nickname' => $nicknameForTables]);
        Db::table('circle_content_report')->where('user_id', $matrixUserId)->update(['user_nickname' => $nicknameForTables]);
        Db::table('ext_user_notes')->where('user_id', $matrixUserId)->update(['user_nickname' => $nicknameForTables]);
    }

    /**
     * 同步诚信保余额到 ext_circle_users.user_score
     * ext_circle_users.user_score 与 ext_user_accounts.credit_amount 应保持一致
     *
     * @param int $extUserId ext_users.id（ext_user_accounts.user_id）
     * @param int $creditAmount 当前诚信保余额
     */
    public static function syncUserScore(int $extUserId, int $creditAmount): void
    {
        $row = Db::table('ext_users')->where('id', $extUserId)->select('user_id')->first();
        if (!$row || empty($row->user_id)) {
            return;
        }
        $matrixUserId = (string)$row->user_id;
        Db::table('ext_circle_users')->where('user_id', $matrixUserId)->update(['user_score' => $creditAmount]);
    }
}
