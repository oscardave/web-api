<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\UserExchangeDao;
use App\Http\Backend\Validations\UserExchangeValidation;

#[Controller(prefix: "/ht/v1/userExchanges")]
class UserExchangesController extends BackendController
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
                'nickname' => $data['nickname'] ?? '',
                'exchange_type' => (int)($data['exchange_type'] ?? 0),
                'membership_level' => (int)($data['membership_level'] ?? 0),
                'status' => (int)($data['status'] ?? 0),
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = UserExchangeValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserExchangeDao::list($requestData);
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

            $validationError = UserExchangeValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserExchangeDao::get($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "getUserLevelInfo")]
    public function getUserLevelInfo(RequestInterface $request)
    {
        try {
            $data = $request->all();
            $userId = (int)($data['user_id'] ?? 0);
            if ($userId <= 0) {
                return $this->responseError('用户ID无效');
            }
            $result = UserExchangeDao::getUserLevelInfo($userId);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }
            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "calcLevelUpgradePrice")]
    public function calcLevelUpgradePrice(RequestInterface $request)
    {
        try {
            $data = $request->all();
            $userId = (int)($data['user_id'] ?? 0);
            $targetLevelId = (int)($data['target_level_id'] ?? 0);
            if ($userId <= 0 || $targetLevelId <= 0) {
                return $this->responseError('用户ID和目标等级ID无效');
            }
            $result = UserExchangeDao::calcLevelUpgradePrice($userId, $targetLevelId);
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
                'exchange_type' => (int)($data['exchange_type'] ?? 1),
                'membership_level' => (int)($data['membership_level'] ?? 0),
                'original_price' => (float)($data['original_price'] ?? 0),
            ];

            $validationError = UserExchangeValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserExchangeDao::create($requestData);
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

            $validationError = UserExchangeValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserExchangeDao::del($requestData);
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

            $validationError = UserExchangeValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserExchangeDao::changeStatus($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
