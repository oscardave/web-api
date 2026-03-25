<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class UserHelpDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_user_helps')
            ->select('id', 'user_id', 'user_nickname', 'user_vanity_id', 'content', 'media_urls', 
                     'circle_id', 'circle_name', 'city_id', 'city_name', 'broadcast_enabled', 
                     'broadcast_cost', 'credit_requirement_enabled', 'credit_requirement_value', 
                     'request_status', 'view_count', 'application_count', 'published_at', 
                     'expired_at', 'completed_at', 'status', 'created_at', 'updated_at')
            ->orderBy('created_at', 'DESC');

        if (!empty($request['user_id'])) {
            $query->where('user_id', 'like', '%' . $request['user_id'] . '%');
        }
        if (!empty($request['circle_id'])) {
            $query->where('circle_id', $request['circle_id']);
        }
        if (($request['city_id'] ?? 0) > 0) {
            $query->where('city_id', $request['city_id']);
        }
        if (!empty($request['request_status'])) {
            $query->where('request_status', $request['request_status']);
        }
        if ($request['broadcast_enabled'] ?? false) {
            $query->where('broadcast_enabled', true);
        }
        if ($request['credit_requirement_enabled'] ?? false) {
            $query->where('credit_requirement_enabled', true);
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
        $row = Db::table('ext_user_helps')
            ->select('id', 'user_id', 'user_nickname', 'user_vanity_id', 'content', 'media_urls', 
                     'circle_id', 'circle_name', 'city_id', 'city_name', 'broadcast_enabled', 
                     'broadcast_cost', 'credit_requirement_enabled', 'credit_requirement_value', 
                     'request_status', 'view_count', 'application_count', 'published_at', 
                     'expired_at', 'completed_at', 'status', 'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '帮办请求不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        // 将media_urls数组转换为JSON字符串
        $mediaUrlsStr = json_encode($request['media_urls'] ?? [], JSON_UNESCAPED_UNICODE);

        $affectedRows = Db::table('ext_user_helps')->insert([
            'user_id' => $request['user_id'],
            'user_nickname' => $request['user_nickname'],
            'user_vanity_id' => $request['user_vanity_id'],
            'content' => $request['content'],
            'media_urls' => $mediaUrlsStr,
            'circle_id' => $request['circle_id'],
            'circle_name' => $request['circle_name'],
            'city_id' => $request['city_id'],
            'city_name' => $request['city_name'],
            'broadcast_enabled' => $request['broadcast_enabled'] ? 1 : 0,
            'broadcast_cost' => $request['broadcast_cost'],
            'credit_requirement_enabled' => $request['credit_requirement_enabled'] ? 1 : 0,
            'credit_requirement_value' => $request['credit_requirement_value'],
            'request_status' => $request['request_status'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建帮办请求失败'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        // 将media_urls数组转换为JSON字符串
        $mediaUrlsStr = json_encode($request['media_urls'] ?? [], JSON_UNESCAPED_UNICODE);

        $affectedRows = Db::table('ext_user_helps')
            ->where('id', $request['id'])
            ->update([
                'content' => $request['content'],
                'media_urls' => $mediaUrlsStr,
                'circle_id' => $request['circle_id'],
                'circle_name' => $request['circle_name'],
                'city_id' => $request['city_id'],
                'city_name' => $request['city_name'],
                'broadcast_enabled' => $request['broadcast_enabled'] ? 1 : 0,
                'broadcast_cost' => $request['broadcast_cost'],
                'credit_requirement_enabled' => $request['credit_requirement_enabled'] ? 1 : 0,
                'credit_requirement_value' => $request['credit_requirement_value'],
                'request_status' => $request['request_status'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新帮办请求失败'];
        }

        return ['error' => ''];
    }

    public static function del(array $request): array
    {
        $affectedRows = Db::table('ext_user_helps')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除帮办请求失败'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $affectedRows = Db::table('ext_user_helps')
            ->where('id', $request['id'])
            ->update(['request_status' => $request['request_status']]);

        if ($affectedRows == 0) {
            return ['error' => '修改帮办请求状态失败'];
        }

        return ['error' => ''];
    }

    public static function enableBroadcast(array $request): array
    {
        $affectedRows = Db::table('ext_user_helps')
            ->where('id', $request['id'])
            ->update([
                'broadcast_enabled' => $request['broadcast_enabled'] ? 1 : 0,
                'broadcast_cost' => $request['broadcast_cost'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '设置广播功能失败'];
        }

        return ['error' => ''];
    }

    public static function setCreditRequirement(array $request): array
    {
        $affectedRows = Db::table('ext_user_helps')
            ->where('id', $request['id'])
            ->update([
                'credit_requirement_enabled' => $request['credit_requirement_enabled'] ? 1 : 0,
                'credit_requirement_value' => $request['credit_requirement_value'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '设置诚信保要求失败'];
        }

        return ['error' => ''];
    }

    // Application相关方法
    public static function listApplications(array $request): array
    {
        $query = Db::table('ext_user_help_applications')
            ->select('id', 'help_request_id', 'applicant_user_id', 'applicant_nickname', 
                     'applicant_vanity_id', 'application_message', 'application_status', 
                     'applied_at', 'reviewed_at', 'completed_at', 'status', 'created_at', 'updated_at')
            ->orderBy('applied_at', 'DESC');

        if (($request['help_request_id'] ?? 0) > 0) {
            $query->where('help_request_id', $request['help_request_id']);
        }
        if (!empty($request['applicant_user_id'])) {
            $query->where('applicant_user_id', 'like', '%' . $request['applicant_user_id'] . '%');
        }
        if (!empty($request['application_status'])) {
            $query->where('application_status', $request['application_status']);
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

    public static function getApplication(array $request): array
    {
        $row = Db::table('ext_user_help_applications')
            ->select('id', 'help_request_id', 'applicant_user_id', 'applicant_nickname', 
                     'applicant_vanity_id', 'application_message', 'application_status', 
                     'applied_at', 'reviewed_at', 'completed_at', 'status', 'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '帮办申请不存在', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function createApplication(array $request): array
    {
        $affectedRows = Db::table('ext_user_help_applications')->insert([
            'help_request_id' => $request['help_request_id'],
            'applicant_user_id' => $request['applicant_user_id'],
            'applicant_nickname' => $request['applicant_nickname'],
            'applicant_vanity_id' => $request['applicant_vanity_id'],
            'application_message' => $request['application_message'],
            'application_status' => $request['application_status'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建帮办申请失败'];
        }

        return ['error' => ''];
    }

    public static function updateApplication(array $request): array
    {
        $affectedRows = Db::table('ext_user_help_applications')
            ->where('id', $request['id'])
            ->update([
                'application_message' => $request['application_message'],
                'application_status' => $request['application_status'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新帮办申请失败'];
        }

        return ['error' => ''];
    }

    public static function delApplication(array $request): array
    {
        $affectedRows = Db::table('ext_user_help_applications')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除帮办申请失败'];
        }

        return ['error' => ''];
    }

    public static function changeApplicationStatus(array $request): array
    {
        $affectedRows = Db::table('ext_user_help_applications')
            ->where('id', $request['id'])
            ->update(['application_status' => $request['application_status']]);

        if ($affectedRows == 0) {
            return ['error' => '修改帮办申请状态失败'];
        }

        return ['error' => ''];
    }

    public static function acceptApplication(array $request): array
    {
        $affectedRows = Db::table('ext_user_help_applications')
            ->where('id', $request['id'])
            ->update([
                'application_status' => 'accepted',
                'reviewed_at' => date('Y-m-d H:i:s'),
            ]);

        if ($affectedRows == 0) {
            return ['error' => '接受帮办申请失败'];
        }

        return ['error' => ''];
    }

    public static function rejectApplication(array $request): array
    {
        $affectedRows = Db::table('ext_user_help_applications')
            ->where('id', $request['id'])
            ->update([
                'application_status' => 'rejected',
                'reviewed_at' => date('Y-m-d H:i:s'),
                'remark' => $request['reject_reason'] ?? '',
            ]);

        if ($affectedRows == 0) {
            return ['error' => '拒绝帮办申请失败'];
        }

        return ['error' => ''];
    }
}
