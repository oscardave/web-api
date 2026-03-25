<?php
declare(strict_types=1);

namespace App\Service;

use Hyperf\DbConnection\Db;

/**
 * 账变触发：账变成功后由调用方在同一事务内调用，不内部启事务。
 * 诚信保徽章：根据当前用户诚信保余额更新 ext_users.credit_badge_id。
 */
class AccountChangeTriggerService
{
    /**
     * 诚信保账变触发：按当前用户诚信保余额匹配 type=2 徽章，更新 credit_badge_id。
     * 调用方需已开启事务，本方法不 begin/commit。
     *
     * @return array{error: string, data: null}
     */
    public static function triggerAfterCreditChange(int $userId): array
    {
        $account = Db::table('ext_user_accounts')->where('user_id', $userId)->first();
        $creditAmount = $account ? (int)($account->credit_amount ?? 0) : 0;

        $badge = Db::table('ext_user_badges')
            ->where('type', 2)
            ->where('status', 1)
            ->where('integrity_score_required', '<=', $creditAmount)
            ->orderBy('integrity_score_required', 'DESC')
            ->first();

        $badgeId = $badge ? (int)$badge->id : 0;
        Db::table('ext_users')->where('id', $userId)->update(['credit_badge_id' => $badgeId]);

        return ['error' => '', 'data' => null];
    }
}
