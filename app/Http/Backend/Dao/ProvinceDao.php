<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class ProvinceDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_provinces')
            ->select('id', 'code', 'name', 'remark', 'status', 'created_at', 'updated_at')
            ->orderBy('created_at', 'DESC');

        if (!empty($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%');
        }
        if (!empty($request['code'])) {
            $query->where('code', 'like', '%' . $request['code'] . '%');
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
}
