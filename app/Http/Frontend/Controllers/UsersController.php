<?php

declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use App\Http\Frontend\Dao\Users as UsersDao;
use App\Http\Frontend\Dao\UserPermissions as UserPermissionsDao;


#[Controller(prefix: "api/v1/users")]
class UsersController extends FrontendController
{
    /**
     * 获取用户资料
     * 支持可选的 GET 参数 user_id，如果提供则获取指定用户的资料，否则获取当前登录用户的资料
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "profile")]
    public function profile(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        // 获取参数（优先 id，其次 user_id）
        $idParam = $request->query('id', '');
        $userIdParam = $request->query('user_id', '');

        $targetUserId = '';

        // 判断使用哪个参数
        if (!empty($idParam) && $idParam !== '0' && $idParam !== 0) {
            // id 参数存在且不为0
            if (is_numeric($idParam)) {
                // id 是数字，从 ext_users 表查询 user_id
                $idResult = UsersDao::getUserIdById((int)$idParam);
                if ($idResult['error'] !== '') {
                    return self::jsonErr($idResult['error']);
                }
                $targetUserId = $idResult['user_id'];
            } else {
                // id 不是数字，当作 user_id 使用
                $targetUserId = $idParam;
            }
        } elseif (!empty($userIdParam)) {
            // id 参数为空或0，使用 user_id 参数
            $targetUserId = $userIdParam;
        } else {
            // 两个参数都为空或0，使用当前登录用户
            $targetUserId = $auth['user_id'];
        }

        // 传入当前登录用户的 user_id，用于查询 remark_name
        $result = UsersDao::profile($targetUserId, $auth['user_id']);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        // 检查是否被当前用户拉黑
        $isBlocked = false;
        // 如果查看的不是自己的资料，才需要检查拉黑状态
        if ($targetUserId !== $auth['user_id']) {
            $isBlocked = UserPermissionsDao::isBlocked($auth['user_id'], $targetUserId);
        }

        // 添加 is_blocked 字段
        $result['data']['is_blocked'] = $isBlocked;

        return self::jsonArray($result['data']);
    }

    /**
     * 设置显示名（兼容 Synapse PUT /profile/{user_id}/displayname）
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PutMapping(path: "profile/displayname")]
    public function setDisplayname(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $data = $request->all();
        if (!isset($data['displayname'])) {
            return self::jsonErr('Missing key \'displayname\'');
        }

        $result = UsersDao::setDisplayname($auth['user_id'], $data['displayname']);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        // 返回空对象，符合 Synapse 标准
        return self::jsonArray([]);
    }

    /**
     * 设置头像（兼容 Synapse PUT /profile/{user_id}/avatar_url）
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PutMapping(path: "profile/avatar_url")]
    public function setAvatar(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $data = $request->all();
        if (!isset($data['avatar_url'])) {
            return self::jsonErr('Missing key \'avatar_url\'');
        }

        $result = UsersDao::setAvatar($auth['user_id'], $data['avatar_url']);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        // 返回空对象，符合 Synapse 标准
        return self::jsonArray([]);
    }

    /**
     * 设置昵称（同步更新 ext_users 和 users 表）
     * 支持 POST；另提供 setNicknamePut 以支持 PUT（与 displayname/avatar_url 一致）。
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "profile/nickname")]
    public function setNickname(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $data = $request->all();
        if (!isset($data['nickname'])) {
            return self::jsonErr('Missing key \'nickname\'');
        }

        $result = UsersDao::setNickname($auth['user_id'], $data['nickname']);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        // 返回空对象，符合 Synapse 标准
        return self::jsonOk();
    }

    /**
     * 设置昵称（PUT），与 displayname/avatar_url 用法一致，逻辑同 setNickname。
     */
    #[PutMapping(path: "profile/nickname")]
    public function setNicknamePut(RequestInterface $request): mixed
    {
        return $this->setNickname($request);
    }

    /**
     * 设置性别（更新 ext_users 表中的 gender 字段）
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "gender")]
    public function setGender(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $data = $request->all();
        if (!isset($data['gender'])) {
            return self::jsonErr('Missing key \'gender\'');
        }

        $result = UsersDao::setGender($auth['user_id'], $data['gender']);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        // 返回空对象，符合 Synapse 标准
        return self::jsonOk();
    }

    /**
     * 设置个性签名（扩展功能）
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "sign")]
    public function setSign(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $data = $request->all();
        $sign = $data['sign'] ?? '';

        $result = UsersDao::setSign($auth['user_id'], $sign);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        return self::jsonOk('个性签名设置成功');
    }

    /**
     * 刷新用户积分/诚信保余额
     * 从 ext_user_accounts 获取当前用户最新账户信息
     */
    #[GetMapping(path: "account")]
    public function account(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $initResult = UsersDao::initializeExtUser($auth['user_id']);
        if ($initResult['error'] !== '') {
            return self::jsonErr($initResult['error']);
        }

        $result = UsersDao::getAccount((int)$initResult['ext_user_id']);
        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        return self::jsonArray($result['data']);
    }

    /**
     * 设置备注（扩展功能）
     * 如果 user_id 为空，则保存当前用户的备注到 ext_users 表
     * 如果 user_id 不为空，则保存当前用户对指定用户的备注到 ext_user_remarks 表
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "remark")]
    public function setRemark(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $data = $request->all();
        $remarkUserId = $data['user_id'] ?? '';
        $remark = $data['remark'] ?? '';

        // 验证 remark 参数
        if (empty($remark)) {
            return self::jsonErr('remark 参数不能为空');
        }

        // 如果 user_id 为空，保存到 ext_users 表（当前用户的备注）
        if (empty($remarkUserId)) {
            $result = UsersDao::setRemark($auth['user_id'], $remark);
        } else {
            // 如果 user_id 不为空，保存到 ext_user_remarks 表（对应用户的备注）
            $result = UsersDao::setUserRemark($auth['user_id'], $remarkUserId, $remark);
        }

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        return self::jsonOk('备注设置成功');
    }

    /**
     * 设置聊天禁用状态（扩展功能）
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "chat-prohibited")]
    public function setChatProhibited(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $data = $request->all();
        $prohibited = $data['prohibited'] ?? false;

        $result = UsersDao::setChatProhibited($auth['user_id'], (bool)$prohibited);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        return self::jsonOk('聊天状态设置成功');
    }

    /**
     * 设置笔记权限（扩展功能）
     * 如果不提供 user_id，则设置自己的笔记创建权限
     * 如果提供 user_id，则设置对目标用户的笔记访问权限
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "note-permission")]
    public function setNotePermission(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $data = $request->all();
        $noteAccess = $data['note_access'] ?? false; // 是否阻止访问
        $targetUserId = $data['user_id'] ?? ''; // 可选的目标用户ID

        $result = UsersDao::setNotePermission($auth['user_id'], (bool)$noteAccess, $targetUserId);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        return self::jsonOk('笔记权限设置成功');
    }

    /**
     * 绑定手机号（需先调用发送手机验证码接口，verify_type=bind_account）
     * 入参：phone, verify_code
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "bind-phone")]
    public function bindPhone(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $data = $request->all();
        $phone = trim((string)($data['phone'] ?? ''));
        $verifyCode = trim((string)($data['verify_code'] ?? ''));
        $countryCode = isset($data['country_code']) ? trim((string) $data['country_code']) : null;
        if ($countryCode === '') {
            $countryCode = null;
        }

        $result = UsersDao::bindPhone($auth['user_id'], $phone, $verifyCode, $countryCode);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        return self::jsonOk('绑定成功');
    }

    /**
     * 绑定邮箱（需先调用发送邮箱验证码接口，verify_type=bind_account）
     * 入参：email, verify_code
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "bind-email")]
    public function bindEmail(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $data = $request->all();
        $email = trim((string)($data['email'] ?? ''));
        $verifyCode = trim((string)($data['verify_code'] ?? ''));

        $result = UsersDao::bindEmail($auth['user_id'], $email, $verifyCode);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        return self::jsonOk('绑定成功');
    }

    /**
     * 换绑手机第一步：验证旧手机验证码
     * 需先调用发送手机验证码接口给旧手机号（verify_type=rebind_verify_old）
     * 入参：verify_code
     */
    #[PostMapping(path: "rebind-phone/verify-old")]
    public function rebindPhoneVerifyOld(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $data = $request->all();
        $verifyCode = trim((string)($data['verify_code'] ?? ''));

        $result = UsersDao::verifyOldPhone($auth['user_id'], $verifyCode);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        return self::jsonArray($result['data']);
    }

    /**
     * 换绑手机第二步：用新手机验证码完成换绑
     * 需先调用发送手机验证码接口给新手机号（verify_type=rebind_bind_new）
     * 入参：phone, verify_code, rebind_token, country_code(可选)
     */
    #[PostMapping(path: "rebind-phone/bind-new")]
    public function rebindPhoneBindNew(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $data = $request->all();
        $phone = trim((string)($data['phone'] ?? ''));
        $verifyCode = trim((string)($data['verify_code'] ?? ''));
        $rebindToken = trim((string)($data['rebind_token'] ?? ''));
        $countryCode = isset($data['country_code']) ? trim((string)$data['country_code']) : null;
        if ($countryCode === '') {
            $countryCode = null;
        }

        $result = UsersDao::rebindPhone($auth['user_id'], $phone, $verifyCode, $rebindToken, $countryCode);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        return self::jsonOk('换绑成功');
    }

    /**
     * 换绑邮箱第一步：验证旧邮箱验证码
     * 需先调用发送邮箱验证码接口给旧邮箱（verify_type=rebind_verify_old）
     * 入参：verify_code
     */
    #[PostMapping(path: "rebind-email/verify-old")]
    public function rebindEmailVerifyOld(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $data = $request->all();
        $verifyCode = trim((string)($data['verify_code'] ?? ''));

        $result = UsersDao::verifyOldEmail($auth['user_id'], $verifyCode);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        return self::jsonArray($result['data']);
    }

    /**
     * 换绑邮箱第二步：用新邮箱验证码完成换绑
     * 需先调用发送邮箱验证码接口给新邮箱（verify_type=rebind_bind_new）
     * 入参：email, verify_code, rebind_token
     */
    #[PostMapping(path: "rebind-email/bind-new")]
    public function rebindEmailBindNew(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $data = $request->all();
        $email = trim((string)($data['email'] ?? ''));
        $verifyCode = trim((string)($data['verify_code'] ?? ''));
        $rebindToken = trim((string)($data['rebind_token'] ?? ''));

        $result = UsersDao::rebindEmail($auth['user_id'], $email, $verifyCode, $rebindToken);

        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        return self::jsonOk('换绑成功');
    }
}
