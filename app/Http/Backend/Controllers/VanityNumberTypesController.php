<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\VanityNumberTypeDao;
use App\Http\Backend\Validations\VanityNumberTypeValidation;

#[Controller(prefix: "/ht/v1/vanityNumberTypes")]
class VanityNumberTypesController extends BackendController
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
                'type_name' => $data['type_name'] ?? '',
                'status' => (int)($data['status'] ?? 0),
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = VanityNumberTypeValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = VanityNumberTypeDao::list($requestData);
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

            $validationError = VanityNumberTypeValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = VanityNumberTypeDao::get($requestData);
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
                'type_name' => $data['type_name'] ?? '',
                'description' => $data['description'] ?? '',
                'digit_counts' => $data['digit_counts'] ?? '',
                'points_cost' => (int)($data['points_cost'] ?? 0),
                'discount_type' => $data['discount_type'] ?? '',
                'invite_discount_rules' => $data['invite_discount_rules'] ?? '',
                'member_discount_rules' => $data['member_discount_rules'] ?? '',
                'is_hidden_forbidden_sale' => (bool)($data['is_hidden_forbidden_sale'] ?? false),
                'is_frontend_display_default' => (bool)($data['is_frontend_display_default'] ?? true),
                'account_type' => $data['account_type'] ?? '',
                'status' => (int)($data['status'] ?? 1),
            ];

            $validationError = VanityNumberTypeValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = VanityNumberTypeDao::create($requestData);
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
                'type_name' => $data['type_name'] ?? '',
                'description' => $data['description'] ?? '',
                'digit_counts' => $data['digit_counts'] ?? '',
                'points_cost' => (int)($data['points_cost'] ?? 0),
                'discount_type' => $data['discount_type'] ?? '',
                'invite_discount_rules' => $data['invite_discount_rules'] ?? '',
                'member_discount_rules' => $data['member_discount_rules'] ?? '',
                'is_hidden_forbidden_sale' => (bool)($data['is_hidden_forbidden_sale'] ?? false),
                'is_frontend_display_default' => (bool)($data['is_frontend_display_default'] ?? true),
                'account_type' => $data['account_type'] ?? '',
                'status' => (int)($data['status'] ?? 0),
            ];

            $validationError = VanityNumberTypeValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = VanityNumberTypeDao::update($requestData);
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

            $validationError = VanityNumberTypeValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = VanityNumberTypeDao::del($requestData);
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

            $validationError = VanityNumberTypeValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = VanityNumberTypeDao::changeStatus($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
