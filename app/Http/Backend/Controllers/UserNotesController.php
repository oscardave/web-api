<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\UserNoteDao;
use App\Http\Backend\Validations\UserNoteValidation;

#[Controller(prefix: "/ht/v1/userNotes")]
class UserNotesController extends BackendController
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
                'title' => $data['title'] ?? '',
                'content' => $data['content'] ?? '',
                'user_id' => (int)($data['user_id'] ?? 0),
                'category_id' => (int)($data['category_id'] ?? 0),
                'tags' => $data['tags'] ?? '',
                'note_type' => (int)($data['note_type'] ?? 0),
                'visibility' => (int)($data['visibility'] ?? 0),
                'audit_status' => (int)($data['audit_status'] ?? 0),
                'status' => (int)($data['status'] ?? 0),
                'is_pinned' => (bool)($data['is_pinned'] ?? false),
                'is_featured' => (bool)($data['is_featured'] ?? false),
                'is_hot' => (bool)($data['is_hot'] ?? false),
                'content_type' => (int)($data['content_type'] ?? 0),
                'source' => $data['source'] ?? '',
                'start_date' => $data['start_date'] ?? '',
                'end_date' => $data['end_date'] ?? '',
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = UserNoteValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserNoteDao::list($requestData);
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

            $validationError = UserNoteValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserNoteDao::get($requestData);
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
                'title' => $data['title'] ?? '',
                'content' => $data['content'] ?? '',
                'category_id' => (int)($data['category_id'] ?? 0),
                'tags' => $data['tags'] ?? '',
                'note_type' => (int)($data['note_type'] ?? 1),
                'visibility' => (int)($data['visibility'] ?? 1),
                'is_pinned' => (bool)($data['is_pinned'] ?? false),
                'is_featured' => (bool)($data['is_featured'] ?? false),
                'allow_comment' => (bool)($data['allow_comment'] ?? true),
                'rate_limit_enabled' => (bool)($data['rate_limit_enabled'] ?? false),
                'rate_limit_count' => (int)($data['rate_limit_count'] ?? 0),
                'rate_limit_period' => (int)($data['rate_limit_period'] ?? 0),
                'audit_status' => (int)($data['audit_status'] ?? 1),
                'summary' => $data['summary'] ?? '',
                'cover_image' => $data['cover_image'] ?? '',
                'content_type' => (int)($data['content_type'] ?? 1),
                'word_count' => (int)($data['word_count'] ?? 0),
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'recommend_score' => (float)($data['recommend_score'] ?? 0.0),
                'is_hot' => (bool)($data['is_hot'] ?? false),
                'source' => $data['source'] ?? '',
                'device_info' => $data['device_info'] ?? '',
                'ip_address' => $data['ip_address'] ?? '',
                'location' => $data['location'] ?? '',
                'status' => (int)($data['status'] ?? 1),
            ];

            $validationError = UserNoteValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserNoteDao::create($requestData);
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
                'title' => $data['title'] ?? '',
                'content' => $data['content'] ?? '',
                'category_id' => (int)($data['category_id'] ?? 0),
                'tags' => $data['tags'] ?? '',
                'note_type' => (int)($data['note_type'] ?? 1),
                'visibility' => (int)($data['visibility'] ?? 1),
                'is_pinned' => (bool)($data['is_pinned'] ?? false),
                'is_featured' => (bool)($data['is_featured'] ?? false),
                'allow_comment' => (bool)($data['allow_comment'] ?? true),
                'rate_limit_enabled' => (bool)($data['rate_limit_enabled'] ?? false),
                'rate_limit_count' => (int)($data['rate_limit_count'] ?? 0),
                'rate_limit_period' => (int)($data['rate_limit_period'] ?? 0),
                'audit_status' => (int)($data['audit_status'] ?? 1),
                'auditor_id' => (int)($data['auditor_id'] ?? 0),
                'audited_at' => $data['audited_at'] ?? '',
                'summary' => $data['summary'] ?? '',
                'cover_image' => $data['cover_image'] ?? '',
                'content_type' => (int)($data['content_type'] ?? 1),
                'word_count' => (int)($data['word_count'] ?? 0),
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'recommend_score' => (float)($data['recommend_score'] ?? 0.0),
                'is_hot' => (bool)($data['is_hot'] ?? false),
                'source' => $data['source'] ?? '',
                'device_info' => $data['device_info'] ?? '',
                'ip_address' => $data['ip_address'] ?? '',
                'location' => $data['location'] ?? '',
                'status' => (int)($data['status'] ?? 1),
            ];

            $validationError = UserNoteValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserNoteDao::update($requestData);
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

            $validationError = UserNoteValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserNoteDao::del($requestData);
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

            $validationError = UserNoteValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserNoteDao::changeStatus($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "audit")]
    public function audit(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
                'audit_status' => (int)($data['audit_status'] ?? 0),
                'auditor_id' => (int)($data['auditor_id'] ?? 0),
                'audit_remark' => $data['audit_remark'] ?? '',
                'audited_at' => $data['audited_at'] ?? date('Y-m-d H:i:s'),
            ];

            $validationError = UserNoteValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserNoteDao::audit($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "pin")]
    public function pin(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
                'is_pinned' => (bool)($data['is_pinned'] ?? false),
            ];

            $validationError = UserNoteValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserNoteDao::pin($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "feature")]
    public function feature(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
                'is_featured' => (bool)($data['is_featured'] ?? false),
            ];

            $validationError = UserNoteValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserNoteDao::feature($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "hot")]
    public function hot(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
                'is_hot' => (bool)($data['is_hot'] ?? false),
            ];

            $validationError = UserNoteValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserNoteDao::hot($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "rateLimit")]
    public function rateLimit(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
                'rate_limit_enabled' => (bool)($data['rate_limit_enabled'] ?? false),
                'rate_limit_count' => (int)($data['rate_limit_count'] ?? 0),
                'rate_limit_period' => (int)($data['rate_limit_period'] ?? 0),
            ];

            $validationError = UserNoteValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserNoteDao::rateLimit($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "updateStats")]
    public function updateStats(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
                'view_count' => (int)($data['view_count'] ?? 0),
                'today_view_count' => (int)($data['today_view_count'] ?? 0),
                'like_count' => (int)($data['like_count'] ?? 0),
                'collect_count' => (int)($data['collect_count'] ?? 0),
                'share_count' => (int)($data['share_count'] ?? 0),
                'comment_count' => (int)($data['comment_count'] ?? 0),
            ];

            $validationError = UserNoteValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserNoteDao::updateStats($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "getStats")]
    public function getStats(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'id' => (int)($data['id'] ?? 0),
            ];

            $validationError = UserNoteValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserNoteDao::getStats($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "batchAudit")]
    public function batchAudit(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $ids = $data['ids'] ?? [];
            if (!is_array($ids) || empty($ids)) {
                return $this->responseError('笔记ID列表格式无效');
            }

            $ids = array_map('intval', $ids);
            $auditStatus = (int)($data['audit_status'] ?? 0);
            $auditorId = (int)($data['auditor_id'] ?? 0);

            $result = UserNoteDao::batchAudit($ids, $auditStatus, $auditorId);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "batchChangeStatus")]
    public function batchChangeStatus(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $ids = $data['ids'] ?? [];
            if (!is_array($ids) || empty($ids)) {
                return $this->responseError('笔记ID列表格式无效');
            }

            $ids = array_map('intval', $ids);
            $status = (int)($data['status'] ?? 0);

            $result = UserNoteDao::batchChangeStatus($ids, $status);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "batchDelete")]
    public function batchDelete(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $ids = $data['ids'] ?? [];
            if (!is_array($ids) || empty($ids)) {
                return $this->responseError('笔记ID列表格式无效');
            }

            $ids = array_map('intval', $ids);

            $result = UserNoteDao::batchDelete($ids);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "getNoteStats")]
    public function getNoteStats(RequestInterface $request)
    {
        try {
            $result = UserNoteDao::getNoteStats();
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "getUserNoteStats")]
    public function getUserNoteStats(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $userId = (int)($data['user_id'] ?? 0);
            if ($userId <= 0) {
                return $this->responseError('用户ID无效');
            }

            $result = UserNoteDao::getUserNoteStats($userId);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    #[PostMapping(path: "getCategoryNoteStats")]
    public function getCategoryNoteStats(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $categoryId = (int)($data['category_id'] ?? 0);
            if ($categoryId <= 0) {
                return $this->responseError('分类ID无效');
            }

            $result = UserNoteDao::getCategoryNoteStats($categoryId);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
