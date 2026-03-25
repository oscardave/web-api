<?php
declare(strict_types=1);

namespace App\Http\Backend\Dao;

use Hyperf\DbConnection\Db;
use App\Http\Backend\Utils\BackendUtils;

/**
 * Index Dao
 * 对应 C++ 的 IndexDao
 */
class IndexDao
{
    /**
     * 登录
     * 对应 C++ 的 IndexDao::login()
     * 
     * @param array $request
     * @return array ['error' => string, 'data' => LoginResult|null]
     */
    public static function login(array $request): array
    {
        $name = $request['name'] ?? '';
        $password = $request['password'] ?? '';
        $clientIP = $request['clientIP'] ?? '127.0.0.1';

        // TODO: 根据 siteCode 获取对应的数据库连接
        // 目前先使用默认连接

        // 查询管理员信息
        $admin = Db::table('syn_admins')
            ->select('id', 'name', 'password', 'status')
            ->where('name', $name)
            ->first();

        if (!$admin) {
            return ['error' => '用户不存在', 'data' => null];
        }

        $adminID = (int)$admin->id;
        $adminName = $admin->name;
        $adminPassword = $admin->password;
        $adminStatus = (int)$admin->status;

        // 检查状态
        if ($adminStatus === 0) { // STATUS_DISABLED
            return ['error' => '用户已禁用', 'data' => null];
        }

        // 验证密码（bcrypt 优先，兼容旧 MD5 格式）
        if (!BackendUtils::verifyAdminPassword($password, $adminPassword)) {
            return ['error' => '密码错误', 'data' => null];
        }

        // 透明升级旧 MD5 密码为 bcrypt
        if (BackendUtils::adminPasswordNeedsRehash($adminPassword)) {
            try {
                $newHash = BackendUtils::hashAdminPassword($password);
                Db::table('syn_admins')
                    ->where('id', $adminID)
                    ->update(['password' => $newHash]);
            } catch (\Throwable $e) {
                error_log('[admin login] MD5→bcrypt upgrade failed for admin ' . $adminID . ': ' . $e->getMessage());
            }
        }

        // 生成 token
        $accessToken = BackendUtils::generateToken((string)$adminID, $adminName, $clientIP);
        $refreshToken = BackendUtils::generateRefreshToken((string)$adminID, $adminName, $clientIP);
        $expireAt = BackendUtils::generateExpireAt();

        // 构建登录结果
        $loginResult = [
            'id' => $adminID,
            'avatar' => 'https://avatars.githubusercontent.com/u/52823142',
            'username' => $adminName,
            'nickname' => '管理员',
            'roles' => ['common'],
            'permissions' => ['permission:btn:add', 'permission:btn:edit'],
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken,
            'expires' => $expireAt,
        ];

        // TODO: 将登录信息写入缓存（对应 C++ 的 AdminsCached::setLoginInfo）
        // 这里先跳过，后续可以添加 Redis 缓存

        return ['error' => '', 'data' => $loginResult];
    }

    /**
     * 刷新 Token
     * 对应 C++ 的 IndexDao::refreshToken()
     * 
     * @param array $request
     * @return array ['error' => string, 'data' => RefreshTokenResult|null]
     */
    public static function refreshToken(array $request): array
    {
        $oldToken = $request['oldToken'] ?? '';
        $clientIP = $request['clientIP'] ?? '127.0.0.1';

        // 解析旧 token 获取用户信息
        $admin = BackendUtils::parseToken($oldToken);
        if (!$admin) {
            return ['error' => '用户不存在', 'data' => null];
        }

        $adminID = (string)$admin['id'];
        $adminName = $admin['name'];

        // 生成新的 token
        $accessToken = BackendUtils::generateToken($adminID, $adminName, $clientIP);
        $refreshToken = BackendUtils::generateRefreshToken($adminID, $adminName, $clientIP);
        $expires = BackendUtils::generateExpireAt();

        $refreshTokenResult = [
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken,
            'expires' => $expires,
        ];

        return ['error' => '', 'data' => $refreshTokenResult];
    }
}
