<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\GroupExtDao;
use App\Http\Backend\Validations\GroupExtValidation;

#[Controller(prefix: "/ht/v1/groupExts")]
class GroupExtsController extends BackendController
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
                'group_name' => trim((string)($data['group_name'] ?? '')),
                'owner_id' => trim((string)($data['owner_id'] ?? '')),
                'owner_nickname' => trim((string)($data['owner_nickname'] ?? '')),
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = GroupExtValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = GroupExtDao::list($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError('服务器错误');
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
                'group_id' => trim((string)($data['group_id'] ?? '')),
                'status' => trim((string)($data['status'] ?? '')),
            ];

            $validationError = GroupExtValidation::validateChangeStatus($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = GroupExtDao::changeStatus($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "dissolve")]
    public function dissolve(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'group_id' => trim((string)($data['group_id'] ?? '')),
            ];

            $validationError = GroupExtValidation::validateDissolve($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = GroupExtDao::dissolve($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "transferOwnership")]
    public function transferOwnership(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'group_id' => trim((string)($data['group_id'] ?? '')),
                'new_owner_id' => trim((string)($data['new_owner_id'] ?? '')),
            ];

            $validationError = GroupExtValidation::validateTransferOwnership($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = GroupExtDao::transferOwnership($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "updateInfo")]
    public function updateInfo(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'group_id' => trim((string)($data['group_id'] ?? '')),
                'group_name' => isset($data['group_name']) ? trim((string)$data['group_name']) : null,
                'avatar_url' => isset($data['avatar_url']) ? trim((string)$data['avatar_url']) : null,
                'description' => isset($data['description']) ? trim((string)$data['description']) : null,
                'max_members' => isset($data['max_members']) ? (int)$data['max_members'] : null,
            ];

            $validationError = GroupExtValidation::validateUpdateInfo($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = GroupExtDao::updateInfo($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "updateRestriction")]
    public function updateRestriction(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'group_id' => trim((string)($data['group_id'] ?? '')),
                'restriction_type' => trim((string)($data['restriction_type'] ?? 'unlimited')),
            ];

            $validationError = GroupExtValidation::validateUpdateRestriction($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = GroupExtDao::updateRestriction($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
