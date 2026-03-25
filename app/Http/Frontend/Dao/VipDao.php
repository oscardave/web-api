<?php
declare(strict_types=1);

namespace App\Http\Frontend\Dao;

use Hyperf\DbConnection\Db;
use Hyperf\Context\ApplicationContext;
use App\Http\Backend\Dao\UserExchangeDao;
use App\Http\Backend\Dao\UserChangeDao;
use App\Service\AccountChangeNotifyService;

/**
 * C 端会员兑换相关 Dao
 */
class VipDao
{
    /** 允许兑换的会员等级 ID：10001 初级、10002 高级、10003 超级 */
    private const ALLOWED_LEVEL_IDS = [10001, 10002, 10003];

    /**
     * 兑换会员等级：校验等级、计算价格、扣积分、写账变、更新会员等级与到期时间。
     * 与 ext-finances/exchanges 会员等级兑换逻辑一致。
     *
     * @param int $extUserId ext_users.id
     * @param int $levelId   目标等级 ID，仅允许 10001/10002/10003 且 is_exchangeable=true
     * @return array{error: string}
     */
    public static function exchangeMemberLevel(int $extUserId, int $levelId): array
    {
        if (!in_array($levelId, self::ALLOWED_LEVEL_IDS, true)) {
            return ['error' => '等级ID无效，仅支持初级(10001)、高级(10002)、超级(10003)'];
        }

        $targetLevel = Db::table('ext_user_levels')->where('id', $levelId)->first();
        if (!$targetLevel) {
            return ['error' => '目标等级不存在'];
        }
        if (empty($targetLevel->is_exchangeable)) {
            return ['error' => '该等级不可兑换'];
        }

        $user = Db::table('ext_users')->where('id', $extUserId)->first();
        if (!$user) {
            return ['error' => '用户不存在'];
        }

        $currentLevelId = (int)($user->member_level_id ?? 0);
        if ($currentLevelId === $levelId) {
            // 与当前等级相同：不扣费、不写账变，返回 same_level 供前端展示「无需兑换」类提示
            return ['error' => '', 'same_level' => true];
        }

        $priceResult = UserExchangeDao::calcLevelUpgradePrice($extUserId, $levelId);
        if ($priceResult['error'] !== '') {
            return $priceResult;
        }

        $price = (int)$priceResult['data']['price'];
        if ($price <= 0) {
            return ['error' => '升级价格无效，无法兑换'];
        }

        $account = Db::table('ext_user_accounts')->where('user_id', $extUserId)->first();
        if (!$account) {
            return ['error' => '用户账户不存在'];
        }

        $pointAmount = (int)($account->point_amount ?? 0);
        if ($pointAmount < $price) {
            return ['error' => '积分余额不足'];
        }

        Db::beginTransaction();
        try {
            $exchangeId = Db::table('ext_user_exchanges')->insertGetId([
                'user_id' => $extUserId,
                'exchange_type' => 1,
                'membership_level' => $levelId,
                'original_price' => $price,
                'discount' => 0,
                'paid_amount' => $price,
                'payment_method' => 1,
                'exchange_time' => date('Y-m-d H:i:s'),
                'status' => 1,
            ]);

            if (!$exchangeId) {
                Db::rollBack();
                return ['error' => '创建兑换记录失败'];
            }

            $targetLevelRow = Db::table('ext_user_levels')->where('id', $levelId)->first();
            $validityPeriod = $targetLevelRow ? (int)($targetLevelRow->validity_period ?? 0) : 0;
            $newExpiration = date('Y-m-d H:i:s', strtotime('+' . $validityPeriod . ' days'));

            Db::table('ext_user_accounts')->where('user_id', $extUserId)->decrement('point_amount', $price);

            $r1 = UserChangeDao::addRecord([
                'user_id' => $extUserId,
                'change_type' => 1,
                'operation_type' => 2,
                'amount' => $price,
                'balance_before' => $pointAmount,
                'balance_after' => $pointAmount - $price,
                'source_id' => 'exchange:' . $exchangeId,
                'description' => '积分扣减（会员等级兑换）',
            ]);
            if ($r1['error'] !== '') {
                Db::rollBack();
                return $r1;
            }

            Db::table('ext_users')->where('id', $extUserId)->update([
                'member_level_id' => $levelId,
                'member_expiration_time' => $newExpiration,
            ]);

            Db::commit();
            ApplicationContext::getContainer()->get(AccountChangeNotifyService::class)->notify($extUserId);
            return ['error' => ''];
        } catch (\Throwable $e) {
            Db::rollBack();
            return ['error' => '操作失败：' . $e->getMessage()];
        }
    }
}
