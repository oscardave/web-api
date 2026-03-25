<?php
declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use App\Caches\UsersCache;
use App\Http\Frontend\Dao\ChatGroups;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Common\Utils;

#[Controller(prefix: "api/v1/chat-groups")]
class ChatGroupsController extends FrontendController
{
    /**
     * 获取分组列表
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "list")]
    #[PostMapping(path: "list")]
    public function list(RequestInterface $request): mixed
    {
        $user = UsersCache::getUserByRequest($request);
        if (empty($user) || empty($user->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        
        $result = ChatGroups::list($user->name);
        
        if ($result['error'] !== '') {
            return self::jsonErr(Utils::ChangeMessage($result['error'], $request));
        }
        
        return self::jsonResult($result['data']);
    }

    /**
     * 创建分组
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "create")]
    public function create(RequestInterface $request): mixed
    {
        $user = UsersCache::getUserByRequest($request);
        if (empty($user) || empty($user->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效的Token信息', $request));
        }
        
        $name = $request->input('name', '');
        $icon = $request->input('icon', '');
        
        if (empty($name)) {
            return self::jsonErr(Utils::ChangeMessage('分组名称不能为空', $request));
        }
        
        $result = ChatGroups::create($user->name, $name, $icon);
        
        if ($result['error'] !== '') {
            return self::jsonErr(Utils::ChangeMessage($result['error'], $request));
        }
        
        return self::jsonResult($result['data']);
    }

    /**
     * 修改分组
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "update")]
    public function update(RequestInterface $request): mixed
    {
        $user = UsersCache::getUserByRequest($request);
        if (empty($user) || empty($user->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        
        $groupId = (int)$request->input('id', 0);
        $name = $request->input('name', null);
        $icon = $request->input('icon', null);
        
        if ($groupId <= 0) {
            return self::jsonErr(Utils::ChangeMessage('分组ID不能为空', $request));
        }
        
        if ($name === null && $icon === null) {
            return self::jsonErr(Utils::ChangeMessage('至少需要提供一个要修改的字段', $request));
        }
        
        $result = ChatGroups::update($user->name, $groupId, $name, $icon);
        
        if ($result['error'] !== '') {
            return self::jsonErr(Utils::ChangeMessage($result['error'], $request));
        }
        
        return self::jsonResult($result['data']);
    }

    /**
     * 删除分组
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "delete")]
    public function delete(RequestInterface $request): mixed
    {
        $user = UsersCache::getUserByRequest($request);
        if (empty($user) || empty($user->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        
        $groupId = (int)$request->input('id', 0);
        
        if ($groupId <= 0) {
            return self::jsonErr(Utils::ChangeMessage('分组ID不能为空', $request));
        }
        
        $result = ChatGroups::delete($user->name, $groupId);
        
        if ($result['error'] !== '') {
            return self::jsonErr(Utils::ChangeMessage($result['error'], $request));
        }
        
        return self::jsonOk('删除成功');
    }

    /**
     * 将聊天添加到分组
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "add-room")]
    public function addRoom(RequestInterface $request): mixed
    {
        $user = UsersCache::getUserByRequest($request);
        if (empty($user) || empty($user->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        
        $groupId = (int)$request->input('group_id', 0);
        $roomId = $request->input('room_id', '');
        
        if ($groupId <= 0) {
            return self::jsonErr(Utils::ChangeMessage('分组ID不能为空', $request));
        }
        
        if (empty($roomId)) {
            return self::jsonErr(Utils::ChangeMessage('房间ID不能为空', $request));
        }
        
        $result = ChatGroups::addRoom($user->name, $groupId, $roomId);
        
        if ($result['error'] !== '') {
            return self::jsonErr(Utils::ChangeMessage($result['error'], $request));
        }
        
        return self::jsonOk('添加成功');
    }

    /**
     * 将聊天从分组中移除
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "remove-room")]
    public function removeRoom(RequestInterface $request): mixed
    {
        $user = UsersCache::getUserByRequest($request);
        if (empty($user) || empty($user->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        
        $groupId = (int)$request->input('group_id', 0);
        $roomId = $request->input('room_id', '');
        
        if ($groupId <= 0) {
            return self::jsonErr(Utils::ChangeMessage('分组ID不能为空', $request));
        }
        
        if (empty($roomId)) {
            return self::jsonErr(Utils::ChangeMessage('房间ID不能为空', $request));
        }
        
        $result = ChatGroups::removeRoom($user->name, $groupId, $roomId);
        
        if ($result['error'] !== '') {
            return self::jsonErr(Utils::ChangeMessage($result['error'], $request));
        }
        
        return self::jsonOk('移除成功');
    }

    /**
     * 获取分组中的聊天列表
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "rooms")]
    #[PostMapping(path: "rooms")]
    public function rooms(RequestInterface $request): mixed
    {
        $user = UsersCache::getUserByRequest($request);
        if (empty($user) || empty($user->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        
        $groupId = (int)$request->input('id', 0);
        
        if ($groupId <= 0) {
            return self::jsonErr(Utils::ChangeMessage('分组ID不能为空', $request));
        }
        
        $result = ChatGroups::listRooms($user->name, $groupId);
        
        if ($result['error'] !== '') {
            return self::jsonErr(Utils::ChangeMessage($result['error'], $request));
        }
        
        return self::jsonResult($result['data']);
    }
}
