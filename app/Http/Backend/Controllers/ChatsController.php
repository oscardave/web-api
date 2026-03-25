<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\ChatDao;
use App\Http\Backend\Validations\ChatValidation;

#[Controller(prefix: "/ht/v1/chats")]
class ChatsController extends BackendController
{
    #[PostMapping(path: "list")]
    public function list(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'user_id' => trim((string)($data['user_id'] ?? '')),
                'nickname' => trim((string)($data['nickname'] ?? '')),
                'room_name' => trim((string)($data['room_name'] ?? '')),
                'content' => trim((string)($data['content'] ?? '')),
                'contains_url' => $data['contains_url'] ?? '',
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = ChatValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = ChatDao::list($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError('服务器错误');
        }
    }
}
