<?php

declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use App\Caches\ConfigsCache;
use App\Caches\VipCache;
use App\Http\Frontend\Dao\Combo;
use App\Http\Frontend\Dao\ConfigsDao;
use App\Pay\Base;
use Hyperf\HttpServer\Contract\RequestInterface;

use App\Caches\UsersCache;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\DbConnection\Db;
use App\Http\Middleware\Cors;
use App\Common\Utils;
use App\Common\Cache;
use App\Common\HttpClient;


//@Middleware(Cors::class)

#[Controller(prefix: "api/v1/circles")]
class CirclesController extends FrontendController
{
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "create")]
    public function createCircle(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr('无效请求');
        }
        $name = $request->input("name");
        $avatar = $request->input("avatar");
        $describe = $request->input("describe");
        $circle_type = $request->input("circle_type");
        if (empty($name) || empty($avatar) || empty($describe)) {
            return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
        }
        $now = time();
        Db::insert("insert into ext_circles(circle_name,circle_avatar_url,circle_description,circle_type,owner_id,owner_nickname,created_time) values(?,?,?,?,?,?,?)", [
            $name,
            $avatar,
            $describe,
            $circle_type,
            $temps->name,
            $temps->nickname,
            date("Y-m-d H:i:s", $now),
        ]);
        $circles = Db::table("ext_circles")->where("circle_name", $name)->where("owner_id", $temps->name)->first();
        Db::insert("insert into ext_circle_users(circle_id,user_id,user_nickname,user_avatar_url,role_type,join_time,created_time,created_at,circle_avatar,circle_type,circle_name,user_score) values(?,?,?,?,?,?,?,?,?,?,?,?)", [
            $circles->circle_id,
            $temps->name,
            $temps->nickname,
            $temps->avatar_url,
            "owner",
            date("Y-m-d H:i:s", $now),
            date("Y-m-d H:i:s", $now),
            date("Y-m-d H:i:s", $now),
            $circles->circle_avatar_url,
            $circles->circle_type,
            $circles->circle_name,
            $temps->score ?? 0,
        ]);
        return self::jsonOk("请等待审核");
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "index")]
    public function index(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);


        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }

        //头部信息,我参加的圈子信息（仅已审核通过的圈子 status=1）
        $userCircle = Db::table("ext_circle_users")
            ->join("ext_circles", "ext_circles.circle_id", "=", "ext_circle_users.circle_id")
            ->where("ext_circle_users.user_id", $temps->name)
            ->where("ext_circle_users.status", 1)
            ->where("ext_circles.status", 1)
            ->select("ext_circle_users.circle_avatar", "ext_circle_users.circle_id", "ext_circle_users.member_status", "ext_circle_users.can_view_help", "ext_circle_users.can_post_help", "ext_circle_users.circle_name")
            ->get();
        $unRead = 0;
        $unReadList = [];
        $userCircleIdList = [];
        foreach ($userCircle as $v) {
            $read = Db::table("ext_circle_read")->where("user_id", $temps->name)->where("read_type", 1)->where("circle_id", $v->circle_id)->first();
            $num = Db::table("ext_circles")->where("circle_id", $v->circle_id)->where("last_help_post_time", ">=", $read->updated ?? "1970-01-01 00:00:00")->count();
            $unRead += $num;
            // $unReadList[$v->circle_id]=$num;
            array_push($unReadList, [$v->circle_id => $num]);
            array_push($userCircleIdList, $v->circle_id);
        }
        $userCircleSetting = Db::table("ext_circle_users_setting")->where("user_id", $temps->name)->first();

        //按照配置，城市，圈子排序
        $circle_id = array_filter(explode(',', $userCircleSetting->circle_id ?? ''));
        $userCities = array_filter(explode(',', $userCircleSetting->city ?? ''));
        if (count($circle_id) > 0) {
            $userCircleIdList = $circle_id;
        }
        $list = Db::table('ext_circle_content as c')
            ->join('ext_circles as cc', 'cc.circle_id', '=', 'c.circle_id')
            ->where('cc.status', 1)
            ->whereIn("c.circle_id", $userCircleIdList)
            ->where(function ($query) use ($userCities, $circle_id) {
                // 城市匹配s
                if (!empty($userCities)) {
                    $query->where(function ($q) use ($userCities) {
                        foreach ($userCities as $city) {
                            $q->orWhere('c.city', 'like', "%{$city}%");
                        }
                    });
                }

                // 或 圈子匹配
                //                    if (!empty($circle_id)) {
                //                        $query->orWhereIn('c.circle_id', $circle_id);
                //                    }

            })
            ->orderBy('c.id', 'desc')
            ->select(
                'c.*',
                'cc.circle_name'
            )
            ->paginate(15);

        // }
        $userCircleContent = Db::table("ext_circle_content")->where("user_id", $temps->name)->orderBy("id", "desc")->get()->toArray();

        // 当前用户的会员等级与徽章信息
        $currentUserBadgeInfo = ConfigsDao::getUserBadgeInfo($temps->name);
        $currentUser = [
            'user_id' => $temps->name,
            'user_score' => $temps->score ?? 0,
            'member_level_id' => $currentUserBadgeInfo['member_level_id'],
            'member_level_name' => $currentUserBadgeInfo['member_level_name'],
            'identity_badge_id' => $currentUserBadgeInfo['identity_badge_id'],
            'identity_badge_name' => $currentUserBadgeInfo['identity_badge_name'],
            'credit_badge_id' => $currentUserBadgeInfo['credit_badge_id'],
            'credit_badge_name' => $currentUserBadgeInfo['credit_badge_name'],
            'pure_badge_enabled' => $currentUserBadgeInfo['pure_badge_enabled'],
        ];

        // 为帮办列表中的每条记录补充发帖人的会员等级与徽章信息
        $listItems = $list->items();
        $creatorUserIds = array_unique(array_filter(array_map(function ($item) {
            return $item->user_id ?? null;
        }, $listItems)));
        $creatorBadgeMap = ConfigsDao::getUsersBadgeInfoBatch($creatorUserIds);
        $enrichedList = [];
        foreach ($listItems as $item) {
            $arr = (array)$item;
            $creatorId = $arr['user_id'] ?? '';
            $badgeInfo = $creatorBadgeMap[$creatorId] ?? [
                'member_level_id' => 0, 'member_level_name' => '',
                'identity_badge_id' => 0, 'identity_badge_name' => '',
                'credit_badge_id' => 0, 'credit_badge_name' => '',
                'pure_badge_enabled' => false,
            ];
            $arr['member_level_id'] = $badgeInfo['member_level_id'];
            $arr['member_level_name'] = $badgeInfo['member_level_name'];
            $arr['identity_badge_id'] = $badgeInfo['identity_badge_id'];
            $arr['identity_badge_name'] = $badgeInfo['identity_badge_name'];
            $arr['credit_badge_id'] = $badgeInfo['credit_badge_id'];
            $arr['credit_badge_name'] = $badgeInfo['credit_badge_name'];
            $arr['pure_badge_enabled'] = $badgeInfo['pure_badge_enabled'];
            $enrichedList[] = $arr;
        }

        //更新最新已读时间
        // Db::update("update ext_circle_users_read set updated=? where user_id=?",[time(),$temps->name]);
        $data = [
            "latest" => [
                "circle" => $userCircle,
                "un_read" => $unRead,
                "book" => count($userCircle) . "/10",
                "un_read_list" => $unReadList,
            ],
            "list" => $enrichedList,
            "list_total" => $list->total(),
            "user_score" => $temps->score ?? 0,
            "current_user" => $currentUser,
            "user_circle" => array_map(function ($item) use ($currentUserBadgeInfo) {
                $arr = (array)$item;
                $arr['member_level_id'] = $currentUserBadgeInfo['member_level_id'];
                $arr['member_level_name'] = $currentUserBadgeInfo['member_level_name'];
                $arr['identity_badge_id'] = $currentUserBadgeInfo['identity_badge_id'];
                $arr['identity_badge_name'] = $currentUserBadgeInfo['identity_badge_name'];
                $arr['credit_badge_id'] = $currentUserBadgeInfo['credit_badge_id'];
                $arr['credit_badge_name'] = $currentUserBadgeInfo['credit_badge_name'];
                $arr['pure_badge_enabled'] = $currentUserBadgeInfo['pure_badge_enabled'];
                return $arr;
            }, $userCircleContent),
        ];
        return self::jsonResult($data);
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[RequestMapping(path: "circle_user_setting", methods: ['GET', 'POST'])]
    public function circleUserSetting(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $city = $request->input("city");
        $circleId = $request->input("circle_id");

        $has = DB::table("ext_circle_users_setting")->where("user_id", $temps->name)->first();
        if (!$has) {
            Db::insert("insert into ext_circle_users_setting(circle_id,city,user_id) values(?,?,?)", [
                $circleId,
                $city ?? "",
                $temps->name,
            ]);
            $has = DB::table("ext_circle_users_setting")->where("user_id", $temps->name)->first();
        }
        $tempList = [];
        if (!empty($has->circle_id)) {
            $temp = explode(",", $has->circle_id);

            foreach ($temp as $v) {
                $detail = Db::table("ext_circles")->where("circle_id", $v)->where("status", 1)->select("circle_id", "circle_name", "circle_avatar_url as circle_avatar", "circle_type")->first();
                if ($detail) {
                    array_push($tempList, $detail);
                }
            }
        }


        $has->circle_detail = $tempList;
        if ($request->getMethod() == "GET") {
            return self::jsonResult((array)$has);
        } else {
            Db::update("update ext_circle_users_setting set city=?,circle_id=? where user_id=?", [$city ?? "", $circleId, $temps->name]);
            return self::jsonOk("设置成功");
        }
    }


    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "circle_mine")]
    public function circleMine(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }

        //头部信息,我参加的/我管理的圈子信息（仅已审核通过的圈子 status=1）
        $userJoin = Db::table("ext_circle_users")->join("ext_circles", "ext_circles.circle_id", "=", "ext_circle_users.circle_id")->where("ext_circle_users.user_id", $temps->name)->where("ext_circle_users.status", 1)->where("ext_circles.status", 1)
            ->select("ext_circle_users.circle_avatar", "ext_circle_users.circle_id", "ext_circles.circle_type", "ext_circles.circle_name", "ext_circles.member_limit", "ext_circles.circle_avatar_url as circle_avatar")->get();
        $userManage = Db::table("ext_circle_users")->join("ext_circles", "ext_circles.circle_id", "=", "ext_circle_users.circle_id")->whereIn("ext_circle_users.role_type", ["admin", "owner"])->where("ext_circle_users.user_id", $temps->name)->where("ext_circles.status", 1)
            ->select("ext_circle_users.circle_avatar", "ext_circle_users.circle_id", "ext_circles.circle_type", "ext_circles.circle_name", "ext_circles.member_limit", "ext_circles.circle_avatar_url as circle_avatar")->get();
        $apply = Db::table('ext_circle_invite')
            ->select('circle_id', Db::raw('COUNT(*) as num'))
            ->where('user_id', $temps->name)
            ->where('type', 0)
            ->groupBy(["circle_id"])
            ->get();
        $applyTotalUnread = $userManageTotalUnread = $userJoinTotalUnread = 0;

        foreach ($userJoin as $v) {
            $tempCircles = Db::table("ext_circles")->where("circle_id", $v->circle_id)->first();
            $inviteNum = Db::table("ext_circle_invite")->where("user_id", $temps->name)->where("circle_id", $v->circle_id)->where("status", 1)->where("type", 0)->count();
            $tempRead = Db::table("ext_circle_read")->where("read_type", 2)->where("user_id", $temps->name)->where("circle_id", $v->circle_id)->first();
            $v->unread = Db::table("ext_circle_content")->where("circle_id", $v->circle_id)->where("created", ">=", $tempRead->created ?? 0)->count();
            $userJoinTotalUnread += $v->unread;
            $v->invite_num = $inviteNum . "/" . $tempCircles->invitation_code_count;
        }
        foreach ($userManage as $v) {
            $tempCircles = Db::table("ext_circles")->where("circle_id", $v->circle_id)->first();
            $inviteNum = Db::table("ext_circle_invite")->where("user_id", $temps->name)->where("circle_id", $v->circle_id)->where("status", 1)->where("type", 0)->count();
            $v->invite_num = $inviteNum . "/" . $tempCircles->invitation_code_count;
            $tempRead = Db::table("ext_circle_read")->where("read_type", 2)->where("user_id", $temps->name)->where("circle_id", $v->circle_id)->first();
            $v->unread = Db::table("ext_circle_content")->where("circle_id", $v->circle_id)->where("created", ">=", $tempRead->created ?? 0)->count();
            $userManageTotalUnread += $v->unread;
        }
        foreach ($apply as $v) {
            $tempCircles = Db::table("ext_circles")->where("circle_id", $v->circle_id)->first();
            $v->circle_avatar = $tempCircles->circle_avatar_url;
            $v->circle_name = $tempCircles->circle_name;
            $v->member_limit = $tempCircles->member_limit;
            $v->circle_type = $tempCircles->circle_type;
            $tempRead = Db::table("ext_circle_read")->where("read_type", 2)->where("user_id", $temps->name)->where("circle_id", $v->circle_id)->first();
            $readCreated = $tempRead ? ($tempRead->created ?? 0) : 0;
            $v->unread = Db::table("ext_circle_content")->where("circle_id", $v->circle_id)->where("created", ">=", $readCreated)->count();
            $applyTotalUnread += $v->unread;
            $inviteNum = Db::table("ext_circle_invite")->where("user_id", $temps->name)->where("circle_id", $v->circle_id)->where("status", 1)->where("type", 0)->count();
            $v->invite_num = $inviteNum . "/" . $tempCircles->invitation_code_count;
        }
        $data = [
            "join" => ["total_unread" => $userJoinTotalUnread, "list" => $userJoin],
            "manage" => ["total_unread" => $userManageTotalUnread, "list" => $userManage],
            "apply" => ["total_unread" => $applyTotalUnread, "list" => $apply],
        ];
        return self::jsonResult($data);
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "circle_invite")]
    public function circleInvite(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("id");
        $circleId = $request->input("circle_id");
        if (empty($id) || empty($circleId)) {
            return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
        }
        $userCircle = Db::table("ext_circle_users")->where("circle_id", $circleId)->where("user_id", $temps->name)->first();
        if (!$userCircle) {
            return self::jsonErr(Utils::ChangeMessage('您不是该圈子成员，无法邀请', $request));
        }
        $idList = explode(",", $id);
        //        if($userCircle->role_type=="member"){
        //            foreach($idList as $v){
        //                $has=Db::table("ext_circle_invite")->where("user_id",$v)->where("circle_id",$circleId)->first();
        //                if($has){
        //                   continue;
        //                }
        //                Db::insert("insert into ext_circle_invite(user_id,invite_id,invite_avatar,invite_nickname,circle_id,created) values(?,?,?,?,?,?)",[
        //                    $v,
        //                    $temps->name,
        //                    $temps->avatar_url,
        //                    $temps->nickname,
        //                    $circleId,
        //                    time(),
        //                ]);
        //            }
        //        }else{

        //管理直接拉。
        foreach ($idList as $v) {
            $has = Db::table("ext_circle_users")->where("user_id", $v)->where("circle_id", $circleId)->first();
            if ($has) {
                continue;
            }
            $user = Db::table("users_v")->where("name", $v)->first();
            if (!$user) {
                continue;
            }
            Db::insert("insert into ext_circle_users(user_id,circle_id,user_nickname,user_avatar_url,join_method,inviter_id,inviter_nickname,join_time,created_at,hand_name) values(?,?,?,?,?,?,?,?,?,?)", [
                $v,
                $circleId,
                $user->nickname ?? '',
                $user->avatar_url ?? '',
                "direct",
                $temps->name,
                $temps->nickname ?? '',
                date("Y-m-d H:i:s", time()),
                date("Y-m-d H:i:s", time()),
                $temps->nickname ?? '',
            ]);

            Db::insert("insert into ext_circle_invite(user_id,invite_id,invite_avatar,invite_nickname,circle_id,created,status) values(?,?,?,?,?,?,?)", [
                $v,
                $temps->name,
                $temps->avatar_url ?? '',
                $temps->nickname ?? '',
                $circleId,
                time(),
                1,
            ]);
        }
        //     }


        return self::jsonOk("邀请成功");
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "circle_invite_list")]
    public function circleInviteList(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("circle_id");
        $list = Db::table("ext_circle_invite")->where("circle_id", $id)->where("type", 0)->where("user_id", $temps->name)->get()->toArray();
        return self::jsonResult($list);
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "circle_invite_confirm")]
    public function circleInviteConfirm(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("id");
        if (empty($id)) {
            return self::jsonExpireErr(Utils::ChangeMessage('参数错误', $request));
        }
        Db::update("update ext_circle_invite set status=1 where id=?", [
            $id,
        ]);
        return self::jsonOk();
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "circle_apply")]
    public function circleApply(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("circle_id");
        if (empty($id)) {
            return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
        }
        $circles = Db::table("ext_circles")->where("circle_id", $id)->where("status", 1)->first();
        if (!$circles) {
            return self::jsonErr(Utils::ChangeMessage('圈子不存在或未通过审核', $request));
        }
        if ($circles->limit_level_status == 1) {
            $tempLevelList = explode(",", $circles->limit_level);
            if (!in_array($temps->level, $tempLevelList)) {
                return self::jsonErr(Utils::ChangeMessage('不符合等级', $request));
            }
        }
        $num = Db::table("ext_circle_invite")->where("type", 0)->where("circle_id", $id)->where("status", 1)->where("user_id", $temps->name)->count();
        if ($num < $circles->invitation_code_count) {
            return self::jsonErr(Utils::ChangeMessage('邀请人数未满足', $request));
        }
        Db::insert("insert into ext_circle_invite(type,user_id,user_nickname,user_avatar,created,circle_id) values(?,?,?,?,?,?)", [
            1,
            $temps->name,
            $temps->name,
            $temps->avatar_url,
            time(),
            $id,
        ]);
        return self::jsonOk("申请成功，请等待审核");
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "circle_search")]
    public function circleSearch(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $name = trim($request->input("search_content"));
        $data = Db::table("ext_circles")->where("status", 1)->where(function ($q) use ($name) {
            $q->where("circle_id", $name)->orWhere("circle_name", "like", "%{$name}%");
        })->where("circle_type", "public")->get();
        return self::jsonObject($data);
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "circle_detail")]
    public function circleDetail(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("circle_id");
        if (empty($id)) {
            return self::jsonExpireErr(Utils::ChangeMessage('参数错误', $request));
        }
        $circles = Db::table("ext_circles")->where("circle_id", $id)->where("status", 1)->first();
        if (!$circles) {
            return self::jsonErr(Utils::ChangeMessage('圈子不存在或未通过审核', $request));
        }
        $userCircles = Db::table("ext_circle_users")->where("circle_id", $id)->where("user_id", $temps->name)->first();

        $notice = Db::table("ext_circle_notice")->where("circle_id", $id)->get();
        $unRead = Db::table("ext_circle_read")->where("circle_id", $id)->where("user_id", $temps->name)->where("read_type", 2)->first();
        $unReadTime = $unRead->updated ?? 0;

        $noticeUnread = Db::table("ext_circle_notice")->where("circle_id", $id)->where("created", ">=", $unReadTime)->count();
        //是否限制普通用户浏览

        $list = Db::table("ext_circle_content")->where("circle_id", $id)->orderBy("id", "desc")->paginate(15);

        $ownerSql = "SELECT *
  FROM ext_circle_users
  WHERE circle_id = ? AND role_type = 'owner'
  LIMIT 1";
        $adminSql = "
    SELECT * 
    FROM ext_circle_users
    WHERE circle_id = ? AND role_type = 'admin'
    ORDER BY user_score DESC";
        $allSql = "
    SELECT *
    FROM ext_circle_users
    WHERE circle_id = ?
    ORDER BY user_score DESC";

        $honor = [];
        $honor["owner"] = DB::select($ownerSql, [$id]);
        $honor["admin"] = DB::select($adminSql, [$id]);
        $honor["all"] = DB::select($allSql, [$id]);
        //更新读
        $now = time();
        $hasReadLog = Db::table("ext_circle_read")->where("user_id", $temps->name)->where("circle_id", $id)->where("read_type", 2)->first();
        if ($hasReadLog) {
            Db::update("update ext_circle_read set updated=? where circle_id=? and user_id=? and read_type=2", [
                $now,
                $id,
                $temps->name,
            ]);
        } else {
            Db::insert("insert into ext_circle_read(circle_id,user_id,read_type,created,updated) values(?,?,?,?,?)", [
                $id,
                $temps->name,
                2,
                $now,
                $now
            ]);
        }

        $unPass = Db::table("ext_circle_invite")->where("circle_id", $id)->where("type", 1)->where("status", 0)->count();
        $circles->un_pass = $unPass;
        $data = [
            "index" => [
                "circles" => $circles,
                "notice" => $notice,
                "notice_unread" => $noticeUnread
            ],
            "rows" => ["list" => $list->items(), "total" => $list->total()],
            "honor" => $honor,
        ];
        if ($circles->limit_views_level_status == 1) {
            $tempLevelList = explode(",", $circles->limit_views_level);
            if (!in_array($temps->level, $tempLevelList)) {
                $data["rows"] = [];
            }
        }
        if (!$userCircles || !($userCircles->can_view_help ?? false)) {
            $data["rows"] = [];
        }
        return self::jsonResult($data);
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "circle_detail_setting")]
    public function circleDetailSetting(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("circle_id");
        if (empty($id)) {
            return self::jsonExpireErr(Utils::ChangeMessage('参数错误', $request));
        }
        $Circle = Db::table("ext_circle_users")->where("user_id", $temps->name)->where("circle_id", $id)->first();
        if ($Circle && $Circle->role_type == "public") {
            return self::jsonExpireErr(Utils::ChangeMessage('没有权限', $request));
        }
        $memberLimit = $request->input("invitation_code_count");
        $banStatus = $request->input("ban_status");
        $limit_level_status = $request->input("limit_level_status");
        $limit_level = $request->input("limit_level");
        $limit_views_level_status = $request->input("limit_views_level_status");
        $limit_views_level = $request->input("limit_views_level");

        Db::update("update ext_circles set invitation_code_count=?,ban_status=?,limit_level_status=?,limit_views_level_status=?,limit_level=?,limit_views_level=? where id=?", [
            $memberLimit,
            $banStatus,
            $limit_level_status,
            $limit_views_level_status,
            $limit_level,
            $limit_views_level,
        ]);
        return self::jsonOk("设置成功");
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "circle_check_list")]
    public function circleCheckList(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("circle_id");
        if (empty($id)) {
            return self::jsonExpireErr(Utils::ChangeMessage('参数错误', $request));
        }
        $userId = $request->input("search_content");;
        $Circle = Db::table("ext_circle_users")->where("user_id", $temps->name)->where("circle_id", $id)->first();
        if ($Circle && $Circle->role_type == "public") {
            return self::jsonExpireErr(Utils::ChangeMessage('没有权限', $request));
        }
        if (empty($userId)) {

            $list = Db::table("ext_circle_invite")->where("circle_id")->where("type", 1)->where("status", 0)->paginate(15);
            $old = Db::table("ext_circle_invite")->where("circle_id")->where("type", 1)->where("status", 1)->paginate(15);
            $data = [
                "rows" => $list->items(),
                "total" => $list->total(),
                "record" => $old->items(),
            ];
        } else {
            $list = Db::table("ext_circle_invite")->where("user_id", $userId)->orWhere("nickname", $userId)->where("circle_id")->where("type", 1)->where("status", 1)->first();
            $data = [
                "rows" => $list,
                "total" => 1,
                "record" => "",
            ];
        }

        return self::jsonResult($data);
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "circle_check_handle")]
    public function circleCheckHandle(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("circle_id");
        if (empty($id)) {
            return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
        }
        $id = $request->input("id");
        $status = $request->input("status");
        $Circle = Db::table("ext_circle_users")->where("user_id", $temps->name)->where("circle_id", $id)->first();
        if ($Circle && $Circle->role_type == "public") {
            return self::jsonErr(Utils::ChangeMessage('没有权限', $request));
        }
        $has = Db::table("ext_circle_invite")->where("type", 1)->where("status", 0)->where("id", $id)->first();
        if (!$has) {
            return self::jsonErr(Utils::ChangeMessage('请刷新后再试', $request));
        }
        Db::update("update ext_circle_invite set status=? where id=?", [
            $status,
            $id,
        ]);
        if ($status == "1") {
            Db::insert("insert into ext_circle_users(user_id,circle_id,user_nickname,user_avatar_url,join_method,inviter_id,inviter_nickname,join_time,created_at,hand_name) values(?,?,?,?,?,?,?,?,?,?)", [
                $has->user_id,
                $id,
                $has->user_nickname,
                $has->user_avatar,
                "invite",
                $temps->name,
                $temps->nickname,
                time(),
                date("Y-m-d H:i:s", time()),
                $temps->nickname,
            ]);
        }
        return self::jsonOk("操作成功");
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "circle_block_list")]
    public function circleBlockList(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("circle_id");
        if (empty($id)) {
            return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
        }
        $Circle = Db::table("ext_circle_users")->where("user_id", $temps->name)->where("circle_id", $id)->first();
        if ($Circle && $Circle->role_type == "public") {
            return self::jsonExpireErr(Utils::ChangeMessage('没有权限', $request));
        }
        $name = $request->input("search_content");
        if (empty($name)) {
            $list = Db::table("ext_circle_users")->where("circle_id", $id)->paginate(15);
            $data = [
                "rows" => $list->items(),
                "total" => $list->total(),
            ];
        } else {
            $user = Db::table("ext_circle_users")->where("user_nickname", $name)->where("circle_id", $id)->first();
            $data = [
                "rows" => $user,
                "total" => 1,
            ];
        }
        return self::jsonResult($data);
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "circle_block_handle")]
    public function circleBlockHandle(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $circle_id = $request->input("circle_id");
        $id = $request->input("id");
        if (empty($id)) {
            return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
        }
        $Circle = Db::table("ext_circle_users")->where("user_id", $temps->name)->where("circle_id", $circle_id)->first();
        if ($Circle && $Circle->role_type == "public") {
            return self::jsonExpireErr(Utils::ChangeMessage('没有权限', $request));
        }
        $type = $request->input("type");
        $can_view_help = $Circle->can_view_help;
        $can_post_help = $Circle->can_post_help;
        switch ($type):
            case "1":
                $can_view_help = false;
            case "2":
                $can_post_help = false;
            case "3":
                $can_view_help = true;
                $can_post_help = true;
                break;
        endswitch;
        Db::update("update ext_circle_users set can_view_help=?,can_post_help=?,han_name=? where id=?", [
            $can_view_help,
            $can_post_help,
            $temps->name,
            $id
        ]);
        return self::jsonOk("操作成功");
    }


    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "circle_notice_list")]
    public function circleNoticeList(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("circle_id");
        if (empty($id)) {
            return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
        }
        $circle = Db::table("ext_circle_users")->where("user_id", $temps->name)->where("circle_id", $id)->first();
        $notice = Db::table("ext_circle_notice")->where("circle_id", $id)->paginate(15);
        $has = Db::table("ext_circle_read")->where("user_id", $temps->name)->where("circle_id", $id)->where("read_type", 3)->first();
        if ($has) {
            Db::update("update ext_circle_read set updated=? where id=?", [time(), $has->id]);
        } else {
            Db::insert("insert into ext_circle_read(user_id,circle_id,read_type,created,updated) values(?,?,?,?,?)", [
                $temps->name,
                $id,
                3,
                time(),
                time(),
            ]);
        }
        $admin = false;
        if ($circle->role_type != "member") {
            $admin = true;
        }
        $data = [
            "admin" => $admin,
            "rows" => $notice->items(),
            "total" => $notice->total(),
        ];
        return self::jsonResult($data);
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "circle_notice_update")]
    public function circleNoticeUpdate(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $circle_id = $request->input("circle_id");
        $id = $request->input("id");

        $Circle = Db::table("ext_circle_users")->where("user_id", $temps->name)->where("circle_id", $circle_id)->first();
        if ($Circle && $Circle->role_type == "public") {
            return self::jsonExpireErr(Utils::ChangeMessage('没有权限', $request));
        }
        $content = $request->input("content");

        if (empty($id)) {
            Db::insert("insert into ext_circle_notice(user_id,circle_id,user_avatar,user_nickname,created,content) values(?,?,?,?,?,?)", [
                $temps->name,
                $circle_id,
                $temps->avatar_url,
                $temps->nickname,
                time(),
                $content
            ]);
        } else {
            Db::update("update ext_circle_notice set content=? where id=?", [$content, $id]);
        }
        return self::jsonOk("操作成功");
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "circle_notice_delete")]
    public function circleNoticeDelete(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $circle_id = $request->input("circle_id");
        $id = $request->input("id");
        if (empty($circle_id)) {
            return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
        }
        $Circle = Db::table("ext_circle_users")->where("user_id", $temps->name)->where("circle_id", $circle_id)->first();
        if ($Circle && $Circle->role_type == "public") {
            return self::jsonErr(Utils::ChangeMessage('没有权限', $request));
        }

        Db::delete("delete  from ext_circle_notice where id=?", [$id]);
        return self::jsonOk("操作成功");
    }

    //投诉帮办，删除帮办
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "circle_content_report")]
    public function circleContentReport(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $circle_id = $request->input("id");
        if (empty($circle_id)) {
            return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
        }
        $type = $request->input("type");
        if (empty($type)) {
            return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
        }
        $images = $request->input("images");

        $content = $request->input("content");
        Db::insert("insert into circle_content_report(user_id,user_nickname,user_avatar,content,created,circle_id,type,images) values(?,?,?,?,?,?,?,?)", [
            $temps->name,
            $temps->nickname,
            $temps->avatar_url,
            $content,
            time(),
            $circle_id,
            $type,
            $images,
        ]);
        return self::jsonOk("投诉成功");
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "circle_content_delete")]
    public function circleContentDelete(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("id");
        if (empty($id)) {
            return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
        }
        $circleContent = Db::table("ext_circle_content")->where("id", $id)->first();
        $Circle = Db::table("ext_circle_users")->where("user_id", $temps->name)->where("circle_id", $circleContent->circle_id)->first();
        if ($circleContent->user_id != $temps->name || ($Circle && $Circle->role_type == "public")) {
            return self::jsonExpireErr(Utils::ChangeMessage('没有权限', $request));
        }
        Db::delete("delete from ext_circle_content where id=?", [$id]);
        return self::jsonOk("操作成功");
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "circle_content_create")]
    public function circleContentCreate(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }

        $circleId = $request->input("circle_id");
        if (empty($circleId)) {
            return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
        }
        $circles = Db::table("ext_circles")->where("circle_id", $circleId)->where("status", 1)->first();
        if (!$circles) {
            return self::jsonErr(Utils::ChangeMessage('圈子不存在或未通过审核', $request));
        }
        if ($circles->circle_status != "normal") {
            return self::jsonErr(Utils::ChangeMessage('圈子状态异常', $request));
        }
        if ($circles->owner_id != $temps->name && $circles->ban_status == 1) {
            return self::jsonErr(Utils::ChangeMessage('不允许发布帮办', $request));
        }
        //有个问题。 目前是管理在创建，后续需要普通用户。需要进圈。还有权限问题
        if ($circles->owner_id != $temps->name) {
            $userCircles = Db::table("ext_circle_users")->where("user_id", $temps->name)->where("circle_id", $circleId)->first();
            if ($userCircles->role_type == "member" && !$userCircles->can_post_help) {
                return self::jsonErr(Utils::ChangeMessage('不允许发布帮办', $request));
            }
        }
        $content = $request->input("content");
        $url = $request->input("url");
        $city = $request->input("city");
        $score = $request->input("score");
        Db::insert("insert into ext_circle_content(user_id,user_nickname,user_avatar,circle_id,score,url,city,created,content) values(?,?,?,?,?,?,?,?,?)", [
            $temps->name,
            $temps->nickname,
            $temps->avatar_url ?? "",
            $circleId,
            $score,
            $url,
            $city,
            time(),
            $content,
        ]);
        return self::jsonOk("发布成功");
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "circle_mine_list")]
    public function circleContentMine(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        //我加入的圈子（仅已审核通过的 status=1），并且要有发布的权限。
        $userJoin = Db::table("ext_circle_users")->join("ext_circles", "ext_circles.circle_id", "=", "ext_circle_users.circle_id")->where("ext_circle_users.user_id", $temps->name)->where("ext_circle_users.status", 1)->where("ext_circles.status", 1)
            ->select("ext_circle_users.circle_avatar", "ext_circle_users.circle_id", "ext_circles.circle_type", "ext_circles.circle_name", "ext_circle_users.can_post_help")->get()->toArray();
        return self::jsonResult($userJoin);
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "quit")]
    public function circleQuit(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("circle_id");
        if ($id == "") {
            return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
        }

        // $userCircle=Db::table("ext_circle_users")->where("user_id",$temps->name)->where("status",1)->select("circle_avatar","circle_id","member_status","can_view_help","can_post_help")->get();

        Db::delete("delete from ext_circle_users where user_id=? and circle_id=?", [$temps->name, $id]);
        return self::jsonOk("退出成功");
    }
}
