<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\UserAlbumDao;
use App\Http\Backend\Validations\UserAlbumValidation;

#[Controller(prefix: "/ht/v1/userAlbums")]
class UserAlbumsController extends BackendController
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
                'name' => $data['name'] ?? '',
                'type' => (int)($data['type'] ?? 0),
                'status' => (int)($data['status'] ?? 0),
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = UserAlbumValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserAlbumDao::list($requestData);
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

            $validationError = UserAlbumValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserAlbumDao::get($requestData);
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
                'name' => $data['name'] ?? '',
                'cover_url' => $data['cover_url'] ?? '',
                'description' => $data['description'] ?? '',
                'type' => (int)($data['type'] ?? 1),
                'photo_count' => (int)($data['photo_count'] ?? 0),
                'max_photo_count' => (int)($data['max_photo_count'] ?? 100),
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'allow_comment' => (bool)($data['allow_comment'] ?? true),
                'allow_download' => (bool)($data['allow_download'] ?? false),
                'status' => (int)($data['status'] ?? 1),
            ];

            $validationError = UserAlbumValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserAlbumDao::create($requestData);
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
                'name' => $data['name'] ?? '',
                'cover_url' => $data['cover_url'] ?? '',
                'description' => $data['description'] ?? '',
                'type' => (int)($data['type'] ?? 1),
                'photo_count' => (int)($data['photo_count'] ?? 0),
                'max_photo_count' => (int)($data['max_photo_count'] ?? 100),
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'allow_comment' => (bool)($data['allow_comment'] ?? true),
                'allow_download' => (bool)($data['allow_download'] ?? false),
                'status' => (int)($data['status'] ?? 1),
            ];

            $validationError = UserAlbumValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserAlbumDao::update($requestData);
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

            $validationError = UserAlbumValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserAlbumDao::del($requestData);
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

            $validationError = UserAlbumValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserAlbumDao::changeStatus($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
