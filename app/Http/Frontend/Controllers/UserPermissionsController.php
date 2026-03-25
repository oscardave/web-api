<?php 
declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Frontend\Dao\UserPermissions as UserPermissionsDao;

#[Controller(prefix: "api/v1/user-permissions")]
class UserPermissionsController extends FrontendController
{
    /**
     * 获取当前用户的拉黑列表
     *
     * @param RequestInterface $request
     * @return object
     */
    #[GetMapping(path: "list")]
    public function list(RequestInterface $request): object
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }
        
        $result = UserPermissionsDao::getBlockList($auth['user_id']);
        
        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }
        
        return self::jsonArray($result['data']);
    }

    /**
     * 拉黑某个用户
     *
     * @param RequestInterface $request
     * @return object
     */
    #[PostMapping(path: "block")]
    public function block(RequestInterface $request): object
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }
        
        $data = $request->all();
        if (!isset($data['target_id'])) {
            return self::jsonErr('Missing key \'target_id\'');
        }
        
        $targetUserId = trim($data['target_id']);
        if (empty($targetUserId)) {
            return self::jsonErr('target_id cannot be empty');
        }
        
        // 不能拉黑自己
        if ($targetUserId === $auth['user_id']) {
            return self::jsonErr('Cannot block yourself');
        }
        
        $remark = $data['remark'] ?? '';
        
        $result = UserPermissionsDao::addBlock($auth['user_id'], $targetUserId, $remark);
        
        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }
        
        return self::jsonArray(['success' => true, 'message' => '拉黑成功', 'data' => $result['data']]);
    }

    /**
     * 解除拉黑某个用户
     *
     * @param RequestInterface $request
     * @return object
     */
    #[PostMapping(path: "unblock")]
    public function unblock(RequestInterface $request): object
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }
        
        $data = $request->all();
        if (!isset($data['target_id'])) {
            return self::jsonErr('Missing key \'target_id\'');
        }
        
        $targetUserId = trim($data['target_id']);
        if (empty($targetUserId)) {
            return self::jsonErr('target_id cannot be empty');
        }
        
        $result = UserPermissionsDao::removeBlock($auth['user_id'], $targetUserId);
        
        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }
        
        return self::jsonArray(['success' => true, 'message' => '解除拉黑成功']);
    }
}

