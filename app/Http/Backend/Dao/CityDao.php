<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class CityDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_cities_v')
            ->select('id', 'code', 'name', 'province_code', 'province_name', 
                     'remark', 'status', 'created_at', 'updated_at')
            ->orderBy('created_at', 'DESC');

        if (!empty($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%');
        }
        if (!empty($request['code'])) {
            $query->where('code', 'like', '%' . $request['code'] . '%');
        }
        if (!empty($request['province_code'])) {
            $query->where('province_code', $request['province_code']);
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
