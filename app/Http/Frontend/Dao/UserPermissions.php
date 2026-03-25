<?php

declare(strict_types=1);

namespace App\Http\Frontend\Dao;

use Hyperf\DbConnection\Db;

class UserPermissions
{
    /**
     * 获取用户的拉黑列表
     * @param string $userId 用户ID
     * @return array ['error' => string, 'data' => array]
     */
    public static function getBlockList(string $userId): array
    {
        try {
            $blocks = Db::table('ext_user_permissions')
                ->select(
                    'id',
                    'user_id',
                    'target_id',
                    'remark',
                    'note_access',
                    'status',
                    'created_at',
                    'updated_at'
                )
                ->where('user_id', $userId)
                ->where('status', 1) // 只返回拉黑中的记录
                ->orderBy('created_at', 'desc')
                ->get();

            // 获取被拉黑用户的详细信息
            $targetUserIds = $blocks->pluck('target_id')->toArray();
            if (!empty($targetUserIds)) {
                $userInfos = Db::table('ext_users')
                    ->select('user_id', 'nickname', 'avatar_url')
                    ->whereIn('user_id', $targetUserIds)
                    ->where('status', 1)
                    ->get()
                    ->keyBy('user_id');

                // 合并数据
                $data = [];
                foreach ($blocks as $block) {
                    $userInfo = $userInfos->get($block->target_id);
                    $data[] = [
                        'id' => $block->id,
                        'user_id' => $block->user_id,
                        'target_id' => $block->target_id,
                        'target_user_nickname' => $userInfo->nickname ?? '',
                        'target_user_avatar' => $userInfo->avatar_url ?? '',
                        'remark' => $block->remark,
                        'note_access' => $block->note_access,
                        'status' => $block->status,
                        'created_at' => $block->created_at,
                        'updated_at' => $block->updated_at
                    ];
                }
            } else {
                $data = [];
            }

            return ['error' => '', 'data' => $data];
        } catch (\Exception $e) {
            return ['error' => '获取拉黑列表失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 添加拉黑记录
     * @param string $userId 用户ID
     * @param string $targetUserId 目标用户ID
     * @param string $remark 拉黑备注
     * @return array ['error' => string, 'data' => array]
     */
    public static function addBlock(string $userId, string $targetUserId, string $remark = ''): array
    {
        try {
            // 检查是否已经存在记录
            $existingBlock = Db::table('ext_user_permissions')
                ->where('user_id', $userId)
                ->where('target_id', $targetUserId)
                ->first();

            if ($existingBlock) {
                // 如果记录存在且已解除拉黑，则重新拉黑
                if ($existingBlock->status == 2) {
                    Db::table('ext_user_permissions')
                        ->where('id', $existingBlock->id)
                        ->update([
                            'status' => 1,
                            'remark' => trim($remark)
                        ]);

                    return [
                        'error' => '',
                        'data' => [
                            'id' => $existingBlock->id,
                            'user_id' => $userId,
                            'target_id' => $targetUserId,
                            'remark' => trim($remark),
                            'note_access' => $existingBlock->note_access,
                            'status' => 1
                        ]
                    ];
                } else {
                    // 如果已经是拉黑状态，返回已存在的记录
                    return [
                        'error' => '',
                        'data' => [
                            'id' => $existingBlock->id,
                            'user_id' => $userId,
                            'target_id' => $targetUserId,
                            'remark' => $existingBlock->remark,
                            'note_access' => $existingBlock->note_access,
                            'status' => $existingBlock->status
                        ]
                    ];
                }
            }

            // 插入新记录
            $id = Db::table('ext_user_permissions')->insertGetId([
                'user_id' => $userId,
                'target_id' => $targetUserId,
                'remark' => trim($remark),
                'note_access' => true, // 默认允许访问笔记
                'status' => 1
            ]);

            return [
                'error' => '',
                'data' => [
                    'id' => $id,
                    'user_id' => $userId,
                    'target_id' => $targetUserId,
                    'remark' => trim($remark),
                    'note_access' => true,
                    'status' => 1
                ]
            ];
        } catch (\Exception $e) {
            return ['error' => '拉黑失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 解除拉黑
     * @param string $userId 用户ID
     * @param string $targetUserId 目标用户ID
     * @return array ['error' => string, 'data' => array]
     */
    public static function removeBlock(string $userId, string $targetUserId): array
    {
        try {
            $block = Db::table('ext_user_permissions')
                ->where('user_id', $userId)
                ->where('target_id', $targetUserId)
                ->where('status', 1) // 只更新拉黑中的记录
                ->first();

            if (!$block) {
                return ['error' => '拉黑记录不存在或已解除', 'data' => []];
            }

            Db::table('ext_user_permissions')
                ->where('id', $block->id)
                ->update([
                    'status' => 2
                ]);

            return ['error' => '', 'data' => []];
        } catch (\Exception $e) {
            return ['error' => '解除拉黑失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 检查用户是否被拉黑
     * @param string $userId 用户ID（当前登录用户）
     * @param string $targetUserId 目标用户ID（被检查的用户）
     * @return bool true-已拉黑，false-未拉黑
     */
    public static function isBlocked(string $userId, string $targetUserId): bool
    {
        try {
            $block = Db::table('ext_user_permissions')
                ->where('user_id', $userId)
                ->where('target_id', $targetUserId)
                ->where('status', 1) // 只检查拉黑中的记录
                ->first();

            return $block !== null;
        } catch (\Exception $e) {
            // 查询异常时返回 false，避免影响主流程
            return false;
        }
    }

    /**
     * 设置笔记访问权限
     * @param string $userId 用户ID（控制权限的用户）
     * @param string $targetUserId 目标用户ID（被控制权限的用户）
     * @param bool $noteAccess 笔记访问权限：true-允许访问，false-禁止访问
     * @return array ['error' => string, 'data' => array]
     */
    public static function setNoteAccess(string $userId, string $targetUserId, bool $noteAccess): array
    {
        try {
            // 检查是否已经存在记录
            $existingPermission = Db::table('ext_user_permissions')
                ->where('user_id', $userId)
                ->where('target_id', $targetUserId)
                ->first();

            if ($existingPermission) {
                // 如果记录存在，更新 note_access
                Db::table('ext_user_permissions')
                    ->where('id', $existingPermission->id)
                    ->update([
                        'note_access' => $noteAccess
                    ]);

                return [
                    'error' => '',
                    'data' => [
                        'id' => $existingPermission->id,
                        'user_id' => $userId,
                        'target_id' => $targetUserId,
                        'note_access' => $noteAccess,
                        'status' => $existingPermission->status
                    ]
                ];
            }

            // 如果记录不存在，创建新记录
            $id = Db::table('ext_user_permissions')->insertGetId([
                'user_id' => $userId,
                'target_id' => $targetUserId,
                'remark' => '',
                'note_access' => $noteAccess,
                'status' => 1 // 默认状态为拉黑中（虽然可能不是拉黑，但保持记录存在）
            ]);

            return [
                'error' => '',
                'data' => [
                    'id' => $id,
                    'user_id' => $userId,
                    'target_id' => $targetUserId,
                    'note_access' => $noteAccess,
                    'status' => 1
                ]
            ];
        } catch (\Exception $e) {
            return ['error' => '设置笔记访问权限失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 获取笔记访问权限
     * @param string $userId 用户ID（控制权限的用户）
     * @param string $targetUserId 目标用户ID（被检查的用户）
     * @return bool|null true-允许访问，false-禁止访问，null-未设置权限
     */
    public static function getNoteAccess(string $userId, string $targetUserId): ?bool
    {
        try {
            $permission = Db::table('ext_user_permissions')
                ->select('note_access')
                ->where('user_id', $userId)
                ->where('target_id', $targetUserId)
                // ->where('status', 1) // 只查询有效记录
                ->first();

            if ($permission) {
                return (bool)$permission->note_access;
            }

            return null; // 未设置权限，默认允许访问
        } catch (\Exception $e) {
            // 查询异常时返回 null，表示未设置权限
            return null;
        }
    }
}
