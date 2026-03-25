<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;

class UserFeedbackDao
{
    public static function list(array $request): array
    {
        $query = Db::table('ext_user_feedbacks_v')
            ->select('id', 'type', 'type_name', 'content', 'user_id', 'target_type', 
                     'target_id', 'source_tag', 'status', 'status_name', 'reviewer_id', 
                     'created_at', 'updated_at')
            ->orderBy('created_at', 'DESC');

        if (($request['type'] ?? 0) > 0) {
            $query->where('type', $request['type']);
        }
        if (!empty($request['content'])) {
            $query->where('content', 'like', '%' . $request['content'] . '%');
        }
        if (($request['user_id'] ?? 0) > 0) {
            $query->where('user_id', $request['user_id']);
        }
        if (!empty($request['target_type'])) {
            $query->where('target_type', $request['target_type']);
        }
        if (($request['target_id'] ?? 0) > 0) {
            $query->where('target_id', $request['target_id']);
        }
        if (!empty($request['source_tag'])) {
            $query->where('source_tag', 'like', '%' . $request['source_tag'] . '%');
        }
        if (($request['status'] ?? 0) > 0) {
            $query->where('status', $request['status']);
        }
        if (($request['reviewer_id'] ?? 0) > 0) {
            $query->where('reviewer_id', $request['reviewer_id']);
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
        $row = Db::table('ext_user_feedbacks_v')
            ->select('id', 'type', 'type_name', 'content', 'user_id', 'target_type', 
                     'target_id', 'source_tag', 'status', 'status_name', 'reviewer_id', 
                     'created_at', 'updated_at')
            ->where('id', $request['id'])
            ->first();

        if (!$row) {
            return ['error' => '用户反馈记录不存在，请检查ID是否正确', 'data' => null];
        }

        return [
            'error' => '',
            'data' => (array)$row,
        ];
    }

    public static function create(array $request): array
    {
        $affectedRows = Db::table('ext_user_feedbacks')->insert([
            'type' => $request['type'],
            'content' => $request['content'],
            'user_id' => $request['user_id'],
            'target_type' => $request['target_type'],
            'target_id' => $request['target_id'],
            'source_tag' => $request['source_tag'],
            'status' => $request['status'],
        ]);

        if ($affectedRows == 0) {
            return ['error' => '创建用户反馈失败，请检查数据是否有效'];
        }

        return ['error' => ''];
    }

    public static function update(array $request): array
    {
        $affectedRows = Db::table('ext_user_feedbacks')
            ->where('id', $request['id'])
            ->update([
                'type' => $request['type'],
                'content' => $request['content'],
                'user_id' => $request['user_id'],
                'target_type' => $request['target_type'],
                'target_id' => $request['target_id'],
                'source_tag' => $request['source_tag'],
                'status' => $request['status'],
                'reviewer_id' => $request['reviewer_id'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '更新用户反馈失败，记录可能不存在或数据未变更'];
        }

        return ['error' => ''];
    }

    public static function del(array $request): array
    {
        $affectedRows = Db::table('ext_user_feedbacks')
            ->where('id', $request['id'])
            ->delete();

        if ($affectedRows == 0) {
            return ['error' => '删除用户反馈失败，记录不存在或已被删除'];
        }

        return ['error' => ''];
    }

    public static function changeStatus(array $request): array
    {
        $affectedRows = Db::table('ext_user_feedbacks')
            ->where('id', $request['id'])
            ->update([
                'status' => $request['status'],
                'reviewer_id' => $request['reviewer_id'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '修改用户反馈状态失败，记录不存在或状态值无效'];
        }

        return ['error' => ''];
    }

    public static function review(array $request): array
    {
        $affectedRows = Db::table('ext_user_feedbacks')
            ->where('id', $request['id'])
            ->update([
                'status' => $request['status'],
                'reviewer_id' => $request['reviewer_id'],
            ]);

        if ($affectedRows == 0) {
            return ['error' => '审核用户反馈失败，记录不存在或状态值无效'];
        }

        return ['error' => ''];
    }
}
