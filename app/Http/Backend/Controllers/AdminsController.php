<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\AdminDao;
use App\Http\Backend\Validations\AdminValidation;

#[Controller(prefix: "/ht/v1/admins")]
class AdminsController extends BackendController
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
                'mail' => $data['mail'] ?? '',
                'phone' => $data['phone'] ?? '',
                'role_id' => (int)($data['role_id'] ?? 0),
                'status' => (int)($data['status'] ?? 0),
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = AdminValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = AdminDao::list($requestData);
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

            $result = AdminDao::get($requestData);
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
                'mail' => $data['mail'] ?? '',
                'phone' => $data['phone'] ?? '',
                'role_id' => (int)($data['role_id'] ?? 0),
                'status' => (int)($data['status'] ?? 0),
                'password' => $data['password'] ?? '',
                'confirm_password' => $data['confirm_password'] ?? '',
                'remark' => $data['remark'] ?? '',
            ];

            $validationError = AdminValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = AdminDao::create($requestData);
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
                'mail' => $data['mail'] ?? '',
                'phone' => $data['phone'] ?? '',
                'role_id' => (int)($data['role_id'] ?? 0),
                'status' => (int)($data['status'] ?? 0),
                'password' => $data['password'] ?? '',
                'confirm_password' => $data['confirm_password'] ?? '',
                'remark' => $data['remark'] ?? '',
            ];

            $validationError = AdminValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = AdminDao::update($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "changePassword")]
    public function changePassword(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
                'new_password' => $data['new_password'] ?? '',
                'confirm_password' => $data['confirm_password'] ?? '',
            ];

            $validationError = AdminValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = AdminDao::changePassword($requestData);
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

            $validationError = AdminValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = AdminDao::changeStatus($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
