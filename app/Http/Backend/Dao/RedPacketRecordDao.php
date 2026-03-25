<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class RedPacketRecordDao
{
    public static function list(array $request): array
    {
        $query = Db::table('red_packet_records_v')
            ->select('packet_id', 'user_id', 'nickname', 'amount', 'record_created_ts', 
                     'packet_type', 'total_amount', 'remaining_amount', 'remaining_count', 
                     'message', 'status', 'created_ts', 'room_name')
            ->orderBy('record_created_ts', 'DESC');

        if (!empty($request['name'])) {
            $query->where('nickname', 'like', '%' . $request['name'] . '%');
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
