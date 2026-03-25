<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Exception\Http\EncodingException;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\CircleDao;
use App\Http\Backend\Validations\CircleValidation;

#[Controller(prefix: "/ht/v1/circles")]
class CirclesController extends BackendController
{
    #[PostMapping(path: "list")]
    public function list(RequestInterface $request)
    {
        try {
            $data = $request->all();
            $logger = ApplicationContext::getContainer()->get(StdoutLoggerInterface::class);
            $logger->info('[CirclesController::list] request body: ' . json_encode($data, JSON_UNESCAPED_UNICODE));

            if (empty($data)) {
                $logger->warning('[CirclesController::list] empty request body, return 无效的请求体');
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'circle_name' => $data['circle_name'] ?? '',
                'owner_vanity_id' => $data['owner_vanity_id'] ?? '',
                'circle_id' => $data['circle_id'] ?? '',
                'circle_status' => $data['circle_status'] ?? '',
                'circle_type' => $data['circle_type'] ?? '',
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = CircleValidation::validate($requestData);
            if ($validationError !== '') {
                $logger->warning('[CirclesController::list] validation failed: ' . $validationError);
                return $this->responseError($validationError);
            }

            $result = CircleDao::list($requestData);
            if ($result['error'] !== '') {
                $logger->warning('[CirclesController::list] CircleDao::list error: ' . $result['error']);
                return $this->responseError($result['error']);
            }

            $total = $result['data']['total'] ?? 0;
            $recordsCount = isset($result['data']['records']) ? count($result['data']['records']) : 0;
            $logger->info(sprintf('[CirclesController::list] success, total=%d, records=%d (list 仅查询 status=1 的已通过圈子)', $total, $recordsCount));

            // 保证与 rejectedList 一致的返回结构：{ success, message, data: { records, total } }
            $data = [
                'records' => $result['data']['records'] ?? [],
                'total' => (int) $total,
            ];
            $body = json_encode(['success' => true, 'message' => null, 'data' => $data], JSON_UNESCAPED_UNICODE);
            $logger->info(sprintf('[CirclesController::list] response body length=%d (若前端仍无结构体，请检查代理/Nginx 或浏览器 Network 中该请求的 Response)', strlen($body)));
            return $this->responseData($data);
        } catch (EncodingException $e) {
            $logger = ApplicationContext::getContainer()->get(StdoutLoggerInterface::class);
            $logger->error('[CirclesController::list] JSON encoding failed: ' . $e->getMessage());
            return $this->responseError('服务器错误');
        } catch (\Exception $e) {
            $logger = ApplicationContext::getContainer()->get(StdoutLoggerInterface::class);
            $logger->error('[CirclesController::list] exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->responseError('服务器错误');
        }
    }

    #[PostMapping(path: "updateSettings")]
    public function updateSettings(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'circle_id' => trim((string)($data['circle_id'] ?? '')),
                'invitation_code_count' => (int)($data['invitation_code_count'] ?? 5),
                'join_user_level_restriction' => (string)($data['join_user_level_restriction'] ?? ''),
                'help_message_view_restriction' => (string)($data['help_message_view_restriction'] ?? ''),
                'help_message_post_restriction' => (string)($data['help_message_post_restriction'] ?? ''),
                'allow_member_invite' => (bool)($data['allow_member_invite'] ?? true),
                'allow_member_edit_info' => (bool)($data['allow_member_edit_info'] ?? false),
                'auto_approve_join' => (bool)($data['auto_approve_join'] ?? false),
                'allow_change_owner' => (bool)($data['allow_change_owner'] ?? true),
                'allow_remove_admin' => (bool)($data['allow_remove_admin'] ?? true),
            ];

            $validationError = CircleValidation::validateUpdateSettings($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = CircleDao::updateSettings($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError('服务器错误');
        }
    }

    /**
     * 更新圈子基本信息（名称、描述、公告、头像、成员上限、类型）
     */
    #[PostMapping(path: "updateInfo")]
    public function updateInfo(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'circle_id' => trim((string)($data['circle_id'] ?? '')),
                'circle_name' => trim((string)($data['circle_name'] ?? '')),
                'circle_description' => (string)($data['circle_description'] ?? ''),
                'circle_announcement' => (string)($data['circle_announcement'] ?? ''),
                'circle_avatar_url' => (string)($data['circle_avatar_url'] ?? ''),
                'member_limit' => (int)($data['member_limit'] ?? 5000),
                'circle_type' => (string)($data['circle_type'] ?? 'public'),
            ];

            $validationError = CircleValidation::validateUpdateInfo($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = CircleDao::updateInfo($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError('服务器错误');
        }
    }

    /** 待审列表：status=0 */
    #[PostMapping(path: "pendingList")]
    public function pendingList(RequestInterface $request)
    {
        try {
            $data = $request->all();
            $requestData = [
                'circle_name' => $data['circle_name'] ?? '',
                'owner_id' => $data['owner_id'] ?? '',
                'owner_nickname' => $data['owner_nickname'] ?? '',
                'owner_vanity_id' => $data['owner_vanity_id'] ?? '',
                'circle_type' => $data['circle_type'] ?? '',
                'apply_time_start' => $data['apply_time_start'] ?? '',
                'apply_time_end' => $data['apply_time_end'] ?? '',
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];
            $result = CircleDao::pendingList($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }
            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError('服务器错误');
        }
    }

    /** 已拒绝列表：status=3 */
    #[PostMapping(path: "rejectedList")]
    public function rejectedList(RequestInterface $request)
    {
        try {
            $data = $request->all();
            $requestData = [
                'circle_name' => $data['circle_name'] ?? '',
                'owner_vanity_id' => $data['owner_vanity_id'] ?? '',
                'circle_type' => $data['circle_type'] ?? '',
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];
            $result = CircleDao::rejectedList($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }
            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError('服务器错误');
        }
    }

    /** 审核通过 */
    #[PostMapping(path: "approve")]
    public function approve(RequestInterface $request)
    {
        try {
            $data = $request->all();
            $requestData = ['circle_id' => trim((string)($data['circle_id'] ?? ''))];
            if ($requestData['circle_id'] === '') {
                return $this->responseError('圈子ID不能为空');
            }
            $result = CircleDao::approve($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }
            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError('服务器错误');
        }
    }

    /** 审核拒绝 */
    #[PostMapping(path: "reject")]
    public function reject(RequestInterface $request)
    {
        try {
            $data = $request->all();
            $requestData = [
                'circle_id' => trim((string)($data['circle_id'] ?? '')),
                'reject_reason' => (string)($data['reject_reason'] ?? ''),
            ];
            if ($requestData['circle_id'] === '') {
                return $this->responseError('圈子ID不能为空');
            }
            $result = CircleDao::reject($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }
            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError('服务器错误');
        }
    }
}
