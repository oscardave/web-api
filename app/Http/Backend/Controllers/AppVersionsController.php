<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\AppVersionDao;
use App\Http\Backend\Validations\AppVersionValidation;

#[Controller(prefix: "/ht/v1/appVersions")]
class AppVersionsController extends BackendController
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
                'client_type' => (int)($data['client_type'] ?? 0),
                'release_type' => (int)($data['release_type'] ?? 0),
                'version_number' => $data['version_number'] ?? '',
                'upgrade_type' => (int)($data['upgrade_type'] ?? 0),
                'version_status' => (int)($data['version_status'] ?? 0),
                'min_compatible_version' => $data['min_compatible_version'] ?? '',
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = AppVersionValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = AppVersionDao::list($requestData);
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

            $validationError = AppVersionValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = AppVersionDao::get($requestData);
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
                'client_type' => (int)($data['client_type'] ?? 1),
                'release_type' => (int)($data['release_type'] ?? 1),
                'appstore_url' => $data['appstore_url'] ?? '',
                'version_number' => $data['version_number'] ?? '',
                'release_time' => $data['release_time'] ?? '',
                'upgrade_type' => (int)($data['upgrade_type'] ?? 1),
                'upgrade_message' => $data['upgrade_message'] ?? '',
                'min_compatible_version' => $data['min_compatible_version'] ?? '',
                'version_status' => (int)($data['version_status'] ?? 1),
            ];

            $validationError = AppVersionValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = AppVersionDao::create($requestData);
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
                'client_type' => (int)($data['client_type'] ?? 1),
                'release_type' => (int)($data['release_type'] ?? 1),
                'appstore_url' => $data['appstore_url'] ?? '',
                'version_number' => $data['version_number'] ?? '',
                'release_time' => $data['release_time'] ?? '',
                'upgrade_type' => (int)($data['upgrade_type'] ?? 1),
                'upgrade_message' => $data['upgrade_message'] ?? '',
                'min_compatible_version' => $data['min_compatible_version'] ?? '',
                'version_status' => (int)($data['version_status'] ?? 1),
            ];

            $validationError = AppVersionValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = AppVersionDao::update($requestData);
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

            $validationError = AppVersionValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = AppVersionDao::del($requestData);
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
                'version_status' => (int)($data['version_status'] ?? 1),
            ];

            $validationError = AppVersionValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = AppVersionDao::changeStatus($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
