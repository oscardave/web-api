<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\UserLevelDao;
use App\Http\Backend\Validations\UserLevelValidation;
use App\Common\Utils;

#[Controller(prefix: "/ht/v1/userLevels")]
class UserLevelsController extends BackendController
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
                'status' => (int)($data['status'] ?? 0),
                'only_exchangeable' => !empty($data['only_exchangeable']),
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = UserLevelValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserLevelDao::list($requestData);
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

            $validationError = UserLevelValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserLevelDao::get($requestData);
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
                'avatar_url' => Utils::toStoragePath((string)($data['avatar_url'] ?? '')),
                'remark' => $data['remark'] ?? '',
                'description' => $data['description'] ?? '',
                'validity_period' => (int)($data['validity_period'] ?? 0),
                'payment_point' => (int)($data['payment_point'] ?? 0),
                'translation_enabled' => (bool)($data['translation_enabled'] ?? false),
                'max_groups_joined' => (int)($data['max_groups_joined'] ?? 0),
                'max_groups_created' => (int)($data['max_groups_created'] ?? 0),
                'max_members_in_group' => (int)($data['max_members_in_group'] ?? 0),
                'max_assisted_city' => (int)($data['max_assisted_city'] ?? 0),
                'max_notes_created' => (int)($data['max_notes_created'] ?? 0),
                'max_notes_per_wall' => (int)($data['max_notes_per_wall'] ?? 0),
                'max_super_note_wall' => (int)($data['max_super_note_wall'] ?? 0),
                'friend_limit' => (int)($data['friend_limit'] ?? 100),
                'avatar_frame_enabled' => (bool)($data['avatar_frame_enabled'] ?? false),
                'is_exchangeable' => (bool)($data['is_exchangeable'] ?? false),
                'sort' => (int)($data['sort'] ?? 0),
                'status' => (int)($data['status'] ?? 1),
            ];

            $validationError = UserLevelValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserLevelDao::create($requestData);
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
                'avatar_url' => Utils::toStoragePath((string)($data['avatar_url'] ?? '')),
                'remark' => $data['remark'] ?? '',
                'description' => $data['description'] ?? '',
                'validity_period' => (int)($data['validity_period'] ?? 0),
                'payment_point' => (int)($data['payment_point'] ?? 0),
                'translation_enabled' => (bool)($data['translation_enabled'] ?? false),
                'max_groups_joined' => (int)($data['max_groups_joined'] ?? 0),
                'max_groups_created' => (int)($data['max_groups_created'] ?? 0),
                'max_members_in_group' => (int)($data['max_members_in_group'] ?? 0),
                'max_assisted_city' => (int)($data['max_assisted_city'] ?? 0),
                'max_notes_created' => (int)($data['max_notes_created'] ?? 0),
                'max_notes_per_wall' => (int)($data['max_notes_per_wall'] ?? 0),
                'max_super_note_wall' => (int)($data['max_super_note_wall'] ?? 0),
                'friend_limit' => (int)($data['friend_limit'] ?? 100),
                'avatar_frame_enabled' => (bool)($data['avatar_frame_enabled'] ?? false),
                'is_exchangeable' => (bool)($data['is_exchangeable'] ?? false),
                'sort' => (int)($data['sort'] ?? 0),
                'status' => (int)($data['status'] ?? 1),
            ];

            $validationError = UserLevelValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserLevelDao::update($requestData);
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

            $validationError = UserLevelValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserLevelDao::del($requestData);
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

            $validationError = UserLevelValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserLevelDao::changeStatus($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
