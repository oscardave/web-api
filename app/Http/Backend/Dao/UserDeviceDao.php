<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class UserDeviceDao
{
    public static function list(array $request): array
    {
        $query = Db::table('user_devices_v as v')
            ->leftJoin('ext_users as eu', 'v.full_user_id', '=', 'eu.user_id')
            ->select(
                'eu.id as ext_user_id',
                'v.user_id',
                'v.full_user_id',
                'v.device_id',
                'v.last_seen',
                'v.device_name',
                'v.ip',
                'v.user_agent',
                'v.hidden',
                'v.nickname'
            )
            ->orderBy('v.last_seen', 'DESC');

        if (!empty($request['username'])) {
            $query->where('v.full_user_id', 'like', '%' . $request['username'] . '%');
        }
        if (!empty($request['nickname'])) {
            $query->where('v.nickname', 'like', '%' . $request['nickname'] . '%');
        }
        if (!empty($request['device_name'])) {
            $query->where('v.device_name', 'like', '%' . $request['device_name'] . '%');
        }
        if (!empty($request['device_id'])) {
            $query->where('v.device_id', 'like', '%' . $request['device_id'] . '%');
        }
        // last_seen 在 Synapse devices 表中为 BIGINT，单位：毫秒（与 devices.py 中 since_ms 一致）
        if (!empty($request['login_time_start'])) {
            $startMs = strtotime($request['login_time_start'] . ' 00:00:00') * 1000;
            $query->where('v.last_seen', '>=', $startMs);
        }
        if (!empty($request['login_time_end'])) {
            $endMs = strtotime($request['login_time_end'] . ' 23:59:59') * 1000;
            $query->where('v.last_seen', '<=', $endMs);
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
