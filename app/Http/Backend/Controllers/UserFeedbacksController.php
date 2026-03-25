<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\UserFeedbackDao;
use App\Http\Backend\Validations\UserFeedbackValidation;

#[Controller(prefix: "/ht/v1/userFeedbacks")]
class UserFeedbacksController extends BackendController
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
                'type' => (int)($data['type'] ?? 0),
                'content' => $data['content'] ?? '',
                'user_id' => (int)($data['user_id'] ?? 0),
                'target_type' => $data['target_type'] ?? '',
                'target_id' => (int)($data['target_id'] ?? 0),
                'source_tag' => $data['source_tag'] ?? '',
                'status' => (int)($data['status'] ?? 0),
                'reviewer_id' => (int)($data['reviewer_id'] ?? 0),
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = UserFeedbackValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserFeedbackDao::list($requestData);
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

            $validationError = UserFeedbackValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserFeedbackDao::get($requestData);
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
                'type' => (int)($data['type'] ?? 1),
                'content' => $data['content'] ?? '',
                'user_id' => (int)($data['user_id'] ?? 0),
                'target_type' => $data['target_type'] ?? 'other',
                'target_id' => (int)($data['target_id'] ?? 0),
                'source_tag' => $data['source_tag'] ?? '',
                'status' => (int)($data['status'] ?? 1),
            ];

            $validationError = UserFeedbackValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserFeedbackDao::create($requestData);
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
                'type' => (int)($data['type'] ?? 1),
                'content' => $data['content'] ?? '',
                'user_id' => (int)($data['user_id'] ?? 0),
                'target_type' => $data['target_type'] ?? 'other',
                'target_id' => (int)($data['target_id'] ?? 0),
                'source_tag' => $data['source_tag'] ?? '',
                'status' => (int)($data['status'] ?? 1),
                'reviewer_id' => (int)($data['reviewer_id'] ?? 0),
            ];

            $validationError = UserFeedbackValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserFeedbackDao::update($requestData);
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

            $validationError = UserFeedbackValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserFeedbackDao::del($requestData);
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
                'reviewer_id' => (int)($data['reviewer_id'] ?? 0),
            ];

            $validationError = UserFeedbackValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserFeedbackDao::changeStatus($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "review")]
    public function review(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
                'status' => (int)($data['status'] ?? 2),
                'reviewer_id' => (int)($data['reviewer_id'] ?? 0),
            ];

            $validationError = UserFeedbackValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserFeedbackDao::review($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
