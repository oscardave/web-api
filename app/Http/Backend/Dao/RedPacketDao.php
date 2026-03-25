<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class RedPacketDao
{
    public static function list(array $request): array
    {
        $query = Db::table('red_packets_v')
            ->select('packet_id', 'sender_id', 'sender_user_id', 'sender_nickname', 'room_id', 
                     'packet_type', 'receiver_id', 'receiver_user_id', 'receiver_nickname', 
                     'total_amount', 'per_amount', 'total_count', 'remaining_amount', 
                     'remaining_count', 'message', 'status', 'expire_ts', 'created_ts', 'room_name')
            ->orderBy('created_ts', 'DESC');

        if (!empty($request['name'])) {
            $query->where('sender_nickname', 'like', '%' . $request['name'] . '%');
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
