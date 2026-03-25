<?php
declare(strict_types=1);

namespace App\Http\Backend\Utils;

/**
 * Backend 工具类
 * 对应 C++ 的 Utils 类
 */
class BackendUtils
{
    // 管理员密码盐
    private const ADMIN_PASSWORD_SALT = 'gD3=bM1&fK2%vL7[';
    
    // Token 过期时间（秒）
    private const ADMIN_TOKEN_EXPIRE_TIME = 60 * 60 * 24; // 1天
    
    // Token 盐
    private const ADMIN_TOKEN_SALT = 'rS9>jYA:mU8-tF3!';
    
    // Refresh Token 盐
    private const ADMIN_REFRESH_TOKEN_SALT = 'qY3(iU9;rV5=wY0#';

    /**
     * @deprecated Use hashAdminPassword() for new passwords and verifyAdminPassword() for verification.
     *
     * @param string $password
     * @return string
     */
    public static function getAdminPassword(string $password): string
    {
        return md5($password . self::ADMIN_PASSWORD_SALT);
    }

    /**
     * Hash an admin password using bcrypt.
     */
    public static function hashAdminPassword(string $password): string
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        if ($hash === false) {
            throw new \RuntimeException('password_hash failed.');
        }
        return $hash;
    }

    /**
     * Verify an admin password against a stored hash (bcrypt first, MD5 fallback).
     */
    public static function verifyAdminPassword(string $password, string $storedHash): bool
    {
        if (str_starts_with($storedHash, '$2y$')
            || str_starts_with($storedHash, '$2a$')
            || str_starts_with($storedHash, '$2b$')) {
            return password_verify($password, $storedHash);
        }

        if (strlen($storedHash) === 32 && ctype_xdigit($storedHash)) {
            return hash_equals($storedHash, md5($password . self::ADMIN_PASSWORD_SALT));
        }

        return false;
    }

    /**
     * Check whether a stored admin hash needs upgrading to bcrypt.
     */
    public static function adminPasswordNeedsRehash(string $storedHash): bool
    {
        return !str_starts_with($storedHash, '$2y$')
            && !str_starts_with($storedHash, '$2a$')
            && !str_starts_with($storedHash, '$2b$');
    }

    /**
     * 生成 Token（JWT）
     * 对应 C++ 的 Utils::generateToken()
     * 
     * @param string $adminID
     * @param string $adminName
     * @param string $clientIP
     * @return string
     */
    public static function generateToken(string $adminID, string $adminName, string $clientIP): string
    {
        $now = time();
        $expireAt = $now + (self::ADMIN_TOKEN_EXPIRE_TIME * 24 * 365); // 1年后过期（与 C++ 保持一致）

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $payload = [
            'id' => $adminID,
            'name' => $adminName,
            'ip' => $clientIP,
            'exp' => (string)$expireAt,
            'iat' => $now,
        ];

        return self::encodeJWT($header, $payload, self::ADMIN_TOKEN_SALT);
    }

    /**
     * 生成 Refresh Token（JWT）
     * 对应 C++ 的 Utils::generateRefreshToken()
     * 
     * @param string $adminID
     * @param string $adminName
     * @param string $clientIP
     * @return string
     */
    public static function generateRefreshToken(string $adminID, string $adminName, string $clientIP): string
    {
        $now = time();
        $expireAt = $now + (self::ADMIN_TOKEN_EXPIRE_TIME * 24 * 365); // 1年后过期

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $payload = [
            'id' => $adminID,
            'name' => $adminName,
            'ip' => $clientIP,
            'exp' => (string)$expireAt,
            'iat' => $now,
        ];

        return self::encodeJWT($header, $payload, self::ADMIN_REFRESH_TOKEN_SALT);
    }

    /**
     * 解析 Token
     * 对应 C++ 的 Utils::parseToken()
     * 
     * @param string $token
     * @return array|null ['id' => int, 'name' => string, 'ip' => string]
     */
    public static function parseToken(string $token): ?array
    {
        if (empty($token)) {
            return null;
        }

        try {
            $decoded = self::decodeJWT($token, self::ADMIN_TOKEN_SALT);
            if (!$decoded) {
                return null;
            }

            // 检查过期时间
            $exp = isset($decoded['exp']) ? (int)$decoded['exp'] : 0;
            $now = time();
            if ($exp < $now) {
                return null; // token 已过期
            }

            return [
                'id' => (int)$decoded['id'],
                'name' => (string)$decoded['name'],
                'ip' => (string)$decoded['ip'],
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 生成过期时间字符串（格式：'xxxx/xx/xx xx:xx:xx'）
     * 对应 C++ 的 Utils::generateExpireAt()
     * 
     * @return string
     */
    public static function generateExpireAt(): string
    {
        $now = time();
        $expireAt = $now + self::ADMIN_TOKEN_EXPIRE_TIME; // 1天（与 C++ 保持一致）

        // 转换为 'xxxx/xx/xx xx:xx:xx' 格式（UTC 时间）
        return gmdate('Y/m/d H:i:s', $expireAt);
    }

    /**
     * 编码 JWT
     * 
     * @param array $header
     * @param array $payload
     * @param string $secret
     * @return string
     */
    private static function encodeJWT(array $header, array $payload, string $secret): string
    {
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($header)));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }

    /**
     * 解码 JWT
     * 
     * @param string $token
     * @param string $secret
     * @return array|null
     */
    private static function decodeJWT(string $token, string $secret): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$base64Header, $base64Payload, $base64Signature] = $parts;

        // 验证签名
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        if ($base64Signature !== $expectedSignature) {
            return null; // 签名验证失败
        }

        // 解码 payload
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Payload)), true);
        if (!$payload) {
            return null;
        }

        return $payload;
    }
}
