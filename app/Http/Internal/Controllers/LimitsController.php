<?php

declare(strict_types=1);

namespace App\Http\Internal\Controllers;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\Di\Annotation\Inject;
use Hyperf\DbConnection\Db;
use App\Http\Middleware\CheckInternalAccess;

/**
 * 内部限制接口：供 Synapse Spam Checker 调用，校验好友/创群/加群等级限制。
 * 仅限内网或本地调用，请勿对外暴露。
 */
#[Controller(prefix: "internal/v1/limits")]
#[Middleware(CheckInternalAccess::class)]
class LimitsController
{
    #[Inject]
    protected HttpResponse $response;

    /**
     * 好友上限：邀请（添加好友）时 Synapse 调用。
     * GET ?user_id=@alice:server
     * 返回 allowed, reason（不允许时）
     */
    #[GetMapping(path: "friend")]
    public function friend(RequestInterface $request)
    {
        $userId = $request->input('user_id', '');
        if ($userId === '') {
            return $this->response->json(['allowed' => false, 'reason' => 'missing user_id']);
        }

        $user = Db::table('ext_users')
            ->select('id', 'friends_count', 'member_level_id')
            ->where('user_id', $userId)
            ->where('status', 1)
            ->first();
        if (!$user) {
            return $this->response->json(['allowed' => true]);
        }

        $levelId = (int)($user->member_level_id ?? 0);
        $friendLimit = -1;
        if ($levelId > 0) {
            $level = Db::table('ext_user_levels')
                ->select('friend_limit')
                ->where('id', $levelId)
                ->where('status', 1)
                ->first();
            $friendLimit = $level ? (int)($level->friend_limit ?? -1) : -1;
        }

        $friendsCount = (int)($user->friends_count ?? 0);
        if ($friendLimit >= 0 && $friendsCount >= $friendLimit) {
            return $this->response->json([
                'allowed' => false,
                'reason'  => '好友人数已达上限，请升级',
            ]);
        }
        return $this->response->json(['allowed' => true]);
    }

    /**
     * 创建群组限制：创群时 Synapse 调用，返回当前等级允许的 max_groups_created、max_members_in_group。
     * Synapse 侧根据已创建群数、房间人数自行比较。
     * GET ?user_id=@alice:server
     */
    #[GetMapping(path: "create_room")]
    public function createRoom(RequestInterface $request)
    {
        $userId = $request->input('user_id', '');
        if ($userId === '') {
            return $this->response->json([
                'max_groups_created'   => 0,
                'max_members_in_group' => 0,
            ]);
        }

        $user = Db::table('ext_users')
            ->select('member_level_id')
            ->where('user_id', $userId)
            ->where('status', 1)
            ->first();
        $levelId = $user ? (int)($user->member_level_id ?? 0) : 0;

        $maxGroupsCreated   = -1;
        $maxMembersInGroup  = -1;
        if ($levelId > 0) {
            $level = Db::table('ext_user_levels')
                ->select('max_groups_created', 'max_members_in_group')
                ->where('id', $levelId)
                ->where('status', 1)
                ->first();
            if ($level) {
                $maxGroupsCreated  = (int)($level->max_groups_created ?? -1);
                $maxMembersInGroup = (int)($level->max_members_in_group ?? -1);
            }
        }

        return $this->response->json([
            'max_groups_created'   => $maxGroupsCreated,
            'max_members_in_group' => $maxMembersInGroup,
        ]);
    }

    /**
     * 加入群组限制：加群时 Synapse 调用，返回当前等级允许的 max_groups_joined。
     * GET ?user_id=@alice:server
     */
    #[GetMapping(path: "join_room")]
    public function joinRoom(RequestInterface $request)
    {
        $userId = $request->input('user_id', '');
        if ($userId === '') {
            return $this->response->json(['max_groups_joined' => 0]);
        }

        $user = Db::table('ext_users')
            ->select('member_level_id')
            ->where('user_id', $userId)
            ->where('status', 1)
            ->first();
        $levelId = $user ? (int)($user->member_level_id ?? 0) : 0;

        $maxGroupsJoined = -1;
        if ($levelId > 0) {
            $level = Db::table('ext_user_levels')
                ->select('max_groups_joined')
                ->where('id', $levelId)
                ->where('status', 1)
                ->first();
            $maxGroupsJoined = $level ? (int)($level->max_groups_joined ?? -1) : -1;
        }

        return $this->response->json(['max_groups_joined' => $maxGroupsJoined]);
    }
}
