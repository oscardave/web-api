<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class UserVerifyCodeDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_verify_codes')
            ->select('id', 'user_id', 'contact_type', 'contact_value', 'verify_code', 
                     'verify_type', 'is_used', 'is_expired', 'request_ip', 'created_at', 
                     'expired_at', 'used_at', 'status', 'remark', 'updated_at')
            ->orderBy('created_at', 'DESC');

        if (!empty($request['contactType'])) {
            $query->where('contact_type', $request['contactType']);
        }
        if (!empty($request['contactValue'])) {
            $query->where('contact_value', 'like', '%' . $request['contactValue'] . '%');
        }
        if (!empty($request['verifyType'])) {
            $query->where('verify_type', $request['verifyType']);
        }
        if (($request['status'] ?? 0) != 0) {
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
