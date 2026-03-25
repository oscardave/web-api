<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\UserAccountDao;
use App\Http\Backend\Validations\UserAccountValidation;
use App\Service\AccountChangeNotifyService;

#[Controller(prefix: "/ht/v1/userAccounts")]
class UserAccountsController extends BackendController
{
    public function __construct(protected AccountChangeNotifyService $notifyService)
    {
    }
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
                'nickname' => trim((string)($data['nickname'] ?? '')),
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = UserAccountValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserAccountDao::list($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "detail")]
    public function detail(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $id = (int)($data['id'] ?? 0);
            $userId = (int)($data['user_id'] ?? 0);

            if ($id > 0) {
                $account = UserAccountDao::getById($id);
            } elseif ($userId > 0) {
                $account = UserAccountDao::getByUserId($userId);
            } else {
                return $this->responseError('请提供 id 或 user_id');
            }

            if (!$account) {
                return $this->responseError('用户账户不存在');
            }

            return $this->responseData($account);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "addBalance")]
    public function addBalance(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'user_id' => (int)($data['user_id'] ?? 0),
                'change_type' => (int)($data['change_type'] ?? 1),
                'amount' => (int)($data['amount'] ?? 0),
                'description' => trim((string)($data['description'] ?? '')),
                'source_id' => trim((string)($data['source_id'] ?? '')),
            ];

            if ($requestData['user_id'] <= 0 || $requestData['amount'] <= 0) {
                return $this->responseError('用户ID和金额必填且金额大于0');
            }
            if (!in_array($requestData['change_type'], [1, 2], true)) {
                return $this->responseError('变动类型：1-积分 2-诚信保');
            }

            $result = UserAccountDao::addBalance($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }
            $this->notifyService->notify($requestData['user_id'], ['change_type' => $requestData['change_type']]);

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "deductBalance")]
    public function deductBalance(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'user_id' => (int)($data['user_id'] ?? 0),
                'change_type' => (int)($data['change_type'] ?? 1),
                'amount' => (int)($data['amount'] ?? 0),
                'description' => trim((string)($data['description'] ?? '')),
                'source_id' => trim((string)($data['source_id'] ?? '')),
            ];

            if ($requestData['user_id'] <= 0 || $requestData['amount'] <= 0) {
                return $this->responseError('用户ID和金额必填且金额大于0');
            }
            if (!in_array($requestData['change_type'], [1, 2], true)) {
                return $this->responseError('变动类型：1-积分 2-诚信保');
            }

            $result = UserAccountDao::deductBalance($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }
            $this->notifyService->notify($requestData['user_id'], ['change_type' => $requestData['change_type']]);

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
