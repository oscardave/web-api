<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\CircleUserDao;
use App\Http\Backend\Validations\CircleUserValidation;

#[Controller(prefix: "/ht/v1/circleUsers")]
class CircleUsersController extends BackendController
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
                'circle_id' => trim((string)($data['circle_id'] ?? '')),
                'keyword' => trim((string)($data['keyword'] ?? '')),
                'user_nickname' => trim((string)($data['user_nickname'] ?? '')),
                'user_id' => trim((string)($data['user_id'] ?? '')),
                'user_vanity_id' => trim((string)($data['user_vanity_id'] ?? '')),
                'role_type' => $data['role_type'] ?? '',
                'member_status' => $data['member_status'] ?? '',
                'join_method' => $data['join_method'] ?? '',
                'is_over_month_inactive' => (bool)($data['is_over_month_inactive'] ?? false),
                'is_weekly_active' => (bool)($data['is_weekly_active'] ?? false),
                'is_monthly_active' => (bool)($data['is_monthly_active'] ?? false),
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = CircleUserValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = CircleUserDao::list($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError('服务器错误');
        }
    }
}
