<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use App\Service\AccountChangeTriggerService;
use App\Service\ProfileSyncService;
use Hyperf\DbConnection\Db;

class UserAccountDao
{
    /**
     * 补齐 ext_user_accounts：将 ext_users 中存在但 ext_user_accounts 中不存在的用户插入账户记录，默认 0 值。
     * @return array{error: string, data?: array{inserted: int}}
     */
    public static function syncMissingAccounts(): array
    {
        try {
            $sql = "INSERT INTO ext_user_accounts (user_id, point_amount, point_frozen_amount, credit_amount, credit_frozen_amount)
                SELECT eu.id, 0, 0, 0, 0
                FROM ext_users eu
                LEFT JOIN ext_user_accounts eua ON eu.id = eua.user_id
                WHERE eua.id IS NULL";
            $affected = Db::connection()->affectingStatement($sql);
            return [
                'error' => '',
                'data' => ['inserted' => $affected],
            ];
        } catch (\Throwable $e) {
            return [
                'error' => $e->getMessage(),
                'data' => ['inserted' => 0],
            ];
        }
    }

    public static function list(array $request): array
    {
        $query = Db::table('ext_user_accounts as a')
            ->leftJoin('ext_users as u', 'a.user_id', '=', 'u.id')
            ->select(
                'a.id', 'a.user_id', 'u.nickname as user_name',
                'a.point_amount', 'a.point_frozen_amount', 'a.credit_amount', 'a.credit_frozen_amount',
                'a.created_at', 'a.updated_at'
            )
            ->orderBy('a.updated_at', 'DESC');

        if (($request['user_id'] ?? 0) > 0) {
            $query->where('a.user_id', $request['user_id']);
        }
        if (!empty($request['nickname'] ?? '')) {
            $query->where('u.nickname', 'like', '%' . $request['nickname'] . '%');
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

    public static function getByUserId(int $userId): ?array
    {
        $row = Db::table('ext_user_accounts as a')
            ->leftJoin('ext_users as u', 'a.user_id', '=', 'u.id')
            ->select(
                'a.id', 'a.user_id', 'u.nickname as user_name',
                'a.point_amount', 'a.point_frozen_amount', 'a.credit_amount', 'a.credit_frozen_amount',
                'a.created_at', 'a.updated_at'
            )
            ->where('a.user_id', $userId)
            ->first();

        return $row ? (array)$row : null;
    }

    public static function getById(int $id): ?array
    {
        $row = Db::table('ext_user_accounts as a')
            ->leftJoin('ext_users as u', 'a.user_id', '=', 'u.id')
            ->select(
                'a.id', 'a.user_id', 'u.nickname as user_name',
                'a.point_amount', 'a.point_frozen_amount', 'a.credit_amount', 'a.credit_frozen_amount',
                'a.created_at', 'a.updated_at'
            )
            ->where('a.id', $id)
            ->first();

        return $row ? (array)$row : null;
    }

    /**
     * 上分：增加账户余额并写入账变记录
     * change_type: 1-积分 2-诚信保
     */
    public static function addBalance(array $request): array
    {
        $userId = (int)$request['user_id'];
        $changeType = (int)$request['change_type'];
        $amount = (int)$request['amount'];
        if ($amount <= 0) {
            return ['error' => '金额必须大于0'];
        }

        Db::beginTransaction();
        try {
            $account = Db::table('ext_user_accounts')->where('user_id', $userId)->first();
            if (!$account) {
                Db::rollBack();
                return ['error' => '用户账户不存在'];
            }

            $balanceBefore = 0;
            $balanceAfter = 0;
            if ($changeType === 1) {
                $balanceBefore = (int)($account->point_amount ?? 0);
                $balanceAfter = $balanceBefore + $amount;
                Db::table('ext_user_accounts')->where('user_id', $userId)->increment('point_amount', $amount);
            } elseif ($changeType === 2) {
                $balanceBefore = (int)($account->credit_amount ?? 0);
                $balanceAfter = $balanceBefore + $amount;
                Db::table('ext_user_accounts')->where('user_id', $userId)->increment('credit_amount', $amount);
            } else {
                Db::rollBack();
                return ['error' => '无效的变动类型'];
            }

            $insertErr = UserChangeDao::addRecord([
                'user_id' => $userId,
                'change_type' => $changeType,
                'operation_type' => 1,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'source_id' => $request['source_id'] ?? '',
                'description' => $request['description'] ?? '',
            ]);
            if ($insertErr['error'] !== '') {
                Db::rollBack();
                return $insertErr;
            }

            if ($changeType === 2) {
                ProfileSyncService::syncUserScore($userId, $balanceAfter);
                $trigger = AccountChangeTriggerService::triggerAfterCreditChange($userId);
                if ($trigger['error'] !== '') {
                    Db::rollBack();
                    return $trigger;
                }
            }

            Db::commit();
            return ['error' => ''];
        } catch (\Throwable $e) {
            Db::rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 下分：扣减前校验余额，再扣减并写入账变记录
     */
    public static function deductBalance(array $request): array
    {
        $userId = (int)$request['user_id'];
        $changeType = (int)$request['change_type'];
        $amount = (int)$request['amount'];
        if ($amount <= 0) {
            return ['error' => '金额必须大于0'];
        }

        Db::beginTransaction();
        try {
            $account = Db::table('ext_user_accounts')->where('user_id', $userId)->first();
            if (!$account) {
                Db::rollBack();
                return ['error' => '用户账户不存在'];
            }

            $balanceBefore = 0;
            $balanceAfter = 0;
            if ($changeType === 1) {
                $balanceBefore = (int)($account->point_amount ?? 0);
                if ($balanceBefore < $amount) {
                    Db::rollBack();
                    return ['error' => '积分余额不足，当前余额：' . $balanceBefore];
                }
                $balanceAfter = $balanceBefore - $amount;
                Db::table('ext_user_accounts')->where('user_id', $userId)->decrement('point_amount', $amount);
            } elseif ($changeType === 2) {
                $balanceBefore = (int)($account->credit_amount ?? 0);
                if ($balanceBefore < $amount) {
                    Db::rollBack();
                    return ['error' => '诚信保余额不足，当前余额：' . $balanceBefore];
                }
                $balanceAfter = $balanceBefore - $amount;
                Db::table('ext_user_accounts')->where('user_id', $userId)->decrement('credit_amount', $amount);
            } else {
                Db::rollBack();
                return ['error' => '无效的变动类型'];
            }

            $insertErr = UserChangeDao::addRecord([
                'user_id' => $userId,
                'change_type' => $changeType,
                'operation_type' => 2,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'source_id' => $request['source_id'] ?? '',
                'description' => $request['description'] ?? '',
            ]);
            if ($insertErr['error'] !== '') {
                Db::rollBack();
                return $insertErr;
            }

            if ($changeType === 2) {
                ProfileSyncService::syncUserScore($userId, $balanceAfter);
                $trigger = AccountChangeTriggerService::triggerAfterCreditChange($userId);
                if ($trigger['error'] !== '') {
                    Db::rollBack();
                    return $trigger;
                }
            }

            Db::commit();
            return ['error' => ''];
        } catch (\Throwable $e) {
            Db::rollBack();
            return ['error' => $e->getMessage()];
        }
    }
}
