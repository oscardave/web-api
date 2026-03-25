<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Context\ApplicationContext;
use App\Http\Backend\Dao\UserChangeDao;
use App\Service\AccountChangeNotifyService;
use App\Service\AccountChangeTriggerService;
use App\Service\ProfileSyncService;

class UserEnsureDao
{
    /**
     * 根据 ext_users.id 获取昵称和用户等级（用于写入 ensures 表）
     * 等级名称来自 ext_user_levels.name（member_level_id）
     * @return array{user_name: string, user_level: string}
     */
    private static function resolveUserInfoByExtUserId(int $extUserId): array
    {
        $row = Db::table('ext_users')
            ->where('id', $extUserId)
            ->select('nickname', 'member_level_id')
            ->first();
        if (!$row) {
            return ['user_name' => '', 'user_level' => ''];
        }
        $userName = $row->nickname ?? '';
        $memberLevelId = (int)($row->member_level_id ?? 0);
        $userLevel = '普通用户';
        if ($memberLevelId > 0) {
            $levelRow = Db::table('ext_user_levels')->where('id', $memberLevelId)->first();
            $userLevel = $levelRow && !empty($levelRow->name) ? trim((string)$levelRow->name) : '普通用户';
        }
        return ['user_name' => $userName, 'user_level' => $userLevel];
    }

    public static function list(array $request): array
    {
        $query = Db::table('ext_user_ensures')
            ->select('id', 'user_id', 'user_name', 'user_level', 'points_to_retrieve', 
                     'application_time', 'status', 'approver_id', 'approval_time', 
                     'reason_for_application', 'reason_for_rejection', 'created_at', 'updated_at')
            ->orderBy('application_time', 'DESC');

        if (($request['user_id'] ?? 0) > 0) {
            $query->where('user_id', $request['user_id']);
        }
        if (!empty($request['user_name'])) {
            $query->where('user_name', 'like', '%' . $request['user_name'] . '%');
        }
        if (($request['status'] ?? 0) > 0) {
            $query->where('status', $request['status']);
        }
        // 已审核：仅查 status in (2,4,5)；若再传 status 则在该范围内精确筛选
        if (!empty($request['status_scope']) && $request['status_scope'] === 'approved') {
            $query->whereIn('status', [2, 4, 5]);
            if (in_array((int)($request['status'] ?? 0), [2, 4, 5], true)) {
                $query->where('status', (int)$request['status']);
            }
        }
        if (($request['approver_id'] ?? 0) > 0) {
            $query->where('approver_id', $request['approver_id']);
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
        $row = Db::table('ext_user_ensures')
            ->select('id', 'user_id', 'user_name', 'user_level', 'points_to_retrieve', 
                     'application_time', 'status', 'approver_id', 'approval_time', 
                     'reason_for_application', 'reason_for_rejection', 'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '用户诚信保申请记录不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    /**
     * 申请取回：校验诚信保余额，扣减 credit_amount、增加 credit_frozen_amount，插入 ensures 与一条冻结账变
     */
    public static function create(array $request): array
    {
        $userId = (int)($request['user_id'] ?? 0);
        if ($userId <= 0) {
            return ['error' => '用户ID无效'];
        }
        $amount = (int)($request['points_to_retrieve'] ?? 0);
        if ($amount <= 0) {
            return ['error' => '申请取回金额必须大于0'];
        }

        $account = Db::table('ext_user_accounts')->where('user_id', $userId)->first();
        if (!$account) {
            return ['error' => '用户账户不存在'];
        }
        $creditAmount = (int)($account->credit_amount ?? 0);
        if ($creditAmount < $amount) {
            return ['error' => '诚信保余额不足，无法申请该取回金额'];
        }

        $userInfo = self::resolveUserInfoByExtUserId($userId);

        Db::beginTransaction();
        try {
            $ensureId = Db::table('ext_user_ensures')->insertGetId([
                'user_id' => $userId,
                'user_name' => $userInfo['user_name'],
                'user_level' => $userInfo['user_level'],
                'points_to_retrieve' => $amount,
                'reason_for_application' => (string)($request['reason_for_application'] ?? ''),
                'status' => 1,
            ]);
            if (!$ensureId) {
                Db::rollBack();
                return ['error' => '创建用户诚信保申请记录失败'];
            }

            Db::table('ext_user_accounts')->where('user_id', $userId)->update([
                'credit_amount' => $creditAmount - $amount,
                'credit_frozen_amount' => (int)($account->credit_frozen_amount ?? 0) + $amount,
            ]);

            $addResult = UserChangeDao::addRecord([
                'user_id' => $userId,
                'change_type' => 2,
                'operation_type' => 3,
                'amount' => $amount,
                'balance_before' => $creditAmount,
                'balance_after' => $creditAmount - $amount,
                'source_id' => 'ensure:' . $ensureId,
                'description' => '诚信保冻结（申请取回）',
            ]);
            if ($addResult['error'] !== '') {
                Db::rollBack();
                return $addResult;
            }

            ProfileSyncService::syncUserScore($userId, $creditAmount - $amount);

            $trigger = AccountChangeTriggerService::triggerAfterCreditChange($userId);
            if ($trigger['error'] !== '') {
                Db::rollBack();
                return $trigger;
            }

            Db::commit();
            ApplicationContext::getContainer()->get(AccountChangeNotifyService::class)->notify($userId);
            return ['error' => ''];
        } catch (\Throwable $e) {
            Db::rollBack();
            return ['error' => '创建申请失败：' . $e->getMessage()];
        }
    }

    public static function update(array $request): array
    {
        $userId = (int)($request['user_id'] ?? 0);
        if ($userId <= 0) {
            return ['error' => '用户ID无效'];
        }
        $userInfo = self::resolveUserInfoByExtUserId($userId);

        $affectedRows = Db::table('ext_user_ensures')
            ->where('id', $request['id'])
            ->update([
                'user_id' => $userId,
                'user_name' => $userInfo['user_name'],
                'user_level' => $userInfo['user_level'],
                'points_to_retrieve' => (int)($request['points_to_retrieve'] ?? 0),
                'reason_for_application' => (string)($request['reason_for_application'] ?? ''),
                'reason_for_rejection' => (string)($request['reason_for_rejection'] ?? ''),
                'status' => (int)($request['status'] ?? 1),
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新用户诚信保申请记录失败'];
        }

        return ['error' => ''];
    }

    public static function del(array $request): array
    {
        $affectedRows = Db::table('ext_user_ensures')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除用户诚信保申请记录失败'];
        }

        return ['error' => ''];
    }

    /**
     * 同意(status=2)：仅更新 ensures 为待执行，写入 approval_time；实际解冻转积分由定时任务在超时后执行
     * 拒绝(status=5)：扣减 credit_frozen_amount，增加 credit_amount，写 1 条账变（解冻）
     */
    public static function changeStatus(array $request): array
    {
        $ensureId = (int)($request['id'] ?? 0);
        $newStatus = (int)($request['status'] ?? 0);
        if ($ensureId <= 0 || !in_array($newStatus, [2, 5], true)) {
            return ['error' => '参数无效'];
        }

        $row = Db::table('ext_user_ensures')->where('id', $ensureId)->first();
        if (!$row) {
            return ['error' => '用户诚信保申请记录不存在'];
        }
        if ((int)$row->status !== 1) {
            return ['error' => '当前申请状态不可审核'];
        }

        $userId = (int)$row->user_id;
        $amount = (int)$row->points_to_retrieve;
        if ($amount <= 0) {
            return ['error' => '申请金额无效'];
        }

        $account = Db::table('ext_user_accounts')->where('user_id', $userId)->first();
        if (!$account) {
            return ['error' => '用户账户不存在'];
        }
        $creditFrozen = (int)($account->credit_frozen_amount ?? 0);
        if ($creditFrozen < $amount) {
            return ['error' => '冻结诚信保余额不足'];
        }

        Db::beginTransaction();
        try {
            if ($newStatus === 2) {
                // 同意：仅更新状态与审核通过时间，不操作账户与账变
                Db::table('ext_user_ensures')->where('id', $ensureId)->update([
                    'status' => 2,
                    'approver_id' => (int)($request['approver_id'] ?? 0),
                    'approval_time' => date('Y-m-d H:i:s'),
                    'reason_for_rejection' => '',
                ]);
            } else {
                // 拒绝(status=5)：解冻回诚信保余额
                $creditAmount = (int)($account->credit_amount ?? 0);
                Db::table('ext_user_accounts')->where('user_id', $userId)->update([
                    'credit_frozen_amount' => $creditFrozen - $amount,
                    'credit_amount' => $creditAmount + $amount,
                ]);

                $r1 = UserChangeDao::addRecord([
                    'user_id' => $userId,
                    'change_type' => 2,
                    'operation_type' => 4,
                    'amount' => $amount,
                    'balance_before' => $creditAmount,
                    'balance_after' => $creditAmount + $amount,
                    'source_id' => 'ensure:' . $ensureId,
                    'description' => '诚信保余额增加（拒绝取回，解冻）',
                ]);
                if ($r1['error'] !== '') {
                    Db::rollBack();
                    return $r1;
                }

                Db::table('ext_user_ensures')->where('id', $ensureId)->update([
                    'status' => 5,
                    'approver_id' => (int)($request['approver_id'] ?? 0),
                    'approval_time' => date('Y-m-d H:i:s'),
                    'reason_for_rejection' => (string)($request['reason_for_rejection'] ?? ''),
                ]);

                ProfileSyncService::syncUserScore($userId, $creditAmount + $amount);

                $trigger = AccountChangeTriggerService::triggerAfterCreditChange($userId);
                if ($trigger['error'] !== '') {
                    Db::rollBack();
                    return $trigger;
                }
            }

            Db::commit();
            if ($newStatus === 5) {
                ApplicationContext::getContainer()->get(AccountChangeNotifyService::class)->notify($userId);
            }
            return ['error' => ''];
        } catch (\Throwable $e) {
            Db::rollBack();
            return ['error' => '操作失败：' . $e->getMessage()];
        }
    }

    /**
     * 执行单条「待执行」申请：解冻转积分并写 2 条账变，最后将状态改为 4（已完成）
     * 供定时任务调用，入参为 ensure 表主键 id
     * @return array{error: string, data: null}
     */
    public static function executeEnsureToPoints(int $ensureId): array
    {
        $row = Db::table('ext_user_ensures')->where('id', $ensureId)->first();
        if (!$row || (int)$row->status !== 2) {
            return ['error' => '记录不存在或状态非待执行', 'data' => null];
        }
        $userId = (int)$row->user_id;
        $amount = (int)$row->points_to_retrieve;
        if ($amount <= 0) {
            return ['error' => '申请金额无效', 'data' => null];
        }

        $account = Db::table('ext_user_accounts')->where('user_id', $userId)->first();
        if (!$account) {
            return ['error' => '用户账户不存在', 'data' => null];
        }
        $creditFrozen = (int)($account->credit_frozen_amount ?? 0);
        if ($creditFrozen < $amount) {
            return ['error' => '冻结诚信保余额不足', 'data' => null];
        }

        $creditAmount = (int)($account->credit_amount ?? 0);
        $pointAmount = (int)($account->point_amount ?? 0);

        Db::beginTransaction();
        try {
            Db::table('ext_user_accounts')->where('user_id', $userId)->update([
                'credit_frozen_amount' => $creditFrozen - $amount,
                'point_amount' => $pointAmount + $amount,
            ]);

            $r1 = UserChangeDao::addRecord([
                'user_id' => $userId,
                'change_type' => 2,
                'operation_type' => 4,
                'amount' => $amount,
                'balance_before' => $creditAmount,
                'balance_after' => $creditAmount,
                'source_id' => 'ensure:' . $ensureId,
                'description' => '解冻诚信保',
            ]);
            if ($r1['error'] !== '') {
                Db::rollBack();
                return $r1;
            }
            $r2 = UserChangeDao::addRecord([
                'user_id' => $userId,
                'change_type' => 1,
                'operation_type' => 1,
                'amount' => $amount,
                'balance_before' => $pointAmount,
                'balance_after' => $pointAmount + $amount,
                'source_id' => 'ensure:' . $ensureId,
                'description' => '积分增加（诚信保取回）',
            ]);
            if ($r2['error'] !== '') {
                Db::rollBack();
                return $r2;
            }

            Db::table('ext_user_ensures')->where('id', $ensureId)->update(['status' => 4]);
            Db::commit();
            ApplicationContext::getContainer()->get(AccountChangeNotifyService::class)->notify($userId);
            return ['error' => '', 'data' => null];
        } catch (\Throwable $e) {
            Db::rollBack();
            return ['error' => '执行失败：' . $e->getMessage(), 'data' => null];
        }
    }

    /**
     * 定时任务：扫描 status=2 且审核通过时间已过 CREDIT_FETCH_TIMEOUT 秒的记录，逐条执行解冻转积分并改为 status=4
     * @return array{error: string, data: array{processed: int}}
     */
    public static function executePendingEnsures(): array
    {
        $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
        $timeout = (int) $config->get('ext_finances.credit_fetch_timeout', 600);

        $rows = Db::table('ext_user_ensures')
            ->where('status', 2)
            ->whereRaw('approval_time <= now() - make_interval(secs => ?)', [$timeout])
            ->get();

        $processed = 0;
        foreach ($rows as $row) {
            $ret = self::executeEnsureToPoints((int) $row->id);
            if ($ret['error'] === '') {
                $processed++;
            }
        }

        return ['error' => '', 'data' => ['processed' => $processed]];
    }
}
