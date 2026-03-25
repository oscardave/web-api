<?php

declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use App\Caches\ConfigsCache;
use App\Caches\VipCache;
use App\Http\Frontend\Dao\Combo;
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

#[Controller(prefix: "api/v1/notes")]
class NotesController extends FrontendController
{
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "create")]
    public function createNote(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $data = $request->all();
        $title = trim($data["title"] ?? "");
        $content = trim($data["content"] ?? "");
        $note_avatar = trim($data["note_avatar"] ?? "");
        $images = trim($data["images"] ?? "");
        $verify_video = trim($data["verify_video"] ?? "");
        $groups = trim($data["groups"] ?? "");
        $city = trim($data["city"] ?? "");
        $remark = trim($data["remark"] ?? "");
        $remark_friends = trim($data["remark_friends"] ?? "");
        $type = trim($data["type"] ?? "");
        $isSuper = $data["is_super"] ?? "";
        $id = trim($data["id"] ?? "");

        // 禁止创建笔记：若 ext_users.note_creation_prohibited = true，则禁止新建（type!=2），但允许编辑已有笔记（type=2）
        $extUser = null;
        try {
            $extUser = Db::table('ext_users')
                ->select('note_creation_prohibited', 'member_level_id')
                ->where('user_id', $temps->name)
                ->first();
            if ($extUser && $extUser->note_creation_prohibited) {
                // type=2 表示编辑已有笔记，其余情况视为创建新笔记
                if ((string)$type !== '2') {
                    return self::jsonErr(Utils::ChangeMessage('当前账号已被禁止创建笔记', $request));
                }
            }
        } catch (\Throwable $e) {
            // 查询异常时不影响主流程，按未禁止处理
        }

        $noteType = 1;
        $videos = $data["videos"] ?? "";
        if ($type == 2) {
            if ($id == "") {
                return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
            }
            $note = Db::table("ext_user_notes")->where("id", $id)->first();
            if (!$note) {
                return self::jsonErr(Utils::ChangeMessage('笔记不存在', $request));
            }
            if ($note->user_id != $temps->name) {
                return self::jsonErr(Utils::ChangeMessage('没有权限修改', $request));
            }
            Db::update("update ext_user_notes set title=?,content=?,note_avatar=?,images=?,verify_video=?,groups=?,city=?,remark=?,remark_friends=?,videos=?  where id=?", [
                $title,
                $content,
                $note_avatar,
                $images,
                $verify_video,
                $groups,
                $city,
                $remark,
                $remark_friends,
                $videos,
                $id,
            ]);
        } else {
            if ($type == 1) {
                $noteType = 0;
            }
            if ($type == 3 && $id == "") {
                return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
            }
            if ($type == 3) {
                $note = Db::table("ext_user_notes")->where("id", $id)->first();
                if (!$note) {
                    return self::jsonErr(Utils::ChangeMessage('参数错误', $request));
                }
                $remark = "";
                $remark_friends = "";
            }
            $setting = Db::table("ext_user_extends")->where("user_id", $temps->name)->first();
            if (!$setting) {
                Db::insert("insert into ext_user_extends(user_id,note_count_limit,super_seat_note_limit) values(?,?,?) ", [
                    $temps->name,
                    10,
                    10,
                ]);
                $setting = Db::table("ext_user_extends")->where("user_id", $temps->name)->first();
            }

            // 按会员等级（ext_user_levels）校验：最大创建笔记、每墙最大笔记（-1 表示不限制）
            $levelRow = null;
            $levelId = $extUser ? (int)($extUser->member_level_id ?? 0) : 0;
            if ($levelId > 0) {
                $levelRow = Db::table('ext_user_levels')
                    ->select('max_notes_created', 'max_notes_per_wall')
                    ->where('id', $levelId)
                    ->where('status', 1)
                    ->first();
            }
            $maxNotesCreated = $levelRow ? (int)($levelRow->max_notes_created ?? -1) : -1;
            $maxNotesPerWall = $levelRow ? (int)($levelRow->max_notes_per_wall ?? -1) : -1;

            if ($isSuper == "0") {
                $num = Db::table("ext_user_notes")->where("type", 0)->where("status", 1)->where("user_id", $temps->name)->count();
                if ($maxNotesCreated >= 0 && $num + 1 > $maxNotesCreated) {
                    return self::jsonErr(Utils::ChangeMessage('笔记数量超出限制，请升级', $request));
                }
            } else {
                $num = Db::table("ext_user_notes")->where("type", 1)->where("status", 1)->where("user_id", $temps->name)->count();
                if ($maxNotesCreated >= 0 && $num + 1 > $maxNotesCreated) {
                    return self::jsonErr(Utils::ChangeMessage('超级笔记数量超出限制，请升级', $request));
                }
            }

            // 每墙最大笔记数：若本条笔记带有 groups（墙），校验该墙下笔记数
            if ($maxNotesPerWall >= 0 && $groups !== '') {
                $wallCount = Db::table("ext_user_notes")
                    ->where("user_id", $temps->name)
                    ->where("status", 1)
                    ->where("groups", $groups)
                    ->count();
                if ($wallCount + 1 > $maxNotesPerWall) {
                    return self::jsonErr(Utils::ChangeMessage('本墙笔记数量超出限制，请升级', $request));
                }
            }
            Db::insert("insert into ext_user_notes(user_id,user_nickname,user_avatar,created,type,status,title,content,note_avatar,verify_video,groups,city,remark,remark_friends,images,videos) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", [
                $temps->name,
                $temps->nickanme ?? "",
                $temps->avatar_url ?? "",
                time(),
                $isSuper,
                $noteType,
                $title,
                $content,
                $note_avatar,
                $verify_video,
                $groups,
                $city,
                $remark,
                $remark_friends,
                $images,
                $videos,
            ]);
            if ($isSuper == "0") {
                Db::update("update ext_user_extends set current_note_count=current_note_count+1 where id=?", [
                    $setting->id,
                ]);
            } else {
                Db::update("update ext_user_extends set current_super_seat_note_count=current_super_seat_note_count+1 where id=?", [
                    $setting->id,
                ]);
            }
        }

        return self::jsonOk("操作成功");
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
        $list = Db::table('ext_user_notes')
            ->where('user_id', $temps->name)
            ->orderBy('pin', 'desc')          // 先置顶的
            ->orderBy('pin_time', 'desc') // 置顶的再按时间
            ->orderBy('id', 'desc')           // 其他按id倒序
            ->paginate(15);
        $group = Db::table("ext_user_notes_group")->where("user_id", $temps->name)->first();
        $data = [
            "rows" => $list->items(),
            "total" => $list->total(),
            "group" => $group ? explode(",", (string)$group->groups) : []
        ];
        return self::jsonResult($data);
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "group")]
    public function group(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $group = $request->input("group");
        $result = Db::table('ext_user_notes')->where("user_id", $temps->name)->where("groups", $group)->paginate(15);
        $data = [
            "rows" => $result->items(),
            "total" => $result->total(),
        ];
        return self::jsonResult($data);
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "search")]
    public function search(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $searchContent = $request->input("search_content");
        $result = Db::table('ext_user_notes')->where("user_id", $temps->name)
            ->where(function ($query) use ($searchContent) {
                $query->where('content', 'like', "%{$searchContent}%")
                    ->orWhere('title', 'like', "%{$searchContent}%")
                    ->orWhere('remark', 'like', "%{$searchContent}%")
                    ->orWhere('city', 'like', "%{$searchContent}%");
            })->get()->toArray();
        $data = [
            "rows" => $result,
            "total" => count($result)
        ];
        return self::jsonResult($data);
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "pin")]
    public function pin(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("id");
        $note = Db::table("ext_user_notes")->where("id", $id)->first();
        if (!$note) {
            return self::jsonErr(Utils::ChangeMessage('笔记不存在', $request));
        }
        if ($note->user_id != $temps->name) {
            return self::jsonErr(Utils::ChangeMessage('没有权限', $request));
        }
        $pin = 1;
        $pinTime = time();
        if ($note->pin == 1) {
            $pin = 0;
            $pinTime = 0;
        }
        Db::update("update ext_user_notes set pin=?,pin_time=? where id=?", [
            $pin,
            $pinTime,
            $id,
        ]);
        return self::jsonOk("操作成功");
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "update")]
    public function updateNote(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("id");
        $note = Db::table("ext_user_notes")->where("id", $id)->first();
        if (!$note) {
            return self::jsonErr(Utils::ChangeMessage('笔记不存在', $request));
        }
        if ($note->user_id != $temps->name) {
            return self::jsonErr(Utils::ChangeMessage('没有权限', $request));
        }
        $data = [
            "rows" => (array)$note,
        ];
        return self::jsonResult($data);
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "remark")]
    public function remark(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("id");
        $note = Db::table("ext_user_notes")->where("id", $id)->first();
        if (!$note) {
            return self::jsonErr(Utils::ChangeMessage('笔记不存在', $request));
        }
        if ($note->user_id != $temps->name) {
            return self::jsonErr(Utils::ChangeMessage('没有权限', $request));
        }
        $remark = $request->input("remark");

        Db::update("update ext_user_notes set remark=? where id=?", [
            $remark,
            $id,
        ]);
        return self::jsonOk("操作成功");
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "remark_friend")]
    public function remarkFriend(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("id");
        $note = Db::table("ext_user_notes")->where("id", $id)->first();
        if (!$note) {
            return self::jsonErr(Utils::ChangeMessage('笔记不存在', $request));
        }
        if ($note->user_id != $temps->name) {
            return self::jsonErr(Utils::ChangeMessage('没有权限', $request));
        }
        $remark_friend = $request->input("remark_friend");
        Db::update("update ext_user_notes set remark_friend=? where id=?", [
            $remark_friend,
            $id,
        ]);
        return self::jsonOk("操作成功");
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "delete")]
    public function deleteNote(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("id");

        $idList = explode(",", $id);
        foreach ($idList as $v) {
            $note = Db::table("ext_user_notes")->where("id", $v)->first();
            if (!$note) {
                continue;
            }
            if ($note->user_id != $temps->name) {
                return self::jsonErr(Utils::ChangeMessage('没有权限', $request));
            }
            Db::delete("delete  from ext_user_notes where id=?", [
                $v
            ]);
        }
        return self::jsonOk("操作成功");
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "move_group")]
    public function moveGroup(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("id");
        $idList = explode(",", $id);
        $group = $request->input("group");
        foreach ($idList as $v) {
            $note = Db::table("ext_user_notes")->where("id", $v)->first();
            if (!$note) {
                continue;
            }
            if ($note->user_id != $temps->name) {
                return self::jsonErr(Utils::ChangeMessage('没有权限', $request));
            }
            Db::update("update ext_user_notes set groups=? where id=?", [
                $group,
                $v
            ]);
        }
        return self::jsonOk("操作成功");
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "group_list")]
    public function groupList(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $res = Db::table("ext_user_notes_group")->where("user_id", $temps->name)->get()->toArray();
        $data = [
            "rows" => $res,
            "total" => count($res),
        ];
        return self::jsonResult($data);
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "group_update")]
    public function groupUpdate(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("id");
        $name = $request->input("name");
        $type = $request->input("type");
        if ($type == 1) {
            // 按会员等级校验：超级笔记墙数量上限（max_super_note_wall，-1 表示不限制）
            $extUser = Db::table('ext_users')->select('member_level_id')->where('user_id', $temps->name)->first();
            $levelId = $extUser ? (int)($extUser->member_level_id ?? 0) : 0;
            $maxSuperNoteWall = -1;
            if ($levelId > 0) {
                $levelRow = Db::table('ext_user_levels')
                    ->select('max_super_note_wall')
                    ->where('id', $levelId)
                    ->where('status', 1)
                    ->first();
                $maxSuperNoteWall = $levelRow ? (int)($levelRow->max_super_note_wall ?? -1) : -1;
            }
            if ($maxSuperNoteWall >= 0) {
                $currentWalls = Db::table('ext_user_notes_group')->where('user_id', $temps->name)->count();
                if ($currentWalls + 1 > $maxSuperNoteWall) {
                    return self::jsonErr(Utils::ChangeMessage('超级笔记墙数量超出限制，请升级', $request));
                }
            }
            Db::insert("insert into ext_user_notes_group(user_id,groups,created) values(?,?,?)", [
                $temps->name,
                trim($name),
                time(),
            ]);
        } elseif ($type == 2) {
            Db::update("update ext_user_notes_group set groups=? where id=?", [
                $name,
                $id,
            ]);
        } else {
            Db::delete("delete  from ext_user_notes_group where id=?", [
                $id
            ]);
        }

        return self::jsonOk("操作成功");
    }


    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "detail")]
    public function detail(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("id");
        $res = Db::table("ext_user_notes")->where("id", $id)->first();
        if (!$res) {
            return self::jsonErr(Utils::ChangeMessage('笔记不存在', $request));
        }
        if ($temps->name != $res->user_id) {
            $res->remark = '';
            $res->remark_friend = '';
        }
        $now = time();
        $has = Db::table("ext_user_permissions")->where("user_id", $temps->name)->where("target_id", $res->user_id)->first();
        if ($has) {
            Db::insert("insert into ext_notes_read(user_id,notes_id,created,updated) values(?,?,?,?)", [
                $temps->name,
                $res->id,
                $now,
                $now,
            ]);
        }
        return self::jsonResult((array)$res);
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "super")]
    public function superNote(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $searchContent = $request->input("search_content");
        $list = Db::table("ext_user_notes")->where(function ($query) use ($searchContent) {
            if ($searchContent != "") {
                $query->where('content', 'like', "%{$searchContent}%")
                    ->orWhere('title', 'like', "%{$searchContent}%")
                    ->orWhere('remark', 'like', "%{$searchContent}%")
                    ->orWhere('city', 'like', "%{$searchContent}%");
            }
        })->where("user_id", $temps->name)->where("type", 1)->where("status", 1)->paginate(15);
        $data = $list->items();
        $today = Db::table("ext_user_notes_log")->where("day", date("Y-m-d", time()))->where("user_id", $temps->name)->get();
        $tempList = [];
        $total = 0;
        foreach ($today as $v) {
            $tempList[$v->notes_id] = $v->count;
            $total += $v->count;
        }
        $setting = Db::table("ext_user_extends")->where("user_id", $temps->name)->first();
        $tempSetting = $setting
            ? [0 => $setting->current_note_count . "/" . $setting->note_count_limit, 1 => $setting->current_super_seat_note_count . "/" . $setting->super_seat_note_limit]
            : [0 => "0/0", 1 => "0/0"];
        foreach ($data as $v) {
            $v->today_view_count = $tempList[$v->id] ?? 0;
            $v->note_num = $tempSetting[$v->type];
        }
        return self::jsonResult(
            ["rows" => $data, "total" => $list->total(), "total_view_count" => $total]
        );
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "remove")]
    public function removeNote(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("id");
        $note = Db::table("ext_user_notes")->where("id", $id)->first();
        if (!$note) {
            return self::jsonErr(Utils::ChangeMessage('笔记不存在', $request));
        }
        if ($note->user_id != $temps->name) {
            return self::jsonErr(Utils::ChangeMessage('没有权限', $request));
        }
        Db::update("update ext_user_notes set status=2 where id=?", [
            $id
        ]);
        return self::jsonOk("操作成功");
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "label_update")]
    public function label(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $id = $request->input("id");
        $note = Db::table("ext_user_notes")->where("id", $id)->first();
        if (!$note) {
            return self::jsonErr(Utils::ChangeMessage('笔记不存在', $request));
        }
        if ($note->user_id != $temps->name) {
            return self::jsonErr(Utils::ChangeMessage('没有权限', $request));
        }
        $label = $request->input("label");
        if ($note->label != "") {
            $label = $note->label . "," . $label;
        }
        Db::update("update ext_user_notes set label=? where id=?", [$label, $id]);
        return self::jsonOk("操作成功");
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "label_list")]
    public function labelList(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $res = Db::table("ext_user_notes_label")->where("user_id", $temps->name)->Get()->toArray();
        return self::jsonResult(
            ["rows" => $res, "total" => count($res)]
        );
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "label_manage")]
    public function labelManage(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }

        $id = $request->input("id");
        $label = $request->input("label");
        $type = $request->input("type");
        if ($type == 1 || $type == 2) {
            if ($id == "") {
                Db::insert("insert into ext_user_notes_label(user_id,label,created) values(?,?,?)", [
                    $temps->name,
                    $label,
                    time(),
                ]);
            } else {
                $note = Db::table("ext_user_notes")->where("id", $id)->first();
                if (!$note) {
                    return self::jsonErr(Utils::ChangeMessage('笔记不存在', $request));
                }
                if ($note->user_id != $temps->name) {
                    return self::jsonErr(Utils::ChangeMessage('没有权限', $request));
                }
                Db::update("update ext_user_notes_label set label=? where id=?", [
                    $label,
                    $id,
                ]);
            }
        } else {
            if ($id != "") {
                $note = Db::table("ext_user_notes")->where("id", $id)->first();
                if (!$note) {
                    return self::jsonErr(Utils::ChangeMessage('笔记不存在', $request));
                }
                if ($note->user_id != $temps->name) {
                    return self::jsonErr(Utils::ChangeMessage('没有权限', $request));
                }
                Db::delete("delete  from ext_user_notes_label where id=?", [$id]);
            }
        }
        return self::jsonOk("操作成功");
    }

    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "super_manage")]
    public function superManage(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr('无效请求');
        }
        $searchContent = $request->input("search_content");
        $list = Db::table("ext_user_notes")->where(function ($query) use ($searchContent) {
            if ($searchContent != "") {
                $query->where('content', 'like', "%{$searchContent}%")
                    ->orWhere('title', 'like', "%{$searchContent}%")
                    ->orWhere('remark', 'like', "%{$searchContent}%")
                    ->orWhere('city', 'like', "%{$searchContent}%");
            }
        })->where("user_id", $temps->name)->where("type", 1)->where("status", 1)->paginate(15);
        $data = $list->items();

        return self::jsonResult(
            ["rows" => $data, "total" => $list->total()]
        );
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "wall")]
    public function wallNote(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $searchContent = $request->input("search_content");

        $type = $request->input("type");

        $cond = [];
        if ($searchContent != "") {
            array_push($cond, ["title", "like", "'%" . $searchContent . "%'"]);
        }

        $res = Db::table("ext_user_permissions")->where("user_id", $temps->name)->where("note_access", true)->get();
        $friendNum = count($res);
        $friendList = [];
        foreach ($res as $v) {
            array_push($friendList, $v->target_id);
        }

        $unReadNum = 0;
        $list = Db::table("ext_user_notes")->where($cond)->whereIn("user_id", $friendList)->orderBy("id", "desc")->get();
        $friendTotalNoteNum = Db::table("ext_user_notes")->whereIn("user_id", $friendList)->count();
        foreach ($list as $v) {
            $has = Db::table("ext_notes_read")->where("notes_id", $v->id)->where("user_id", $temps->name)->orderBy("id", "desc")->first();
            $v->unRead = false;
            if (!$has) {
                $v->unRead = true;
                $unReadNum++;
            }
        }
        $friendInfoList = [];
        if ($type == 2) {
            foreach ($friendList as $v) {
                $tempList = [];
                $temps = Db::table("ext_user_notes")->where("user_id", $v)->get();
                $unReadNum = 0;
                foreach ($temps as $k => $val) {
                    if ($k == 0) {
                        $tempList["user_id"] = $v;
                        $tempList["user_avatar"] = $val->user_avatar;
                        $tempList["user_nickname"] = $val->user_nickname;
                        $tempList["note_num"] = count($temps);
                    }

                    $has = Db::table("ext_notes_read")->where("notes_id", $val->id)->where("user_id", $v)->first();
                    if (!$has) {
                        $unReadNum++;
                    }
                }
                $tempList["un_read_num"] = $unReadNum;
                $user = Db::table("users")->where("name", $v)->select("score")->first();
                $tempList["user_score"] = $user->score ?? 0;
                if ($searchContent != "") {
                    if (stripos($tempList["user_nickname"], $searchContent) !== false) {
                        array_push($friendInfoList, $tempList);
                    }
                } else {
                    array_push($friendInfoList, $tempList);
                }
            }
        }

        $data = ["rows" => $list, "friend_num" => $friendNum, "un_read_num" => $unReadNum, "friend_total_note_num" => $friendTotalNoteNum, "friend_list" => $friendInfoList];
        return self::jsonResult($data);
    }
    /**
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "wall/detail")]
    public function wallNoteDetail(RequestInterface $request): mixed
    {
        $temps = UsersCache::getUserByRequest($request);
        if (empty($temps->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        $userID = $request->input("user_id");
        $searchContent = $request->input("search_content");
        $group = $request->input("group");
        $cond = [];
        if ($searchContent != "") {
            array_push($cond, ["title", "like", "'%" . $searchContent . "%'"]);
        }

        if ($group != "") {
            array_push($cond, ["group", "=", $group]);
        }
        $temps = Db::table("ext_user_notes")->where($cond)->where("user_id", $userID)->paginate(15);
        $group = Db::table("ext_user_notes_group")->where("user_id", $userID)->get();
        $data = [
            "rows" => $temps->items(),
            "total" => $temps->total(),
            "group" => $group,
        ];
        return self::jsonResult($data);
    }
}
