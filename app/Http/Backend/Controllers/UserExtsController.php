<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\UserExtDao;
use App\Http\Backend\Validations\UserExtValidation;
use App\Common\Utils;

#[Controller(prefix: "/ht/v1/userExts")]
class UserExtsController extends BackendController
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
                'id' => isset($data['id']) ? (int)$data['id'] : 0,
                'nickname' => $data['nickname'] ?? '',
                'login_ip' => $data['login_ip'] ?? '',
                'referrer_id' => $data['referrer_id'] ?? '',
                'registration_time_start' => $data['registration_time_start'] ?? '',
                'registration_time_end' => $data['registration_time_end'] ?? '',
                'last_login_time_start' => $data['last_login_time_start'] ?? '',
                'last_login_time_end' => $data['last_login_time_end'] ?? '',
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = UserExtValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserExtDao::list($requestData);
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
                'user_id' => $data['user_id'] ?? '',
            ];

            $validationError = UserExtValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserExtDao::get($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * 修改用户扩展状态（同步更新 Synapse users.deactivated）
     * 仅 normal 为可登录，其余 banned/suspended/limited 均视为禁用。
     */
    #[PostMapping(path: "changeStatus")]
    public function changeStatus(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'user_id' => (string)($data['user_id'] ?? ''),
                'account_status' => (string)($data['account_status'] ?? ''),
            ];

            $validationError = UserExtValidation::validateChangeStatus($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserExtDao::changeStatus($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * 设置用户处罚（禁止聊天 / 禁止创建笔记 / 禁止超级坐席笔记）
     * 禁止聊天会同步更新 Synapse users.shadow_banned（同库）
     */
    #[PostMapping(path: "setPenalty")]
    public function setPenalty(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'user_id' => (string)($data['user_id'] ?? ''),
                'chat_prohibited' => (bool)($data['chat_prohibited'] ?? false),
                'note_creation_prohibited' => (bool)($data['note_creation_prohibited'] ?? false),
                'super_seat_note_prohibited' => (bool)($data['super_seat_note_prohibited'] ?? false),
            ];

            $validationError = UserExtValidation::validateSetPenalty($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserExtDao::setPenalty($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * 管理员修改用户密码：调用 Synapse Admin API 更新密码并踢掉该用户所有设备，强制重新登录。
     */
    #[PostMapping(path: "setPassword")]
    public function setPassword(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'user_id' => trim((string)($data['user_id'] ?? '')),
                'new_password' => (string)($data['new_password'] ?? ''),
            ];

            $validationError = UserExtValidation::validateSetPassword($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserExtDao::setPassword($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * 用户奖励：增加笔记上限 / 超级坐席笔记上限
     */
    #[PostMapping(path: "setReward")]
    public function setReward(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'user_id' => (string)($data['user_id'] ?? ''),
                'note_limit_increase' => (int)($data['note_limit_increase'] ?? 0),
                'super_seat_limit_increase' => (int)($data['super_seat_limit_increase'] ?? 0),
            ];

            $validationError = UserExtValidation::validateSetReward($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserExtDao::setReward($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * 更新用户会员与徽章信息（会员等级、身份徽章、诚信徽章；会员等级截止时间由后端根据所选等级的 validity_period 自动设置）
     */
    #[PostMapping(path: "updateMembership")]
    public function updateMembership(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'user_id' => (string)($data['user_id'] ?? ''),
                'member_level_id' => (int)($data['member_level_id'] ?? 0),
                'identity_badge_id' => (int)($data['identity_badge_id'] ?? 0),
                'credit_badge_id' => (int)($data['credit_badge_id'] ?? 0),
                'pure_badge_enabled' => isset($data['pure_badge_enabled']) ? (bool)$data['pure_badge_enabled'] : false,
                'circle_badge_id' => (int)($data['circle_badge_id'] ?? 0),
                'vanity_number' => isset($data['vanity_number']) ? trim((string)$data['vanity_number']) : '',
            ];

            $validationError = UserExtValidation::validateUpdateMembership($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserExtDao::updateMembership($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * 更新用户头像与个人描述（同步 ext_users 和 profiles）
     */
    #[PostMapping(path: "updateProfile")]
    public function updateProfile(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $avatarUrl = isset($data['avatar_url']) ? (string)$data['avatar_url'] : null;
            $requestData = [
                'user_id' => (string)($data['user_id'] ?? ''),
                'avatar_url' => $avatarUrl !== null ? Utils::toStoragePath($avatarUrl) : null,
                'nickname' => isset($data['nickname']) ? trim((string)$data['nickname']) : null,
                'personal_description' => isset($data['personal_description']) ? (string)$data['personal_description'] : null,
            ];

            $validationError = UserExtValidation::validateUpdateProfile($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserExtDao::updateProfile($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * 发送系统消息（通过 Synapse Admin API Server Notice 发送到用户聊天）
     */
    #[PostMapping(path: "sendMessage")]
    public function sendMessage(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'user_id' => trim((string)($data['user_id'] ?? '')),
                'message' => (string)($data['message'] ?? ''),
            ];

            $validationError = UserExtValidation::validateSendMessage($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserExtDao::sendSystemMessage($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * 获取随机头像地址（从 HEADS_LIST_FILE 随机读取一行，返回以 / 开头的路径，不含域名）
     */
    #[PostMapping(path: "getRandomAvatar")]
    public function getRandomAvatar(RequestInterface $request)
    {
        try {
            $result = UserExtDao::getRandomAvatarPath();
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }
            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
