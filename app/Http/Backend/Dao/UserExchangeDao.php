<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;
use Hyperf\Context\ApplicationContext;
use App\Service\AccountChangeTriggerService;
use App\Service\AccountChangeNotifyService;
use App\Service\ProfileSyncService;

class UserExchangeDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_user_exchanges_v')
            ->select('id', 'user_id', 'nickname', 'avatar', 'exchange_type',
                     'membership_level', 'membership_level_name', 'original_price',
                     'exchange_time', 'status', 'created_at', 'updated_at')
            ->orderBy('exchange_time', 'DESC');

        $userId = (int)($request['user_id'] ?? 0);
        if ($userId > 0) {
            $query->where('user_id', $userId);
        }
        if (!empty($request['nickname'])) {
            $query->where('nickname', 'like', '%' . $request['nickname'] . '%');
        }
        if (($request['exchange_type'] ?? 0) > 0) {
            $query->where('exchange_type', $request['exchange_type']);
        }
        if (($request['membership_level'] ?? 0) > 0) {
            $query->where('membership_level', $request['membership_level']);
        }
        if (($request['status'] ?? 0) > 0) {
            $query->where('status', $request['status']);
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

    public static function get(array $request): array
    {
        $row = Db::table('ext_user_exchanges_v')
            ->select('id', 'user_id', 'nickname', 'avatar', 'exchange_type',
                     'membership_level', 'membership_level_name', 'original_price',
                     'exchange_time', 'status', 'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '兑换记录不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    /**
     * 新增兑换：校验用户积分余额 >= 价格，兑换时间与状态由接口自动填写
     * payment_method：1-积分兑换，2-现金支付，3-混合支付（表字段为 smallint）
     * 诚信保兑换(type=2)：扣减积分、增加诚信保余额，并写 2 条账变（积分下分、诚信保上分）
     */
    public static function create(array $request): array
    {
        $userId = (int)($request['user_id'] ?? 0);
        if ($userId <= 0) {
            return ['error' => '用户ID无效'];
        }
        $exchangeType = (int)($request['exchange_type'] ?? 0);
        if (!in_array($exchangeType, [1, 2], true)) {
            return ['error' => '兑换类型无效'];
        }
        $price = (float)($request['original_price'] ?? 0);
        if ($price <= 0) {
            return ['error' => '价格必须大于0'];
        }

        $account = Db::table('ext_user_accounts')->where('user_id', $userId)->first();
        if (!$account) {
            return ['error' => '用户账户不存在'];
        }
        $pointAmount = (int)($account->point_amount ?? 0);
        $creditAmount = (int)($account->credit_amount ?? 0);
        $priceInt = (int)ceil($price);
        if ($pointAmount < $priceInt) {
            return ['error' => '用户积分余额不足'];
        }

        $membershipLevel = $exchangeType === 1 ? (int)($request['membership_level'] ?? 0) : 0;
        $paymentMethod = 1; // 1-积分兑换

        if ($exchangeType === 1) {
            $priceResult = self::calcLevelUpgradePrice($userId, $membershipLevel);
            if ($priceResult['error'] !== '') {
                return $priceResult;
            }
            $computedPrice = (int)$priceResult['data']['price'];
            if ($computedPrice <= 0) {
                return ['error' => '升级价格无效，无法兑换'];
            }
            if ($priceInt !== $computedPrice) {
                return ['error' => '价格已变化，请重新选择等级后再提交'];
            }
            $pointAmount = (int)($account->point_amount ?? 0);
            if ($pointAmount < $computedPrice) {
                return ['error' => '用户积分余额不足'];
            }
            $priceInt = $computedPrice;
        }

        Db::beginTransaction();
        try {
            $exchangeId = Db::table('ext_user_exchanges')->insertGetId([
                'user_id' => $userId,
                'exchange_type' => $exchangeType,
                'membership_level' => $membershipLevel,
                'original_price' => $price,
                'discount' => 0,
                'paid_amount' => $price,
                'payment_method' => $paymentMethod,
                'exchange_time' => date('Y-m-d H:i:s'),
                'status' => 1,
            ]);

            if (!$exchangeId) {
                Db::rollBack();
                return ['error' => '创建兑换记录失败'];
            }

            if ($exchangeType === 1) {
                $user = Db::table('ext_users')->where('id', $userId)->first();
                $targetLevel = Db::table('ext_user_levels')->where('id', $membershipLevel)->first();
                $validityPeriod = $targetLevel ? (int)($targetLevel->validity_period ?? 0) : 0;
                $newExpiration = date('Y-m-d H:i:s', strtotime('+' . $validityPeriod . ' days'));

                Db::table('ext_user_accounts')->where('user_id', $userId)->decrement('point_amount', $priceInt);
                $r1 = UserChangeDao::addRecord([
                    'user_id' => $userId,
                    'change_type' => 1,
                    'operation_type' => 2,
                    'amount' => $priceInt,
                    'balance_before' => $pointAmount,
                    'balance_after' => $pointAmount - $priceInt,
                    'source_id' => 'exchange:' . $exchangeId,
                    'description' => '积分扣减（会员等级兑换）',
                ]);
                if ($r1['error'] !== '') {
                    Db::rollBack();
                    return $r1;
                }
                Db::table('ext_users')->where('id', $userId)->update([
                    'member_level_id' => $membershipLevel,
                    'member_expiration_time' => $newExpiration,
                ]);
            }

            if ($exchangeType === 2) {
                // 诚信保兑换：扣积分、加诚信保，写 2 条账变
                Db::table('ext_user_accounts')->where('user_id', $userId)->update([
                    'point_amount' => $pointAmount - $priceInt,
                    'credit_amount' => $creditAmount + $priceInt,
                ]);

                $r1 = UserChangeDao::addRecord([
                    'user_id' => $userId,
                    'change_type' => 1,
                    'operation_type' => 2,
                    'amount' => $priceInt,
                    'balance_before' => $pointAmount,
                    'balance_after' => $pointAmount - $priceInt,
                    'source_id' => 'exchange:' . $exchangeId,
                    'description' => '积分扣减（诚信保兑换）',
                ]);
                if ($r1['error'] !== '') {
                    Db::rollBack();
                    return $r1;
                }
                $r2 = UserChangeDao::addRecord([
                    'user_id' => $userId,
                    'change_type' => 2,
                    'operation_type' => 1,
                    'amount' => $priceInt,
                    'balance_before' => $creditAmount,
                    'balance_after' => $creditAmount + $priceInt,
                    'source_id' => 'exchange:' . $exchangeId,
                    'description' => '诚信保增加（积分兑换）',
                ]);
                if ($r2['error'] !== '') {
                    Db::rollBack();
                    return $r2;
                }
                $trigger = AccountChangeTriggerService::triggerAfterCreditChange($userId);
                if ($trigger['error'] !== '') {
                    Db::rollBack();
                    return $trigger;
                }
                ProfileSyncService::syncUserScore($userId, $creditAmount + $priceInt);
            }

            Db::commit();
            ApplicationContext::getContainer()->get(AccountChangeNotifyService::class)->notify($userId);
            return ['error' => ''];
        } catch (\Throwable $e) {
            Db::rollBack();
            return ['error' => '操作失败：' . $e->getMessage()];
        }
    }

    /**
     * 计算会员等级升级价格。目标等级 sort 必须大于当前等级 sort（向下、同级不允许）。
     * 非会员（当前 payment_point=0）：价格=目标 payment_point 全价。
     * 会员升级：价格=(目标 payment_point - 当前 payment_point) / 当前等级 validity_period × 剩余天数（validity_period 为 0 时按 30 天），剩余天数来自 ext_users.member_expiration_time。
     *
     * @return array{error: string, data: array{price: int, current_level_name: string, target_level_name: string, remaining_days: int}|null}
     */
    public static function calcLevelUpgradePrice(int $userId, int $targetLevelId): array
    {
        $user = Db::table('ext_users')->where('id', $userId)->first();
        if (!$user) {
            return ['error' => '用户不存在', 'data' => null];
        }
        $currentLevelId = (int)($user->member_level_id ?? 0);
        $expiration = $user->member_expiration_time ?? null;

        $currentLevel = $currentLevelId > 0
            ? Db::table('ext_user_levels')->where('id', $currentLevelId)->first()
            : null;
        $targetLevel = Db::table('ext_user_levels')->where('id', $targetLevelId)->first();
        if (!$targetLevel) {
            return ['error' => '目标等级不存在', 'data' => null];
        }
        $targetSort = (int)($targetLevel->sort ?? 0);
        $targetPayment = (int)($targetLevel->payment_point ?? 0);
        $targetValidity = (int)($targetLevel->validity_period ?? 0);
        $targetName = (string)($targetLevel->name ?? '');
        $isExchangeable = !empty($targetLevel->is_exchangeable);

        $currentSort = 0;
        $currentPayment = 0;
        $currentValidityPeriod = 0;
        $currentName = '';
        if ($currentLevel) {
            $currentSort = (int)($currentLevel->sort ?? 0);
            $currentPayment = (int)($currentLevel->payment_point ?? 0);
            $currentValidityPeriod = (int)($currentLevel->validity_period ?? 0);
            $currentName = (string)($currentLevel->name ?? '');
        }

        if (!$isExchangeable) {
            return ['error' => '该等级不可兑换', 'data' => null];
        }
        if ($targetSort <= $currentSort) {
            return ['error' => '只能兑换更高等级', 'data' => null];
        }

        $remainingDays = 0;
        if ($expiration) {
            $ts = is_numeric($expiration) ? (int)$expiration : strtotime((string)$expiration);
            $now = time();
            $remainingDays = (int)floor(($ts - $now) / 86400);
            if ($remainingDays < 0) {
                $remainingDays = 0;
            }
        }

        if ($currentPayment === 0) {
            $price = $targetPayment;
        } else {
            $diff = $targetPayment - $currentPayment;
            if ($diff <= 0) {
                return ['error' => '升级价格无效，无法兑换', 'data' => null];
            }
            if ($remainingDays <= 0) {
                return ['error' => '当前会员已过期，无法按差价升级，请先续费', 'data' => null];
            }
            $validityDays = $currentValidityPeriod > 0 ? $currentValidityPeriod : 30;
            $price = (int)ceil($diff / $validityDays * $remainingDays);
        }

        if ($price <= 0) {
            return ['error' => '升级价格无效，无法兑换', 'data' => null];
        }

        return [
            'error' => '',
            'data' => [
                'price' => $price,
                'current_level_name' => $currentName,
                'target_level_name' => $targetName,
                'remaining_days' => $remainingDays,
            ],
        ];
    }

    /**
     * 根据用户ID（ext_users.id）获取当前会员等级说明，用于新增兑换弹窗展示。
     * level_sort 用于前端过滤可兑换等级（仅展示 sort 大于当前等级的选项）。
     *
     * @return array{error: string, data: array{level_id: int, level_name: string, level_remark: string, level_sort: int}|null}
     */
    public static function getUserLevelInfo(int $userId): array
    {
        if ($userId <= 0) {
            return ['error' => '用户ID无效', 'data' => null];
        }
        $user = Db::table('ext_users')->where('id', $userId)->first();
        if (!$user) {
            return ['error' => '用户不存在', 'data' => null];
        }
        $levelId = (int)($user->member_level_id ?? 0);
        if ($levelId <= 0) {
            return [
                'error' => '',
                'data' => [
                    'level_id' => 0,
                    'level_name' => '普通用户',
                    'level_remark' => '',
                    'level_sort' => 0,
                ],
            ];
        }
        $level = Db::table('ext_user_levels')->where('id', $levelId)->first();
        if (!$level) {
            return [
                'error' => '',
                'data' => [
                    'level_id' => $levelId,
                    'level_name' => '未知等级',
                    'level_remark' => '',
                    'level_sort' => 0,
                ],
            ];
        }
        return [
            'error' => '',
            'data' => [
                'level_id' => $levelId,
                'level_name' => trim((string)($level->name ?? '')),
                'level_remark' => trim((string)($level->remark ?? '')),
                'level_sort' => (int)($level->sort ?? 0),
            ],
        ];
    }

    public static function del(array $request): array
    {
        $affectedRows = Db::table('ext_user_exchanges')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除兑换记录失败'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $affectedRows = Db::table('ext_user_exchanges')
            ->where('id', $request['id'])
            ->update(['status' => $request['status']]);

        if ($affectedRows == 0) {
            return ['error' => '修改兑换状态失败'];
        }

        return ['error' => ''];
    }
}
