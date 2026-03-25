<?php
declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use App\Caches\UsersCache;
use App\Http\Frontend\Dao\Chats;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Common\Utils;

#[Controller(prefix: "api/v1/chats")]
class ChatsControllers extends FrontendController
{
    /**
     * 获取我创建的聊天室
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "created")]
    #[PostMapping(path: "created")]
    public function created(RequestInterface $request): mixed
    {
        $user = UsersCache::getUserByRequest($request);
        if (empty($user) || empty($user->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        
        $page = (int)$request->input('page', 1);
        $limit = (int)$request->input('limit', 50);
        
        // 限制每页最大数量
        if ($limit > 100) {
            $limit = 100;
        }
        
        $result = Chats::getCreatedRooms($user->name, $page, $limit);
        
        if ($result['error'] !== '') {
            return self::jsonErr(Utils::ChangeMessage($result['error'], $request));
        }
        
        return self::jsonResult([
            'list' => $result['data'],
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit']
        ]);
    }

    /**
     * 获取我管理的聊天（包括群主和管理员）
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "managed")]
    #[PostMapping(path: "managed")]
    public function managed(RequestInterface $request): mixed
    {
        $user = UsersCache::getUserByRequest($request);
        if (empty($user) || empty($user->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        
        $page = (int)$request->input('page', 1);
        $limit = (int)$request->input('limit', 50);
        
        // 限制每页最大数量
        if ($limit > 100) {
            $limit = 100;
        }
        
        $result = Chats::getManagedRooms($user->name, $page, $limit);
        
        if ($result['error'] !== '') {
            return self::jsonErr(Utils::ChangeMessage($result['error'], $request));
        }
        
        return self::jsonResult([
            'list' => $result['data'],
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit']
        ]);
    }

    /**
     * 获取我加入的聊天
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "joined")]
    #[PostMapping(path: "joined")]
    public function joined(RequestInterface $request): mixed
    {
        $user = UsersCache::getUserByRequest($request);
        if (empty($user) || empty($user->name)) {
            return self::jsonExpireErr(Utils::ChangeMessage('无效请求', $request));
        }
        
        $page = (int)$request->input('page', 1);
        $limit = (int)$request->input('limit', 50);
        
        // 限制每页最大数量
        if ($limit > 100) {
            $limit = 100;
        }
        
        $result = Chats::getJoinedRooms($user->name, $page, $limit);
        
        if ($result['error'] !== '') {
            return self::jsonErr(Utils::ChangeMessage($result['error'], $request));
        }
        
        return self::jsonResult([
            'list' => $result['data'],
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit']
        ]);
    }
}
