<?php

declare(strict_types=1);

namespace App\Http\Frontend\Dao;

use Hyperf\DbConnection\Db;

class UserFeedbacks
{
    /**
     * 提交反馈（投诉或建议）
     * @param string $matrixUserId Matrix ID，例如: @u10010:domain.com
     * @param int $type 反馈类型（必须小于100）
     * @param string $content 反馈内容
     * @param string $images 图片JSON数组，默认 '[]'
     * @param string $targetType 投诉对象类型：notes, chat, help, dynamic, other（可选）
     * @param int $targetId 投诉对象ID（可选）
     * @param string $sourceTag 来源标签（可选）
     * @return array ['error' => string, 'data' => array]
     */
    public static function submitFeedback(
        string $matrixUserId,
        int $type,
        string $content,
        string $images = '[]',
        string $targetType = 'other',
        int $targetId = 0,
        string $sourceTag = ''
    ): array {
        // 验证反馈类型（必须小于100）
        if ($type >= 100 || $type < 0) {
            return ['error' => '反馈类型无效，必须小于100且大于等于0', 'data' => []];
        }

        // 验证反馈内容
        $content = trim($content);
        if (empty($content)) {
            return ['error' => '反馈内容不能为空', 'data' => []];
        }

        // 验证和处理 images 字段
        $images = trim($images);
        if (empty($images)) {
            $images = '[]';
        }

        // 验证 images 是否为有效的 JSON 格式
        $imagesArray = json_decode($images, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'images 字段必须是有效的 JSON 格式', 'data' => []];
        }

        // 确保 images 是数组格式
        if (!is_array($imagesArray)) {
            return ['error' => 'images 字段必须是数组格式的 JSON', 'data' => []];
        }

        // 重新编码为 JSON 字符串，确保格式正确
        $imagesJson = json_encode($imagesArray, JSON_UNESCAPED_UNICODE);

        // 验证投诉对象类型（如果提供）
        if (!empty($targetType)) {
            $allowedTargetTypes = ['notes', 'chat', 'help', 'dynamic', 'other'];
            if (!in_array($targetType, $allowedTargetTypes, true)) {
                return ['error' => '投诉对象类型无效，允许的值：notes, chat, help, dynamic, other', 'data' => []];
            }
        }

        // 通过 Matrix ID 查询 ext_users 表获取 id（BIGINT）
        $extUser = Db::table('ext_users')
            ->select('id')
            ->where('user_id', $matrixUserId)
            ->where('status', 1)
            ->first();

        if (!$extUser) {
            return ['error' => '用户不存在', 'data' => []];
        }

        $extUserId = $extUser->id;

        try {
            // 插入反馈记录
            $feedbackId = Db::table('ext_user_feedbacks')->insertGetId([
                'type' => $type,
                'content' => $content,
                'images' => $imagesJson, // JSON格式的图片数组
                'user_id' => $extUserId, // 使用 ext_users.id
                'target_type' => $targetType,
                'target_id' => $targetId,
                'source_tag' => trim($sourceTag),
                'status' => 1, // 默认未审核
                'reviewer_id' => 0 // 默认未审核
            ]);

            return [
                'error' => '',
                'data' => [
                    'id' => $feedbackId,
                    'type' => $type,
                    'content' => $content,
                    'images' => $imagesArray, // 返回数组格式
                    'user_id' => $extUserId,
                    'target_type' => $targetType,
                    'target_id' => $targetId,
                    'source_tag' => trim($sourceTag),
                    'status' => 1
                ]
            ];
        } catch (\Exception $e) {
            return ['error' => '提交反馈失败：' . $e->getMessage(), 'data' => []];
        }
    }
}
