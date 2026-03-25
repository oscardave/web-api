<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class UserAlbumDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_user_albums')
            ->select('id', 'user_id', 'name', 'cover_url', 'description', 'type', 
                     'photo_count', 'max_photo_count', 'sort_order', 'allow_comment', 
                     'allow_download', 'status', 'created_at', 'updated_at')
            ->orderBy('created_at', 'DESC');

        if (($request['user_id'] ?? 0) > 0) {
            $query->where('user_id', $request['user_id']);
        }
        if (!empty($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%');
        }
        if (($request['type'] ?? 0) > 0) {
            $query->where('type', $request['type']);
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

    public static function get(array $request): array
    {
        $row = Db::table('ext_user_albums')
            ->select('id', 'user_id', 'name', 'cover_url', 'description', 'type', 
                     'photo_count', 'max_photo_count', 'sort_order', 'allow_comment', 
                     'allow_download', 'status', 'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '用户相册记录不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        $affectedRows = Db::table('ext_user_albums')->insert([
            'user_id' => $request['user_id'],
            'name' => $request['name'],
            'cover_url' => $request['cover_url'],
            'description' => $request['description'],
            'type' => $request['type'],
            'photo_count' => $request['photo_count'],
            'max_photo_count' => $request['max_photo_count'],
            'sort_order' => $request['sort_order'],
            'allow_comment' => $request['allow_comment'] ? 1 : 0,
            'allow_download' => $request['allow_download'] ? 1 : 0,
            'status' => $request['status'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建用户相册记录失败'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        $affectedRows = Db::table('ext_user_albums')
            ->where('id', $request['id'])
            ->update([
                'user_id' => $request['user_id'],
                'name' => $request['name'],
                'cover_url' => $request['cover_url'],
                'description' => $request['description'],
                'type' => $request['type'],
                'photo_count' => $request['photo_count'],
                'max_photo_count' => $request['max_photo_count'],
                'sort_order' => $request['sort_order'],
                'allow_comment' => $request['allow_comment'] ? 1 : 0,
                'allow_download' => $request['allow_download'] ? 1 : 0,
                'status' => $request['status'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新用户相册记录失败'];
        }

        return ['error' => ''];
    }

    public static function del(array $request): array
    {
        $affectedRows = Db::table('ext_user_albums')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除用户相册记录失败'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $affectedRows = Db::table('ext_user_albums')
            ->where('id', $request['id'])
            ->update(['status' => $request['status']]);

        if ($affectedRows == 0) {
            return ['error' => '修改用户相册状态失败'];
        }

        return ['error' => ''];
    }
}
