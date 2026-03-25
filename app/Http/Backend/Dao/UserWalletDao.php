<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class UserWalletDao
{
    public static function list(array $request): array
    {
        $query = Db::table('user_wallets_v')
            ->select('user_id', 'admin', 'is_guest', 'shadow_banned', 'creation_ts', 
                     'balance', 'updated_ts', 'nickname')
            ->orderBy('creation_ts', 'DESC');

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
