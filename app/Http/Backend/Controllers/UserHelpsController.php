<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\UserHelpDao;
use App\Http\Backend\Validations\UserHelpValidation;

#[Controller(prefix: "/ht/v1/userHelps")]
class UserHelpsController extends BackendController
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
                'user_id' => $data['user_id'] ?? '',
                'circle_id' => $data['circle_id'] ?? '',
                'city_id' => (int)($data['city_id'] ?? 0),
                'request_status' => $data['request_status'] ?? '',
                'broadcast_enabled' => (bool)($data['broadcast_enabled'] ?? false),
                'credit_requirement_enabled' => (bool)($data['credit_requirement_enabled'] ?? false),
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = UserHelpValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserHelpDao::list($requestData);
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

            $validationError = UserHelpValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserHelpDao::get($requestData);
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

            $mediaUrls = $data['media_urls'] ?? [];
            if (is_string($mediaUrls)) {
                $mediaUrls = json_decode($mediaUrls, true) ?? [];
            }

            $requestData = [
                'user_id' => $data['user_id'] ?? '',
                'user_nickname' => $data['user_nickname'] ?? '',
                'user_vanity_id' => $data['user_vanity_id'] ?? '',
                'content' => $data['content'] ?? '',
                'media_urls' => $mediaUrls,
                'circle_id' => $data['circle_id'] ?? '',
                'circle_name' => $data['circle_name'] ?? '',
                'city_id' => (int)($data['city_id'] ?? 0),
                'city_name' => $data['city_name'] ?? '',
                'broadcast_enabled' => (bool)($data['broadcast_enabled'] ?? false),
                'broadcast_cost' => (int)($data['broadcast_cost'] ?? 5),
                'credit_requirement_enabled' => (bool)($data['credit_requirement_enabled'] ?? false),
                'credit_requirement_value' => (int)($data['credit_requirement_value'] ?? 0),
                'request_status' => $data['request_status'] ?? 'pending',
            ];

            $validationError = UserHelpValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserHelpDao::create($requestData);
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

            $mediaUrls = $data['media_urls'] ?? [];
            if (is_string($mediaUrls)) {
                $mediaUrls = json_decode($mediaUrls, true) ?? [];
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
                'content' => $data['content'] ?? '',
                'media_urls' => $mediaUrls,
                'circle_id' => $data['circle_id'] ?? '',
                'circle_name' => $data['circle_name'] ?? '',
                'city_id' => (int)($data['city_id'] ?? 0),
                'city_name' => $data['city_name'] ?? '',
                'broadcast_enabled' => (bool)($data['broadcast_enabled'] ?? false),
                'broadcast_cost' => (int)($data['broadcast_cost'] ?? 5),
                'credit_requirement_enabled' => (bool)($data['credit_requirement_enabled'] ?? false),
                'credit_requirement_value' => (int)($data['credit_requirement_value'] ?? 0),
                'request_status' => $data['request_status'] ?? '',
            ];

            $validationError = UserHelpValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserHelpDao::update($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "del")]
    public function del(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
            ];

            $validationError = UserHelpValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserHelpDao::del($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "changeStatus")]
    public function changeStatus(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
                'request_status' => $data['request_status'] ?? '',
            ];

            $validationError = UserHelpValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserHelpDao::changeStatus($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "enableBroadcast")]
    public function enableBroadcast(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
                'broadcast_enabled' => (bool)($data['broadcast_enabled'] ?? false),
                'broadcast_cost' => (int)($data['broadcast_cost'] ?? 5),
            ];

            $validationError = UserHelpValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserHelpDao::enableBroadcast($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "setCreditRequirement")]
    public function setCreditRequirement(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
                'credit_requirement_enabled' => (bool)($data['credit_requirement_enabled'] ?? false),
                'credit_requirement_value' => (int)($data['credit_requirement_value'] ?? 0),
            ];

            $validationError = UserHelpValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserHelpDao::setCreditRequirement($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
