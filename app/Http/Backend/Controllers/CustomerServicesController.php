<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\CustomerServiceDao;
use App\Http\Backend\Validations\CustomerServiceValidation;

#[Controller(prefix: "/ht/v1/customerServices")]
class CustomerServicesController extends BackendController
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
                'nickname' => $data['nickname'] ?? '',
                'username' => $data['username'] ?? '',
                'beautiful_id' => $data['beautiful_id'] ?? '',
                'status' => (int)($data['status'] ?? 0),
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = CustomerServiceValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = CustomerServiceDao::list($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "get")]
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

            $validationError = CustomerServiceValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = CustomerServiceDao::get($requestData);
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
                'sort_order' => (int)($data['sort_order'] ?? 1),
                'nickname' => $data['nickname'] ?? '',
                'username' => $data['username'] ?? '',
                'beautiful_id' => $data['beautiful_id'] ?? '',
                'friends_count' => (int)($data['friends_count'] ?? 0),
                'max_friends' => (int)($data['max_friends'] ?? 3000),
                'status' => (int)($data['status'] ?? 1),
            ];

            $validationError = CustomerServiceValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = CustomerServiceDao::create($requestData);
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
                'sort_order' => (int)($data['sort_order'] ?? 1),
                'nickname' => $data['nickname'] ?? '',
                'username' => $data['username'] ?? '',
                'beautiful_id' => $data['beautiful_id'] ?? '',
                'friends_count' => (int)($data['friends_count'] ?? 0),
                'max_friends' => (int)($data['max_friends'] ?? 3000),
                'status' => (int)($data['status'] ?? 1),
            ];

            $validationError = CustomerServiceValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = CustomerServiceDao::update($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "delete")]
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

            $validationError = CustomerServiceValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = CustomerServiceDao::del($requestData);
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
                'status' => (int)($data['status'] ?? 1),
            ];

            $validationError = CustomerServiceValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = CustomerServiceDao::changeStatus($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "changeSort")]
    public function changeSort(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
                'sort_order' => (int)($data['sort_order'] ?? 1),
            ];

            $validationError = CustomerServiceValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = CustomerServiceDao::changeSort($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
