<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\CircleBadgeDao;
use App\Http\Backend\Validations\CircleBadgeValidation;
use App\Common\Utils;

/**
 * 圈子徽章管理
 */
#[Controller(prefix: "/ht/v1/circleBadges")]
class CircleBadgesController extends BackendController
{
    #[PostMapping(path: "list")]
    public function list(RequestInterface $request)
    {
        try {
            $data = $request->all();
            $requestData = [
                'name' => $data['name'] ?? '',
                'status' => (int)($data['status'] ?? 0),
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $err = CircleBadgeValidation::validateList($requestData);
            if ($err !== '') {
                return $this->responseError($err);
            }

            $result = CircleBadgeDao::list($requestData);
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
            $requestData = ['id' => (int)($data['id'] ?? 0)];

            $err = CircleBadgeValidation::validateDetail($requestData);
            if ($err !== '') {
                return $this->responseError($err);
            }

            $result = CircleBadgeDao::get($requestData);
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
            $requestData = [
                'name' => trim((string)($data['name'] ?? '')),
                'icon_url' => Utils::toStoragePath((string)($data['icon_url'] ?? '')),
                'remark' => (string)($data['remark'] ?? ''),
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'status' => (int)($data['status'] ?? 1),
            ];

            $err = CircleBadgeValidation::validateCreate($requestData);
            if ($err !== '') {
                return $this->responseError($err);
            }

            $result = CircleBadgeDao::create($requestData);
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
            $requestData = [
                'id' => (int)($data['id'] ?? 0),
                'name' => trim((string)($data['name'] ?? '')),
                'icon_url' => Utils::toStoragePath((string)($data['icon_url'] ?? '')),
                'remark' => (string)($data['remark'] ?? ''),
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'status' => (int)($data['status'] ?? 1),
            ];

            $err = CircleBadgeValidation::validateUpdate($requestData);
            if ($err !== '') {
                return $this->responseError($err);
            }

            $result = CircleBadgeDao::update($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
