<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class ChatRoomDao
{
    public static function list(array $request): array
    {
        $query = Db::table('rooms_v')
            ->select('room_id', 'is_public', 'creator', 'creator_user_id', 'creator_nickname', 
                     'room_name', 'join_rules', 'topic', 'joined_members', 'invited_members', 
                     'left_members', 'banned_members', 'knocked_members', 'created_ts')
            ->orderBy('room_id', 'DESC');

        if (!empty($request['name'])) {
            $query->where('room_name', 'like', '%' . $request['name'] . '%');
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
