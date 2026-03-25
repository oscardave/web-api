<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class UserReportDao
{
    /**
     * 按日期计算用户概览统计并写入 ext_user_reports。
     * 若 report_date 已存在则 UPDATE，否则 INSERT。
     *
     * @param string $reportDate 报告日期，格式 Y-m-d
     * @return array{error: string, data?: array{action: string}}
     */
    public static function computeAndUpsert(string $reportDate): array
    {
        $dateStart = $reportDate . ' 00:00:00';
        $dateEnd = $reportDate . ' 23:59:59';

        $newUsersToday = (int) Db::table('ext_users')
            ->whereRaw("DATE(registration_time AT TIME ZONE 'UTC') = ?", [$reportDate])
            ->where('status', 1)
            ->count();

        $totalUsersCumulative = (int) Db::table('ext_users')
            ->whereRaw("DATE(registration_time AT TIME ZONE 'UTC') <= ?", [$reportDate])
            ->where('status', 1)
            ->count();

        $loginUsersToday = (int) Db::table('user_ips')
            ->whereRaw("DATE(to_timestamp(last_seen / 1000.0) AT TIME ZONE 'UTC') = ?", [$reportDate])
            ->distinct()
            ->count('user_id');

        $loginDevicesResult = Db::selectOne(
            "SELECT COUNT(*) as cnt FROM (SELECT 1 FROM user_ips WHERE DATE(to_timestamp(last_seen / 1000.0) AT TIME ZONE 'UTC') = ? GROUP BY user_id, device_id) t",
            [$reportDate]
        );
        $loginDevicesToday = (int) ($loginDevicesResult->cnt ?? 0);

        // ext_user_notes 使用 created（Unix 秒）而非 created_at
        $notesCreatedToday = (int) Db::table('ext_user_notes')
            ->whereRaw("DATE(to_timestamp(created) AT TIME ZONE 'UTC') = ?", [$reportDate])
            ->where('status', 1)
            ->count();

        $totalNotesCumulative = (int) Db::table('ext_user_notes')
            ->whereRaw("DATE(to_timestamp(created) AT TIME ZONE 'UTC') <= ?", [$reportDate])
            ->where('status', 1)
            ->count();

        $newMembersToday = (int) Db::table('ext_user_exchanges')
            ->where('exchange_type', 1)
            ->where('status', 1)
            ->whereRaw("DATE(exchange_time AT TIME ZONE 'UTC') = ?", [$reportDate])
            ->count();

        $currentMembers = (int) Db::table('ext_users')
            ->where('member_level_id', '>', 10000)
            ->whereNotNull('member_expiration_time')
            ->where('member_expiration_time', '>=', $dateEnd)
            ->where('status', 1)
            ->count();

        $expiredMembers = (int) Db::table('ext_users')
            ->where('member_level_id', '>', 10000)
            ->whereNotNull('member_expiration_time')
            ->where('member_expiration_time', '<', $dateStart)
            ->where('status', 1)
            ->count();

        $row = [
            'report_date' => $reportDate,
            'new_users_today' => $newUsersToday,
            'total_users_cumulative' => $totalUsersCumulative,
            'login_users_today' => $loginUsersToday,
            'login_devices_today' => $loginDevicesToday,
            'super_seat_visits_today' => 0,
            'notes_created_today' => $notesCreatedToday,
            'total_notes_cumulative' => $totalNotesCumulative,
            'new_members_today' => $newMembersToday,
            'current_members' => $currentMembers,
            'expired_members' => $expiredMembers,
            'remark' => '',
        ];

        $exists = Db::table('ext_user_reports')->where('report_date', $reportDate)->first();
        if ($exists) {
            Db::table('ext_user_reports')->where('report_date', $reportDate)->update($row);
            return ['error' => '', 'data' => ['action' => 'update']];
        }
        Db::table('ext_user_reports')->insert($row);
        return ['error' => '', 'data' => ['action' => 'insert']];
    }

    public static function list(array $request): array
    {
        $query = Db::table('ext_user_reports')
            ->select('id', 'report_date', 'new_users_today', 'total_users_cumulative', 
                     'login_users_today', 'login_devices_today', 'super_seat_visits_today', 
                     'notes_created_today', 'total_notes_cumulative', 'new_members_today', 
                     'current_members', 'expired_members', 'remark', 'created_at', 'updated_at')
            ->orderBy('report_date', 'DESC');

        if (!empty($request['report_date_start'])) {
            $query->where('report_date', '>=', $request['report_date_start']);
        }
        if (!empty($request['report_date_end'])) {
            $query->where('report_date', '<=', $request['report_date_end']);
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
}
