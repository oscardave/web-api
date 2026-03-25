<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\UserVanityNumberDao;
use App\Http\Backend\Validations\UserVanityNumberValidation;

#[Controller(prefix: "/ht/v1/userVanityNumbers")]
class UserVanityNumbersController extends BackendController
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
                'user_id' => (int)($data['user_id'] ?? 0),
                'vanity_number' => $data['vanity_number'] ?? '',
                'type_id' => (int)($data['type_id'] ?? 0),
                'status' => (int)($data['status'] ?? 0),
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = UserVanityNumberValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserVanityNumberDao::list($requestData);
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

            $validationError = UserVanityNumberValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserVanityNumberDao::get($requestData);
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
                'user_id' => (int)($data['user_id'] ?? 0),
                'vanity_number' => $data['vanity_number'] ?? '',
                'type_id' => (int)($data['type_id'] ?? 0),
                'is_in_selected_pool' => (bool)($data['is_in_selected_pool'] ?? false),
                'is_frontend_display' => (bool)($data['is_frontend_display'] ?? true),
                'points_paid' => (int)($data['points_paid'] ?? 0),
                'discount_applied' => (float)($data['discount_applied'] ?? 1.00),
                'purchase_method' => $data['purchase_method'] ?? '',
                'purchase_reason' => $data['purchase_reason'] ?? '',
                'status' => (int)($data['status'] ?? 1),
            ];

            $validationError = UserVanityNumberValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserVanityNumberDao::create($requestData);
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
                'user_id' => (int)($data['user_id'] ?? 0),
                'vanity_number' => $data['vanity_number'] ?? '',
                'type_id' => (int)($data['type_id'] ?? 0),
                'is_in_selected_pool' => (bool)($data['is_in_selected_pool'] ?? false),
                'is_frontend_display' => (bool)($data['is_frontend_display'] ?? true),
                'points_paid' => (int)($data['points_paid'] ?? 0),
                'discount_applied' => (float)($data['discount_applied'] ?? 1.00),
                'purchase_method' => $data['purchase_method'] ?? '',
                'purchase_reason' => $data['purchase_reason'] ?? '',
                'status' => (int)($data['status'] ?? 0),
            ];

            $validationError = UserVanityNumberValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserVanityNumberDao::update($requestData);
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

            $validationError = UserVanityNumberValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserVanityNumberDao::del($requestData);
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
                'status' => (int)($data['status'] ?? 0),
            ];

            $validationError = UserVanityNumberValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserVanityNumberDao::changeStatus($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
