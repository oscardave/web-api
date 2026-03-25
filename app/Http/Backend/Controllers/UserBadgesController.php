<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\UserBadgeDao;
use App\Http\Backend\Validations\UserBadgeValidation;
use App\Common\Utils;

#[Controller(prefix: "/ht/v1/userBadges")]
class UserBadgesController extends BackendController
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
                'name' => $data['name'] ?? '',
                'type' => (int)($data['type'] ?? 0),
                'status' => (int)($data['status'] ?? 0),
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = UserBadgeValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserBadgeDao::list($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "detail")]
    public function get(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
            ];

            $validationError = UserBadgeValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserBadgeDao::get($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "create")]
    public function create(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'name' => $data['name'] ?? '',
                'type' => (int)($data['type'] ?? 0),
                'icon_url' => Utils::toStoragePath((string)($data['icon_url'] ?? '')),
                'points_exchange_cost' => (int)($data['points_exchange_cost'] ?? 0),
                'membership_level_required' => (int)($data['membership_level_required'] ?? 0),
                'integrity_score_required' => (int)($data['integrity_score_required'] ?? 0),
                'invited_users_required' => (int)($data['invited_users_required'] ?? 0),
                'monthly_help_posts_required' => (int)($data['monthly_help_posts_required'] ?? 0),
                'is_exchangeable' => (bool)($data['is_exchangeable'] ?? false),
                'description' => $data['description'] ?? '',
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'status' => (int)($data['status'] ?? 1),
            ];

            $validationError = UserBadgeValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserBadgeDao::create($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "update")]
    public function update(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
                'name' => $data['name'] ?? '',
                'type' => (int)($data['type'] ?? 0),
                'icon_url' => Utils::toStoragePath((string)($data['icon_url'] ?? '')),
                'points_exchange_cost' => (int)($data['points_exchange_cost'] ?? 0),
                'membership_level_required' => (int)($data['membership_level_required'] ?? 0),
                'integrity_score_required' => (int)($data['integrity_score_required'] ?? 0),
                'invited_users_required' => (int)($data['invited_users_required'] ?? 0),
                'monthly_help_posts_required' => (int)($data['monthly_help_posts_required'] ?? 0),
                'is_exchangeable' => (bool)($data['is_exchangeable'] ?? false),
                'description' => $data['description'] ?? '',
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'status' => (int)($data['status'] ?? 1),
            ];

            $validationError = UserBadgeValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserBadgeDao::update($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "delete")]
    public function delete(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }
            $requestData = ['id' => (int)($data['id'] ?? 0)];
            if ($requestData['id'] <= 0) {
                return $this->responseError('徽章ID无效');
            }
            $result = UserBadgeDao::delete($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }
            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
