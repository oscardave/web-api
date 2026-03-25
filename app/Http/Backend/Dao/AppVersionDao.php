<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class AppVersionDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_app_versions_v')
            ->select('id', 'client_type', 'client_type_name', 'release_type', 'release_type_name', 
                     'appstore_url', 'version_number', 'release_time', 'upgrade_type', 'upgrade_type_name', 
                     'upgrade_message', 'min_compatible_version', 'version_status', 'version_status_name', 
                     'created_at', 'updated_at')
            ->orderBy('release_time', 'DESC');

        if (($request['client_type'] ?? 0) > 0) {
            $query->where('client_type', $request['client_type']);
        }
        if (($request['release_type'] ?? 0) > 0) {
            $query->where('release_type', $request['release_type']);
        }
        if (!empty($request['version_number'])) {
            $query->where('version_number', 'like', '%' . $request['version_number'] . '%');
        }
        if (($request['upgrade_type'] ?? 0) > 0) {
            $query->where('upgrade_type', $request['upgrade_type']);
        }
        if (($request['version_status'] ?? 0) > 0) {
            $query->where('version_status', $request['version_status']);
        }
        if (!empty($request['min_compatible_version'])) {
            $query->where('min_compatible_version', 'like', '%' . $request['min_compatible_version'] . '%');
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
        $row = Db::table('ext_app_versions_v')
            ->select('id', 'client_type', 'client_type_name', 'release_type', 'release_type_name', 
                     'appstore_url', 'version_number', 'release_time', 'upgrade_type', 'upgrade_type_name', 
                     'upgrade_message', 'min_compatible_version', 'version_status', 'version_status_name', 
                     'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => 'APP版本记录不存在，请检查ID是否正确', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        $affectedRows = Db::table('ext_app_versions')->insert([
            'client_type' => $request['client_type'],
            'release_type' => $request['release_type'],
            'appstore_url' => $request['appstore_url'],
            'version_number' => $request['version_number'],
            'release_time' => $request['release_time'],
            'upgrade_type' => $request['upgrade_type'],
            'upgrade_message' => $request['upgrade_message'],
            'min_compatible_version' => $request['min_compatible_version'],
            'version_status' => $request['version_status'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建APP版本失败，请检查数据是否有效或版本号是否重复'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        $affectedRows = Db::table('ext_app_versions')
            ->where('id', $request['id'])
            ->update([
                'client_type' => $request['client_type'],
                'release_type' => $request['release_type'],
                'appstore_url' => $request['appstore_url'],
                'version_number' => $request['version_number'],
                'release_time' => $request['release_time'],
                'upgrade_type' => $request['upgrade_type'],
                'upgrade_message' => $request['upgrade_message'],
                'min_compatible_version' => $request['min_compatible_version'],
                'version_status' => $request['version_status'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新APP版本失败，记录可能不存在或数据未变更'];
        }

        return ['error' => ''];
    }

    public static function del(array $request): array
    {
        $affectedRows = Db::table('ext_app_versions')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除APP版本失败，记录不存在或已被删除'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $affectedRows = Db::table('ext_app_versions')
            ->where('id', $request['id'])
            ->update(['version_status' => $request['version_status']]);

        if ($affectedRows == 0) {
            return ['error' => '修改APP版本状态失败，记录不存在或状态值无效'];
        }

        return ['error' => ''];
    }
}
