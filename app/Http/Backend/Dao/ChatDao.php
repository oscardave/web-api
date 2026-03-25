<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class ChatDao
{
    private static function escapeLikeWildcards(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    public static function list(array $request): array
    {
        $query = Db::table('chats_v')
            ->select('event_id', 'user_id', 'nickname', 'full_user_id', 'room_id',
                     'sender', 'contains_url', 'origin_server_ts', 'received_ts',
                     'content', 'internal_metadata', 'room_name');

        if (!empty($request['user_id'])) {
            $query->where('user_id', 'like', '%' . self::escapeLikeWildcards($request['user_id']) . '%');
        }
        if (!empty($request['nickname'])) {
            $query->where('nickname', 'like', '%' . self::escapeLikeWildcards($request['nickname']) . '%');
        }
        if (!empty($request['room_name'])) {
            $query->where('room_name', 'like', '%' . self::escapeLikeWildcards($request['room_name']) . '%');
        }
        if (($request['content'] ?? '') !== '') {
            $pattern = '%' . self::escapeLikeWildcards($request['content']) . '%';
            $query->where(function ($q) use ($pattern) {
                $q->whereRaw("(content::jsonb->'content'->>'body') ILIKE ?", [$pattern])
                  ->orWhereRaw("(content::jsonb->'content'->>'url') ILIKE ?", [$pattern]);
            });
        }
        $cu = $request['contains_url'] ?? '';
        if ($cu !== '' && $cu !== null) {
            $val = $cu === true || $cu === 'true' || $cu === 1;
            $query->where('contains_url', $val);
        }

        $total = (clone $query)->count();

        $offset = ($request['page'] - 1) * $request['pageSize'];
        $rows = $query->orderBy('origin_server_ts', 'DESC')
                      ->limit($request['pageSize'])
                      ->offset($offset)
                      ->get()
                      ->toArray();

        return [
            'error' => '',
            'data' => [
                'records' => $rows,
                'total' => $total,
            ],
        ];
    }
}
