<?php

declare(strict_types=1);

namespace App\Http\Frontend\Dao;

use Hyperf\DbConnection\Db;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Context\ApplicationContext;
use App\Common\Cache;
use App\Common\Utils;
use App\Helpers\PhoneHelper;
use App\Helpers\SmsService;

class Index
{
    /**
     * Matrix MXID 的 server 部分，始终使用服务端配置（domains.default_home_server），
     * 须与 Synapse server_name 一致；不采用客户端传入的 home_server，避免旧客户端传错域名。
     */
    private static function canonicalHomeServer(): string
    {
        return (string) ApplicationContext::getContainer()
            ->get(ConfigInterface::class)
            ->get('domains.default_home_server', 'im-sq01.bleiworc.xyz');
    }

    /**
     * 统计时间窗内已成功落库的验证码发送次数（status=1），用于限流。
     *
     * @param 'phone'|'mail' $contactType
     * @param '1m'|'1h'|'24h' $window 固定键，对应 SQL INTERVAL 字面量
     */
    private static function countVerifyCodeSendsInWindow(
        string $contactType,
        string $contactValue,
        string $verifyType,
        string $window
    ): int {
        $intervalSql = match ($window) {
            '1m' => "INTERVAL '1 minute'",
            '1h' => "INTERVAL '1 hour'",
            '24h' => "INTERVAL '24 hours'",
            default => throw new \InvalidArgumentException('invalid rate limit window'),
        };

        return (int) Db::table('ext_verify_codes')
            ->where('contact_type', $contactType)
            ->where('contact_value', $contactValue)
            ->where('verify_type', $verifyType)
            ->where('status', 1)
            ->whereRaw("created_at > (CURRENT_TIMESTAMP - {$intervalSql})")
            ->count();
    }

    /** @return string 错误信息，空字符串表示通过 */
    private static function checkPhoneVerifyCodeRateLimit(string $phone, string $verifyType): string
    {
        $n1m = self::countVerifyCodeSendsInWindow('phone', $phone, $verifyType, '1m');
        if ($n1m >= 1) {
            return '验证码发送过于频繁，请1分钟后再试';
        }
        $n1h = self::countVerifyCodeSendsInWindow('phone', $phone, $verifyType, '1h');
        if ($n1h >= 5) {
            return '验证码发送次数过多，1小时内最多发送5次，请稍后再试';
        }
        $n24h = self::countVerifyCodeSendsInWindow('phone', $phone, $verifyType, '24h');
        if ($n24h >= 10) {
            return '验证码发送次数过多，24小时内最多发送10次，请明日再试';
        }

        return '';
    }

    /** @return string 错误信息，空字符串表示通过 */
    private static function checkEmailVerifyCodeRateLimit(string $email, string $verifyType): string
    {
        $n1m = self::countVerifyCodeSendsInWindow('mail', $email, $verifyType, '1m');
        if ($n1m >= 1) {
            return '验证码发送过于频繁，请1分钟后再试';
        }
        $n1h = self::countVerifyCodeSendsInWindow('mail', $email, $verifyType, '1h');
        if ($n1h >= 3) {
            return '验证码发送次数过多，1小时内最多发送3次，请稍后再试';
        }
        $n24h = self::countVerifyCodeSendsInWindow('mail', $email, $verifyType, '24h');
        if ($n24h >= 5) {
            return '验证码发送次数过多，24小时内最多发送5次，请明日再试';
        }

        return '';
    }

    /**
     * 发送手机验证码
     * @param array $data
     * @return string 返回错误消息，空字符串表示成功
     */
    public static function sendPhoneVerifyCode(array $data): string
    {
        echo "[sendPhoneVerifyCode] 开始执行，参数：" . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";

        // 1. 验证必填参数
        if (empty($data['phone'])) {
            echo "[sendPhoneVerifyCode] 错误：手机号不能为空\n";
            return '手机号不能为空';
        }
        if (empty($data['verify_type'])) {
            echo "[sendPhoneVerifyCode] 错误：验证类型不能为空\n";
            return '验证类型不能为空';
        }
        if (empty($data['request_ip'])) {
            echo "[sendPhoneVerifyCode] 错误：客户端IP不能为空\n";
            return '客户端IP不能为空';
        }

        $phone = trim($data['phone']);
        $countryCode = isset($data['country_code']) ? trim((string) $data['country_code']) : null;
        if ($countryCode === '') {
            $countryCode = null;
        }
        $verifyType = $data['verify_type'];
        $userId = $data['user_id'] ?? 0;
        $clientIp = $data['request_ip'];

        echo "[sendPhoneVerifyCode] 处理后参数 - phone: $phone, country_code: " . ($countryCode ?? 'null') . ", verifyType: $verifyType, userId: $userId, clientIp: $clientIp\n";

        // 2. 归一化为 E.164（支持多国家：仅中国 11 位、E.164 或 country_code + 本地号）
        $e164 = PhoneHelper::normalizeToE164($phone, $countryCode);
        if ($e164 === '') {
            echo "[sendPhoneVerifyCode] 错误：手机号格式不正确\n";
            return '手机号格式不正确';
        }
        $phone = $e164;

        // 3. 发送频率：1分钟最多1次、1小时最多5次、24小时最多10次（按手机号+verify_type，status=1）
        try {
            echo "[sendPhoneVerifyCode] 检查发送频率...\n";
            $rateErr = self::checkPhoneVerifyCodeRateLimit($phone, $verifyType);
            if ($rateErr !== '') {
                echo "[sendPhoneVerifyCode] 错误：{$rateErr}\n";
                return $rateErr;
            }
            echo "[sendPhoneVerifyCode] 频率检查通过\n";
        } catch (\Exception $e) {
            echo "[sendPhoneVerifyCode] 检查频率异常：" . $e->getMessage() . "\n";
            echo "[sendPhoneVerifyCode] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return '系统错误，请稍后重试';
        }

        // 4. 生成6位随机数字验证码
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // 5. 插入数据库记录
        try {
            echo "[sendPhoneVerifyCode] 准备插入数据库...\n";
            $result = Db::insert(
                "INSERT INTO ext_verify_codes (user_id, contact_type, contact_value, verify_code, verify_type, request_ip, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$userId, 'phone', $phone, $code, $verifyType, $clientIp, 1]
            );
            echo "[sendPhoneVerifyCode] 数据库插入结果：" . ($result ? '成功' : '失败') . "\n";
        } catch (\Exception $e) {
            echo "[sendPhoneVerifyCode] 数据库插入异常：" . $e->getMessage() . "\n";
            echo "[sendPhoneVerifyCode] 异常代码：" . $e->getCode() . "\n";
            echo "[sendPhoneVerifyCode] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return '验证码生成失败，请稍后重试';
        }

        // 6. 存入Redis用于快速验证（600秒过期）
        try {
            echo "[sendPhoneVerifyCode] 准备存入Redis...\n";
            $redis = Cache::get();
            $redis->set("phone_verify_code:{$phone}:{$verifyType}", $code, 600);
            echo "[sendPhoneVerifyCode] Redis存储成功\n";
        } catch (\Exception $e) {
            echo "[sendPhoneVerifyCode] Redis存储异常：" . $e->getMessage() . "\n";
            echo "[sendPhoneVerifyCode] 异常堆栈：" . $e->getTraceAsString() . "\n";
            // Redis失败不影响主流程
        }

        // 7. 调用短信服务发送验证码
        try {
            echo "[sendPhoneVerifyCode] 准备发送短信...\n";

            // 根据验证类型生成不同的短信内容
            $message = self::getVerifyCodeMessage($verifyType, $code);

            // 调用短信服务发送
            $smsResult = SmsService::sendSms($phone, $message);

            if (!$smsResult['success']) {
                echo "[sendPhoneVerifyCode] 短信发送失败：" . $smsResult['message'] . "\n";
                // 短信发送失败不影响验证码已生成，但记录错误
                // 可以根据业务需求决定是否返回错误
                // return '短信发送失败：' . $smsResult['message'];
            } else {
                echo "[sendPhoneVerifyCode] 短信发送成功\n";
            }
        } catch (\Exception $e) {
            echo "[sendPhoneVerifyCode] 发送短信异常：" . $e->getMessage() . "\n";
            echo "[sendPhoneVerifyCode] 异常堆栈：" . $e->getTraceAsString() . "\n";
            // 短信发送异常不影响主流程，验证码已生成
        }

        echo "[sendPhoneVerifyCode] 执行完成，返回成功\n";
        return ''; // 成功返回空字符串
    }

    /**
     * 根据验证类型生成短信内容
     * @param string $verifyType 验证类型（register, login, reset_password 等）
     * @param string $code 验证码
     * @return string 短信内容
     */
    private static function getVerifyCodeMessage(string $verifyType, string $code): string
    {
        $messages = [
            'register' => "您的注册验证码是 {$code}，5分钟内有效，请勿泄露给他人。",
            'login' => "您的登录验证码是 {$code}，5分钟内有效，请勿泄露给他人。",
            'reset_password' => "您的找回密码验证码是 {$code}，5分钟内有效，请勿泄露给他人。",
            'bind_account' => "您的绑定验证码是 {$code}，5分钟内有效，请勿泄露给他人。",
            'rebind_verify_old' => "您正在进行换绑操作，验证码是 {$code}，5分钟内有效，请勿泄露给他人。",
            'rebind_bind_new' => "您正在绑定新账号，验证码是 {$code}，5分钟内有效，请勿泄露给他人。",
        ];

        // 如果找不到对应的类型，使用默认消息
        return $messages[$verifyType] ?? "您的验证码是 {$code}，5分钟内有效，请勿泄露给他人。";
    }

    /**
     * 发送邮箱验证码
     * @param array $data
     * @return string 返回错误消息，空字符串表示成功
     */
    public static function sendEmailVerifyCode(array $data): string
    {
        echo "[sendEmailVerifyCode] 开始执行，参数：" . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";

        // 1. 验证必填参数
        if (empty($data['email'])) {
            echo "[sendEmailVerifyCode] 错误：邮箱地址不能为空\n";
            return '邮箱地址不能为空';
        }
        if (empty($data['verify_type'])) {
            echo "[sendEmailVerifyCode] 错误：验证类型不能为空\n";
            return '验证类型不能为空';
        }
        if (empty($data['request_ip'])) {
            echo "[sendEmailVerifyCode] 错误：客户端IP不能为空\n";
            return '客户端IP不能为空';
        }

        $email = trim($data['email']);
        $verifyType = $data['verify_type'];
        $userId = $data['user_id'] ?? 0;
        $clientIp = $data['request_ip'];

        echo "[sendEmailVerifyCode] 处理后参数 - email: $email, verifyType: $verifyType, userId: $userId, clientIp: $clientIp\n";

        // 2. 验证邮箱格式（入库用小写，避免同邮箱大小写绕过限流）
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "[sendEmailVerifyCode] 错误：邮箱地址格式不正确\n";
            return '邮箱地址格式不正确';
        }
        $email = strtolower($email);

        // 3. 发送频率：1分钟最多1次、1小时最多3次、24小时最多5次（按邮箱+verify_type，status=1）
        try {
            echo "[sendEmailVerifyCode] 检查发送频率...\n";
            $rateErr = self::checkEmailVerifyCodeRateLimit($email, $verifyType);
            if ($rateErr !== '') {
                echo "[sendEmailVerifyCode] 错误：{$rateErr}\n";
                return $rateErr;
            }
            echo "[sendEmailVerifyCode] 频率检查通过\n";
        } catch (\Exception $e) {
            echo "[sendEmailVerifyCode] 检查频率异常：" . $e->getMessage() . "\n";
            echo "[sendEmailVerifyCode] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return '系统错误，请稍后重试';
        }

        // 4. 生成6位随机数字验证码
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // 5. 插入数据库记录
        try {
            echo "[sendEmailVerifyCode] 准备插入数据库...\n";
            $result = Db::insert(
                "INSERT INTO ext_verify_codes (user_id, contact_type, contact_value, verify_code, verify_type, request_ip, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$userId, 'mail', $email, $code, $verifyType, $clientIp, 1]
            );
            echo "[sendEmailVerifyCode] 数据库插入结果：" . ($result ? '成功' : '失败') . "\n";
        } catch (\Exception $e) {
            echo "[sendEmailVerifyCode] 数据库插入异常：" . $e->getMessage() . "\n";
            echo "[sendEmailVerifyCode] 异常代码：" . $e->getCode() . "\n";
            echo "[sendEmailVerifyCode] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return '验证码生成失败，请稍后重试';
        }

        // 6. 存入Redis用于快速验证（600秒过期）
        try {
            echo "[sendEmailVerifyCode] 准备存入Redis...\n";
            $redis = Cache::get();
            $redis->set("email_verify_code:{$email}:{$verifyType}", $code, 600);
            echo "[sendEmailVerifyCode] Redis存储成功\n";
        } catch (\Exception $e) {
            echo "[sendEmailVerifyCode] Redis存储异常：" . $e->getMessage() . "\n";
            echo "[sendEmailVerifyCode] 异常堆栈：" . $e->getTraceAsString() . "\n";
            // Redis失败不影响主流程
        }

        // 7. 调用邮件服务发送验证码
        $result = \App\Helpers\EmailService::sendVerifyCode($email, $code, $verifyType);
        if (!$result['success']) {
            return $result['message'];
        }

        return '';
    }

    /**
     * 重置密码
     * @param array $data
     * @return string 返回错误消息，空字符串表示成功
     */
    public static function resetPassword(array $data): string
    {
        echo "[resetPassword] 开始执行，参数：" . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";

        // 1. 验证必填参数
        if (empty($data['contact_type'])) {
            echo "[resetPassword] 错误：联系方式类型不能为空\n";
            return '联系方式类型不能为空';
        }
        if (empty($data['contact_value'])) {
            echo "[resetPassword] 错误：联系方式不能为空\n";
            return '联系方式不能为空';
        }
        if (empty($data['verify_code'])) {
            echo "[resetPassword] 错误：验证码不能为空\n";
            return '验证码不能为空';
        }
        if (empty($data['new_password'])) {
            echo "[resetPassword] 错误：新密码不能为空\n";
            return '新密码不能为空';
        }

        $contactType = $data['contact_type'];
        $contactValue = trim($data['contact_value']);
        $verifyCode = trim($data['verify_code']);
        $newPassword = $data['new_password'];

        echo "[resetPassword] 处理后参数 - contactType: $contactType, contactValue: $contactValue, verifyCode: $verifyCode\n";

        // 手机号验证码查询用 E.164；邮箱与发码入库一致用小写（与 sendEmailVerifyCode 一致）
        $contactValueForVerify = $contactType === 'phone'
            ? (PhoneHelper::normalizeToE164($contactValue, $data['country_code'] ?? null) ?: $contactValue)
            : strtolower($contactValue);

        // 2. 验证密码长度
        if (mb_strlen($newPassword) < 6 || mb_strlen($newPassword) > 32) {
            echo "[resetPassword] 错误：密码长度必须在6-32位之间\n";
            return '密码长度必须在6-32位之间';
        }

        // 3. 验证验证码是否有效
        try {
            echo "[resetPassword] 验证验证码...\n";
            $codeRecord = Db::table('ext_verify_codes')
                ->where('contact_type', $contactType)
                ->where('contact_value', $contactValueForVerify)
                ->where('verify_type', 'reset_password')
                ->where('verify_code', $verifyCode)
                ->where('status', 1)
                ->where('is_used', false)
                ->where('is_expired', false)
                ->whereRaw('expired_at > CURRENT_TIMESTAMP')
                ->first();

            if (!$codeRecord) {
                echo "[resetPassword] 错误：验证码无效、已过期或已使用\n";
                return '验证码无效、已过期或已使用';
            }
            echo "[resetPassword] 验证码有效，ID: {$codeRecord->id}\n";
        } catch (\Exception $e) {
            echo "[resetPassword] 验证验证码异常：" . $e->getMessage() . "\n";
            echo "[resetPassword] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return '系统错误，请稍后重试';
        }

        // 4. 根据联系方式查询用户
        try {
            $query = Db::table('ext_users')->where('status', 1);
            if ($contactType === 'mail') {
                $query->where('bound_email', strtolower($contactValue));
            } else {
                $phoneValues = PhoneHelper::boundPhoneQueryValues($contactValue, $data['country_code'] ?? null);
                if ($phoneValues !== []) {
                    $query->whereIn('bound_phone', $phoneValues);
                } else {
                    $query->whereRaw('1=0');
                }
            }
            $extUser = $query->select('user_id')->first();

            if (!$extUser || empty($extUser->user_id)) {
                return '用户不存在';
            }

            $matrixId = $extUser->user_id;
        } catch (\Exception $e) {
            echo "[resetPassword] 查询用户异常：" . $e->getMessage() . "\n";
            echo "[resetPassword] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return '系统错误，请稍后重试';
        }

        // 5. 使用 Synapse 兼容的方式加密密码
        try {
            echo "[resetPassword] 加密密码...\n";
            $hashedPassword = Utils::bcrypt_hash($newPassword);
            echo "[resetPassword] 密码加密成功\n";
        } catch (\Exception $e) {
            echo "[resetPassword] 密码加密异常：" . $e->getMessage() . "\n";
            echo "[resetPassword] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return '密码加密失败';
        }

        // 6. 开启事务
        echo "[resetPassword] 开启事务...\n";
        Db::beginTransaction();
        try {
            // 更新 users 表的密码
            echo "[resetPassword] 更新 users 表密码...\n";
            $updated = Db::table('users')
                ->where('name', $matrixId)
                ->update(['password_hash' => $hashedPassword]);
            echo "[resetPassword] 更新 users 表结果：$updated\n";

            if ($updated !== 1) {
                Db::rollBack();
                echo "[resetPassword] 错误：更新密码失败\n";
                return '更新密码失败';
            }

            // 更新 ext_users 表的最后登录时间（使用数据库时间）
            echo "[resetPassword] 更新 ext_users 表...\n";
            Db::update(
                "UPDATE ext_users SET last_login_time = CURRENT_TIMESTAMP WHERE user_id = ?",
                [$matrixId]
            );

            // 标记验证码为已使用（使用数据库时间）
            echo "[resetPassword] 标记验证码为已使用...\n";
            Db::update(
                "UPDATE ext_verify_codes SET is_used = true, used_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$codeRecord->id]
            );

            Db::commit();
            echo "[resetPassword] 事务提交成功\n";
        } catch (\Exception $e) {
            Db::rollBack();
            echo "[resetPassword] 事务异常：" . $e->getMessage() . "\n";
            echo "[resetPassword] 异常代码：" . $e->getCode() . "\n";
            echo "[resetPassword] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return '重置密码失败：' . $e->getMessage();
        }

        echo "[resetPassword] 执行完成，返回成功\n";
        return ''; // 成功返回空字符串
    }

    /**
     * 用户注册（支持手机号和邮箱）
     * @param array $data
     * @return array ['error' => string, 'data' => array]
     */
    public static function register(array $data): array
    {
        echo "[register] 开始执行，参数：" . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";

        // 1. 验证必填参数
        if (empty($data['password'])) {
            echo "[register] 错误：密码不能为空\n";
            return ['error' => '密码不能为空', 'data' => []];
        }
        if (empty($data['verify_code'])) {
            echo "[register] 错误：验证码不能为空\n";
            return ['error' => '验证码不能为空', 'data' => []];
        }
        if (empty($data['contact_type'])) {
            echo "[register] 错误：联系方式类型不能为空\n";
            return ['error' => '联系方式类型不能为空', 'data' => []];
        }
        if (empty($data['contact_value'])) {
            echo "[register] 错误：联系方式不能为空\n";
            return ['error' => '联系方式不能为空', 'data' => []];
        }

        $password = $data['password'];
        $verifyCode = trim($data['verify_code']);
        $contactType = $data['contact_type']; // phone 或 mail
        $contactValue = trim($data['contact_value']);
        $deviceId = $data['device_id'] ?? '';
        $deviceName = $data['device_name'] ?? '';
        $homeServer = self::canonicalHomeServer();

        // 用户名：必填，归一化为小写作为 Matrix localpart（与 Synapse 一致）
        $username = trim((string)($data['username'] ?? ''));
        if ($username === '') {
            return ['error' => '用户名不能为空', 'data' => []];
        }
        $localpart = strtolower($username);
        // Matrix localpart 规范：仅允许小写字母、数字、. _ -
        if (strlen($localpart) < 2 || strlen($localpart) > 100) {
            return ['error' => '用户名长度为 2-100 个字符', 'data' => []];
        }
        if (!preg_match('/^[a-z0-9._-]+$/', $localpart)) {
            return ['error' => '用户名仅支持小写字母、数字、点、下划线、横线', 'data' => []];
        }

        echo "[register] 处理后参数 - contactType: $contactType, contactValue: $contactValue, localpart: $localpart\n";

        // 2. 验证密码长度
        if (mb_strlen($password) < 6 || mb_strlen($password) > 32) {
            echo "[register] 错误：密码长度必须在6-32位之间\n";
            return ['error' => '密码长度必须在6-32位之间', 'data' => []];
        }

        // 2.5 用户名唯一性：仅按 localpart 判重（忽略域名，单机场景）
        try {
            $existName = Db::table('users')
                ->whereRaw("name LIKE '@%:%' AND substring(name from 2 for position(':' in name) - 2) = ?", [$localpart])
                ->exists();
            if ($existName) {
                return ['error' => '用户名已被使用', 'data' => []];
            }
        } catch (\Exception $e) {
            echo "[register] 检查用户名唯一性异常：" . $e->getMessage() . "\n";
            return ['error' => '系统错误，请稍后重试', 'data' => []];
        }

        $matrixId = '@' . $localpart . ':' . $homeServer;

        // 手机号验证码查询用 E.164；邮箱与发码入库一致用小写（与 sendEmailVerifyCode 一致）
        $contactValueForVerify = $contactType === 'phone'
            ? (PhoneHelper::normalizeToE164($contactValue, $data['country_code'] ?? null) ?: $contactValue)
            : strtolower($contactValue);

        // 3. 验证验证码是否有效
        try {
            echo "[register] 验证验证码...\n";
            $codeRecord = Db::table('ext_verify_codes')
                ->where('contact_type', $contactType)
                ->where('contact_value', $contactValueForVerify)
                ->where('verify_type', 'register')
                ->where('verify_code', $verifyCode)
                ->where('status', 1)
                ->where('is_used', false)
                ->where('is_expired', false)
                ->whereRaw('expired_at > CURRENT_TIMESTAMP')
                ->first();

            if (!$codeRecord) {
                echo "[register] 错误：验证码无效、已过期或已使用\n";
                return ['error' => '验证码无效、已过期或已使用', 'data' => []];
            }
            echo "[register] 验证码有效，ID: {$codeRecord->id}\n";
        } catch (\Exception $e) {
            echo "[register] 验证验证码异常：" . $e->getMessage() . "\n";
            echo "[register] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return ['error' => '系统错误，请稍后重试', 'data' => []];
        }

        // 4. 检查手机号或邮箱是否已注册
        try {
            $fieldName = $contactType === 'mail' ? 'bound_email' : 'bound_phone';
            $query = Db::table('ext_users')->where('status', 1);
            if ($contactType === 'mail') {
                $query->where('bound_email', strtolower($contactValue));
            } else {
                $phoneValues = PhoneHelper::boundPhoneQueryValues($contactValue, $data['country_code'] ?? null);
                if ($phoneValues !== []) {
                    $query->whereIn('bound_phone', $phoneValues);
                } else {
                    $query->whereRaw('1=0');
                }
            }
            $existUser = $query->first();

            if ($existUser) {
                return ['error' => $contactType === 'mail' ? '该邮箱已注册' : '该手机号已注册', 'data' => []];
            }
        } catch (\Exception $e) {
            echo "[register] 检查注册异常：" . $e->getMessage() . "\n";
            echo "[register] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return ['error' => '系统错误，请稍后重试', 'data' => []];
        }

        // 5. 加密密码
        try {
            echo "[register] 加密密码...\n";
            $hashedPassword = Utils::bcrypt_hash($password);
            echo "[register] 密码加密成功\n";
        } catch (\Exception $e) {
            echo "[register] 密码加密异常：" . $e->getMessage() . "\n";
            echo "[register] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return ['error' => '密码加密失败', 'data' => []];
        }

        // 5.5 可选邀请码：邀请码 = 邀请人 ext_users.id，校验存在则写入邀请关系
        $referrerExtId = 0;
        $referrerUserId = '';
        $referrerVanityId = '';
        $inviteCodeRaw = trim((string)($data['invite_code'] ?? ''));
        if ($inviteCodeRaw !== '') {
            $referrerExtId = (int) $inviteCodeRaw;
            if ($referrerExtId <= 0) {
                return ['error' => '邀请码无效', 'data' => []];
            }
            try {
                $referrer = Db::table('ext_users')
                    ->where('id', $referrerExtId)
                    ->where('status', 1)
                    ->select('user_id')
                    ->first();
                if (!$referrer || empty($referrer->user_id)) {
                    return ['error' => '邀请码无效', 'data' => []];
                }
                $referrerUserId = (string) $referrer->user_id;
                $vanityRow = Db::table('ext_user_vanity_numbers')
                    ->where('user_id', $referrerExtId)
                    ->where('status', 1)
                    ->value('vanity_number');
                $referrerVanityId = $vanityRow !== null ? (string) $vanityRow : '';
                echo "[register] 邀请码有效，邀请人 ext_users.id: $referrerExtId, 靓号: $referrerVanityId\n";
            } catch (\Exception $e) {
                echo "[register] 校验邀请码异常：" . $e->getMessage() . "\n";
                return ['error' => '邀请码无效', 'data' => []];
            }
        }

        // 6. 开启事务
        echo "[register] 开启事务...\n";
        Db::beginTransaction();
        try {
            // 6.1 写入 ext_users 表（使用数据库时间）
            echo "[register] 写入 ext_users 表...\n";

            // 如果是手机号，归一化为 E.164 存储（多国家支持）
            $boundPhone = '';
            if ($contactType === 'phone') {
                $boundPhone = PhoneHelper::normalizeToE164($contactValue, $data['country_code'] ?? null);
                if ($boundPhone !== '') {
                    echo "[register] 手机号 E.164: $contactValue -> $boundPhone\n";
                }
            }

            // 性别：仅接受 male/female/other，未传或非法则默认为 unknown
            $genderRaw = trim((string)($data['gender'] ?? ''));
            $gender = in_array($genderRaw, ['male', 'female', 'other'], true) ? $genderRaw : 'unknown';

            $extUserRow = [
                'nickname' => $localpart,
                'bound_phone' => $boundPhone,
                'bound_email' => $contactType === 'mail' ? strtolower($contactValue) : '', // 邮箱小写化
                'bound_type' => $contactType,
                'bound_value' => $contactValue,
                'login_ip' => $data['request_ip'] ?? '',
                'device_id' => $deviceId,
                'device_model' => $deviceName,
                'gender' => $gender,
                'status' => 1
            ];
            if ($referrerExtId > 0) {
                $extUserRow['referrer_id'] = $referrerExtId;
                $extUserRow['referrer_user_id'] = $referrerUserId;
                $extUserRow['referrer_vanity_id'] = $referrerVanityId;
                $extUserRow['referrer_invite_code'] = $inviteCodeRaw;
            }
            $extUserId = Db::table('ext_users')->insertGetId($extUserRow);

            // 使用原生SQL更新时间字段为数据库时间
            Db::update(
                "UPDATE ext_users SET registration_time = CURRENT_TIMESTAMP, last_login_time = CURRENT_TIMESTAMP WHERE id = ?",
                [$extUserId]
            );
            echo "[register] ext_users ID: $extUserId, Matrix ID: $matrixId\n";

            if (!$extUserId) {
                Db::rollBack();
                echo "[register] 错误：创建用户失败\n";
                return ['error' => '创建用户失败', 'data' => []];
            }

            // 6.2 写入 users 表（Matrix ID 使用用户提交的 username 作为 localpart）
            echo "[register] 写入 users 表...\n";
            $inserted = Db::insert(
                "INSERT INTO users (name, password_hash, creation_ts) VALUES (?, ?, ?)",
                [$matrixId, $hashedPassword, time()]
            );
            echo "[register] users 表插入结果：" . ($inserted ? '成功' : '失败') . "\n";

            if (!$inserted) {
                Db::rollBack();
                echo "[register] 错误：创建 Synapse 用户失败\n";
                return ['error' => '创建 Synapse 用户失败', 'data' => []];
            }

            // 6.4 创建 profiles 表记录（Synapse 标准，displayname 与 localpart 一致）
            echo "[register] 写入 profiles 表...\n";
            $profileInserted = Db::insert(
                "INSERT INTO profiles (user_id, displayname, avatar_url, full_user_id) VALUES (?, ?, ?, ?)",
                [$localpart, $localpart, '', $matrixId]
            );
            echo "[register] profiles 表插入结果：" . ($profileInserted ? '成功' : '失败') . "\n";

            if (!$profileInserted) {
                Db::rollBack();
                echo "[register] 错误：创建用户资料失败\n";
                return ['error' => '创建用户资料失败', 'data' => []];
            }

            // 6.5 更新 ext_users 的 user_id
            echo "[register] 更新 ext_users 的 user_id...\n";
            Db::table('ext_users')
                ->where('id', $extUserId)
                ->update(['user_id' => $matrixId]);

            // 6.6 生成 access_token
            $accessToken = 'syt_' . base64_encode(random_bytes(24));
            echo "[register] 生成 access_token\n";

            // 6.7 如果没有提供 device_id，生成一个
            if (empty($deviceId)) {
                $deviceId = strtoupper(bin2hex(random_bytes(8)));
                echo "[register] 生成 device_id: $deviceId\n";
            }

            // 6.8 插入 access_token（让数据库序列自动生成ID）
            echo "[register] 插入 access_token...\n";
            self::insertAccessToken($matrixId, $deviceId, $accessToken);

            // 6.10 插入或更新 device 信息
            echo "[register] 插入/更新 devices 表...\n";
            Db::insert(
                "INSERT INTO devices (user_id, device_id, display_name, last_seen, ip) 
                VALUES (?, ?, ?, ?, ?) 
                ON CONFLICT (user_id, device_id) DO UPDATE SET 
                    display_name = EXCLUDED.display_name,
                    last_seen = EXCLUDED.last_seen,
                    ip = EXCLUDED.ip",
                [$matrixId, $deviceId, $deviceName, time() * 1000, $data['request_ip'] ?? '']
            );

            // 6.11 标记验证码为已使用（使用数据库时间）
            echo "[register] 标记验证码为已使用...\n";
            Db::update(
                "UPDATE ext_verify_codes SET is_used = true, used_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$codeRecord->id]
            );

            // 6.12 若有邀请人，则邀请人下级邀请数 +1
            if ($referrerExtId > 0) {
                Db::table('ext_users')->where('id', $referrerExtId)->increment('subordinate_referrals_count', 1);
                echo "[register] 已更新邀请人 subordinate_referrals_count\n";
            }

            Db::commit();
            echo "[register] 事务提交成功\n";

            // 7. 返回 Synapse 格式的响应数据
            echo "[register] 注册成功，返回数据\n";
            return [
                'error' => '',
                'data' => [
                    'user_id' => $matrixId,
                    'access_token' => $accessToken,
                    'home_server' => $homeServer,
                    'device_id' => $deviceId
                ]
            ];
        } catch (\Exception $e) {
            Db::rollBack();
            echo "[register] 事务异常：" . $e->getMessage() . "\n";
            echo "[register] 异常代码：" . $e->getCode() . "\n";
            echo "[register] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return ['error' => '注册失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 检查用户名称或手机号是否已注册（供注册页输入框实时校验）
     * 入参：keyword 为用户名称或手机号，后端根据格式自动判断。
     * 用户名判重按 localpart，与 MXID 域名无关；手机号按 ext_users.bound_phone。
     * @param string $keyword 用户名称（localpart）或手机号
     * @return array ['error' => string, 'exists' => bool, 'type' => 'username'|'phone'|null]
     */
    public static function checkUsernameOrPhoneExists(string $keyword): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return ['error' => '请输入用户名称或手机号', 'exists' => false, 'type' => null];
        }

        try {
            // 1. 手机号：支持多国家 E.164 或 country_code + 本地号
            $phoneValues = PhoneHelper::boundPhoneQueryValues($keyword, null);
            if ($phoneValues !== []) {
                $extUser = Db::table('ext_users')
                    ->where('status', 1)
                    ->whereIn('bound_phone', $phoneValues)
                    ->exists();
                return [
                    'error' => '',
                    'exists' => $extUser,
                    'type' => $extUser ? 'phone' : null,
                ];
            }

            // 2. 视为用户名（localpart）：仅按 localpart 判重，忽略 home_server
            $localpart = strtolower($keyword);
            if (strlen($localpart) < 2 || strlen($localpart) > 100) {
                return ['error' => '用户名长度为 2-100 个字符', 'exists' => false, 'type' => null];
            }
            if (!preg_match('/^[a-z0-9._-]+$/', $localpart)) {
                return ['error' => '用户名仅支持小写字母、数字、点、下划线、横线', 'exists' => false, 'type' => null];
            }
            $existName = Db::table('users')
                ->whereRaw("name LIKE '@%:%' AND substring(name from 2 for position(':' in name) - 2) = ?", [$localpart])
                ->exists();
            return [
                'error' => '',
                'exists' => $existName,
                'type' => $existName ? 'username' : null,
            ];
        } catch (\Exception $e) {
            return ['error' => '系统错误，请稍后重试', 'exists' => false, 'type' => null];
        }
    }

    /**
     * 根据 identifier 解析出 Matrix user_id（支持账号/靓号/手机号/邮箱）
     * @param string $identifier 账号、靓号、手机号或邮箱
     * @return array ['error' => string, 'matrix_id' => string|null, 'ext_user_id' => int|null]
     */
    public static function resolveIdentifierToUser(string $identifier): array
    {
        $homeServer = self::canonicalHomeServer();
        $identifier = trim($identifier);
        if ($identifier === '') {
            return ['error' => '请输入账号、靓号、手机号或邮箱', 'matrix_id' => null, 'ext_user_id' => null];
        }

        try {
            // 1. 靓号：纯数字（1-20位）
            if (preg_match('/^\d{1,20}$/', $identifier)) {
                $vanity = Db::table('ext_user_vanity_numbers')
                    ->join('ext_users', 'ext_user_vanity_numbers.user_id', '=', 'ext_users.id')
                    ->where('ext_user_vanity_numbers.vanity_number', $identifier)
                    ->where('ext_user_vanity_numbers.status', 1)
                    ->where('ext_users.status', 1)
                    ->select('ext_users.user_id', 'ext_users.id as ext_user_id')
                    ->first();
                if ($vanity && !empty($vanity->user_id)) {
                    return ['error' => '', 'matrix_id' => $vanity->user_id, 'ext_user_id' => (int)$vanity->ext_user_id];
                }
            }

            // 2. 手机号：多国家 E.164
            $phoneValues = PhoneHelper::boundPhoneQueryValues($identifier, null);
            if ($phoneValues !== []) {
                $extUser = Db::table('ext_users')
                    ->select('user_id', 'id')
                    ->whereIn('bound_phone', $phoneValues)
                    ->where('status', 1)
                    ->first();
                if ($extUser && !empty($extUser->user_id)) {
                    return ['error' => '', 'matrix_id' => $extUser->user_id, 'ext_user_id' => (int)$extUser->id];
                }
            }

            // 3. 邮箱
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $email = strtolower($identifier);
                $extUser = Db::table('ext_users')
                    ->select('user_id', 'id')
                    ->where('bound_email', $email)
                    ->where('status', 1)
                    ->first();
                if ($extUser && !empty($extUser->user_id)) {
                    return ['error' => '', 'matrix_id' => $extUser->user_id, 'ext_user_id' => (int)$extUser->id];
                }
            }

            // 4. 账号：Matrix ID (@xxx:domain) 或 localpart (u12345)
            $matrixId = $identifier;
            if (strpos($identifier, '@') === 0 && strpos($identifier, ':') !== false) {
                // 完整 Matrix ID
            } else {
                // localpart，补全为 @localpart:homeServer
                $matrixId = '@' . $identifier . ':' . $homeServer;
            }
            $user = Db::table('users')->where('name', $matrixId)->select('name')->first();
            if ($user) {
                $extUser = Db::table('ext_users')
                    ->select('user_id', 'id')
                    ->where('user_id', $user->name)
                    ->where('status', 1)
                    ->first();
                return [
                    'error' => '',
                    'matrix_id' => $user->name,
                    'ext_user_id' => $extUser ? (int)$extUser->id : null
                ];
            }

            return ['error' => '用户不存在', 'matrix_id' => null, 'ext_user_id' => null];
        } catch (\Exception $e) {
            return ['error' => '系统错误，请稍后重试', 'matrix_id' => null, 'ext_user_id' => null];
        }
    }

    /**
     * 用户登录（支持账号/靓号/手机号/邮箱 + 密码）
     * @param array $data
     * @return array ['error' => string, 'data' => array]
     */
    public static function login(array $data): array
    {
        // 1. 验证必填参数
        if (empty($data['password'])) {
            return ['error' => '密码不能为空', 'data' => []];
        }
        $identifier = trim((string)($data['identifier'] ?? $data['contact_value'] ?? ''));
        if ($identifier === '' && !empty($data['contact_type']) && !empty($data['contact_value'])) {
            $identifier = trim($data['contact_value']);
        }
        if ($identifier === '') {
            return ['error' => '请输入账号、靓号、手机号或邮箱', 'data' => []];
        }

        $password = $data['password'];
        $deviceId = $data['device_id'] ?? '';
        $deviceName = $data['device_name'] ?? '';
        $homeServer = self::canonicalHomeServer();

        // 2. 解析 identifier 得到 matrix_id
        $resolved = self::resolveIdentifierToUser($identifier);
        if ($resolved['error'] !== '') {
            return ['error' => $resolved['error'], 'data' => []];
        }
        $matrixId = $resolved['matrix_id'];
        $extUserId = $resolved['ext_user_id'];

        // 3. 从 users 表验证密码（bcrypt 优先，兼容旧 MD5 格式并透明升级）
        $needsPasswordUpgrade = false;
        try {
            $user = Db::table('users')
                ->select('password_hash')
                ->where('name', $matrixId)
                ->first();

            if (!$user) {
                return ['error' => '用户不存在', 'data' => []];
            }

            if (!Utils::verifyPasswordWithLegacyFallback($password, $user->password_hash)) {
                return ['error' => '密码错误', 'data' => []];
            }

            $needsPasswordUpgrade = Utils::isLegacyHash($user->password_hash);
        } catch (\Exception $e) {
            return ['error' => '系统错误，请稍后重试', 'data' => []];
        }

        // 4. 开启事务
        Db::beginTransaction();
        try {
            // 4.1 生成 access_token
            $accessToken = 'syt_' . base64_encode(random_bytes(24));

            // 4.2 如果没有提供 device_id，生成一个
            if (empty($deviceId)) {
                $deviceId = strtoupper(bin2hex(random_bytes(8)));
            }

            // 4.3 插入 access_token
            self::insertAccessToken($matrixId, $deviceId, $accessToken);

            // 4.4 更新或插入 device 信息
            Db::insert(
                "INSERT INTO devices (user_id, device_id, display_name, last_seen, ip) 
                VALUES (?, ?, ?, ?, ?) 
                ON CONFLICT (user_id, device_id) DO UPDATE SET 
                    display_name = EXCLUDED.display_name,
                    last_seen = EXCLUDED.last_seen,
                    ip = EXCLUDED.ip",
                [$matrixId, $deviceId, $deviceName, time() * 1000, $data['request_ip'] ?? '']
            );

            // 5.5 透明升级旧 MD5 密码为 bcrypt
            if ($needsPasswordUpgrade) {
                try {
                    $newHash = Utils::bcrypt_hash($password);
                    Db::table('users')
                        ->where('name', $matrixId)
                        ->update(['password_hash' => $newHash]);
                } catch (\Throwable $e) {
                    error_log('[login] MD5→bcrypt upgrade failed for ' . $matrixId . ': ' . $e->getMessage());
                }
            }

            // 5.6 更新 ext_users 的登录信息（使用数据库时间）
            if ($extUserId) {
                Db::update(
                    "UPDATE ext_users SET last_login_time = CURRENT_TIMESTAMP, login_ip = ?, device_id = ?, device_model = ? WHERE id = ?",
                    [$data['request_ip'] ?? '', $deviceId, $deviceName, $extUserId]
                );
            }

            Db::commit();

            return [
                'error' => '',
                'data' => [
                    'user_id' => $matrixId,
                    'access_token' => $accessToken,
                    'home_server' => $homeServer,
                    'device_id' => $deviceId
                ]
            ];
        } catch (\Exception $e) {
            Db::rollBack();
            return ['error' => '登录失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 用户退出登录（单设备）
     * @param array $data
     * @return array ['error' => string, 'data' => array]
     */
    public static function logout(array $data): array
    {
        echo "[logout] 开始执行，参数：" . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";

        // 1. 验证必填参数
        if (empty($data['access_token'])) {
            echo "[logout] 错误：Access token 不能为空\n";
            return ['error' => 'Access token 不能为空', 'data' => []];
        }

        $accessToken = trim($data['access_token']);
        echo "[logout] access_token: $accessToken\n";

        // 2. 根据 access_token 查询用户和设备信息
        try {
            echo "[logout] 查询 access_token 信息...\n";
            $tokenRecord = Db::table('access_tokens')
                ->select('user_id', 'device_id')
                ->where('token', $accessToken)
                ->first();

            if (!$tokenRecord) {
                echo "[logout] 错误：Invalid access token\n";
                return ['error' => 'Invalid access token', 'data' => []];
            }

            $userId = $tokenRecord->user_id;
            $deviceId = $tokenRecord->device_id;
            echo "[logout] 找到 token，user_id: $userId, device_id: $deviceId\n";
        } catch (\Exception $e) {
            echo "[logout] 查询 token 异常：" . $e->getMessage() . "\n";
            echo "[logout] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return ['error' => '系统错误，请稍后重试', 'data' => []];
        }

        // 3. 开启事务
        echo "[logout] 开启事务...\n";
        Db::beginTransaction();
        try {
            if (!empty($deviceId)) {
                // 3.1 如果 token 关联了设备，删除该设备（会级联删除相关 tokens）
                echo "[logout] 删除设备和相关 tokens...\n";
                Db::delete("DELETE FROM devices WHERE user_id = ? AND device_id = ?", [$userId, $deviceId]);

                // 删除该设备的所有 access_tokens
                Db::delete("DELETE FROM access_tokens WHERE user_id = ? AND device_id = ?", [$userId, $deviceId]);
                echo "[logout] 已删除设备和 tokens\n";
            } else {
                // 3.2 如果 token 没有关联设备，只删除该 access_token
                echo "[logout] 只删除当前 access_token...\n";
                Db::delete("DELETE FROM access_tokens WHERE token = ?", [$accessToken]);
                echo "[logout] 已删除 access_token\n";
            }

            Db::commit();
            echo "[logout] 事务提交成功\n";

            // 4. 返回空对象，符合 Synapse 标准
            echo "[logout] 退出登录成功\n";
            return ['error' => '', 'data' => []];
        } catch (\Exception $e) {
            Db::rollBack();
            echo "[logout] 事务异常：" . $e->getMessage() . "\n";
            echo "[logout] 异常代码：" . $e->getCode() . "\n";
            echo "[logout] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return ['error' => '退出登录失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 用户退出所有设备
     * @param array $data
     * @return array ['error' => string, 'data' => array]
     */
    public static function logoutAll(array $data): array
    {
        echo "[logoutAll] 开始执行，参数：" . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";

        // 1. 验证必填参数
        if (empty($data['access_token'])) {
            echo "[logoutAll] 错误：Access token 不能为空\n";
            return ['error' => 'Access token 不能为空', 'data' => []];
        }

        $accessToken = trim($data['access_token']);
        echo "[logoutAll] access_token: $accessToken\n";

        // 2. 根据 access_token 查询用户信息
        try {
            echo "[logoutAll] 查询 access_token 信息...\n";
            $tokenRecord = Db::table('access_tokens')
                ->select('user_id')
                ->where('token', $accessToken)
                ->first();

            if (!$tokenRecord) {
                echo "[logoutAll] 错误：Invalid access token\n";
                return ['error' => 'Invalid access token', 'data' => []];
            }

            $userId = $tokenRecord->user_id;
            echo "[logoutAll] 找到用户：$userId\n";
        } catch (\Exception $e) {
            echo "[logoutAll] 查询 token 异常：" . $e->getMessage() . "\n";
            echo "[logoutAll] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return ['error' => '系统错误，请稍后重试', 'data' => []];
        }

        // 3. 开启事务
        echo "[logoutAll] 开启事务...\n";
        Db::beginTransaction();
        try {
            // 3.1 删除该用户的所有设备
            echo "[logoutAll] 删除所有设备...\n";
            Db::delete("DELETE FROM devices WHERE user_id = ?", [$userId]);

            // 3.2 删除该用户的所有 access_tokens
            echo "[logoutAll] 删除所有 access_tokens...\n";
            Db::delete("DELETE FROM access_tokens WHERE user_id = ?", [$userId]);

            Db::commit();
            echo "[logoutAll] 事务提交成功\n";

            // 4. 返回空对象，符合 Synapse 标准
            echo "[logoutAll] 退出所有设备成功\n";
            return ['error' => '', 'data' => []];
        } catch (\Exception $e) {
            Db::rollBack();
            echo "[logoutAll] 事务异常：" . $e->getMessage() . "\n";
            echo "[logoutAll] 异常代码：" . $e->getCode() . "\n";
            echo "[logoutAll] 异常堆栈：" . $e->getTraceAsString() . "\n";
            return ['error' => '退出登录失败：' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * 写入 access_tokens。
     *
     * Synapse 使用显式 id（不推进 PostgreSQL 序列），web-api 用 SERIAL；共库时序列会落后于 MAX(id)。
     * 每次插入前将序列对齐到 MAX(id)，避免 nextval 与 Synapse 已占用的 id 冲突。
     * 若仍撞主键（极端并发），再 setval 后重试一次。
     */
    private static function insertAccessToken(string $userId, string $deviceId, string $token): void
    {
        Db::statement(
            "SELECT setval(pg_get_serial_sequence('access_tokens', 'id'), COALESCE((SELECT MAX(id) FROM access_tokens), 1))"
        );
        try {
            Db::insert(
                'INSERT INTO access_tokens (user_id, device_id, token) VALUES (?, ?, ?)',
                [$userId, $deviceId, $token]
            );
        } catch (\Throwable $e) {
            if (! self::isAccessTokensPkeyDuplicate($e)) {
                throw $e;
            }
            Db::statement(
                "SELECT setval(pg_get_serial_sequence('access_tokens', 'id'), COALESCE((SELECT MAX(id) FROM access_tokens), 1))"
            );
            Db::insert(
                'INSERT INTO access_tokens (user_id, device_id, token) VALUES (?, ?, ?)',
                [$userId, $deviceId, $token]
            );
        }
    }

    private static function isAccessTokensPkeyDuplicate(\Throwable $e): bool
    {
        for ($depth = 0; $depth < 8 && $e !== null; ++$depth) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'access_tokens_pkey') && str_contains($msg, '23505')) {
                return true;
            }
            $prev = $e->getPrevious();
            $e = $prev instanceof \Throwable ? $prev : null;
        }

        return false;
    }
}
