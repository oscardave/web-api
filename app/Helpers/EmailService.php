<?php

declare(strict_types=1);

namespace App\Helpers;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\ApplicationContext;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

class EmailService
{
    /**
     * 发送验证码邮件
     * @param string $to 收件人邮箱
     * @param string $code 验证码
     * @param string $verifyType 验证类型（register, login, reset_password 等）
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendVerifyCode(string $to, string $code, string $verifyType): array
    {
        $messages = [
            'register' => "您的注册验证码是 {$code}，5分钟内有效，请勿泄露给他人。",
            'login' => "您的登录验证码是 {$code}，5分钟内有效，请勿泄露给他人。",
            'reset_password' => "您的找回密码验证码是 {$code}，5分钟内有效，请勿泄露给他人。",
        ];
        $body = $messages[$verifyType] ?? "您的验证码是 {$code}，5分钟内有效，请勿泄露给他人。";
        $subject = '验证码通知';

        return self::send($to, $subject, $body);
    }

    /**
     * 发送邮件
     * @param string $to 收件人邮箱
     * @param string $subject 主题
     * @param string $body 正文（纯文本）
     * @return array ['success' => bool, 'message' => string]
     */
    public static function send(string $to, string $subject, string $body): array
    {
        try {
            $container = ApplicationContext::getContainer();
            $config = $container->get(ConfigInterface::class);
            $mock = $config->get('mail.mock', true);

            if ($mock) {
                return ['success' => true, 'message' => 'ok'];
            }

            $host = $config->get('mail.host', '');
            $port = $config->get('mail.port', 465);
            $username = $config->get('mail.username', '');
            $password = $config->get('mail.password', '');
            $fromAddress = $config->get('mail.from_address', $username);
            $fromName = $config->get('mail.from_name', '验证码');
            $encryption = $config->get('mail.encryption', 'ssl');

            if (empty($host) || empty($username) || empty($password)) {
                return [
                    'success' => false,
                    'message' => '邮件服务配置错误：SMTP 未完整配置',
                ];
            }

            $mail = new PHPMailer(true);
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Encoding = PHPMailer::ENCODING_BASE64;
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->Port = $port;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password;
            $mail->SMTPSecure = $encryption === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

            $mail->setFrom($fromAddress, $fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->isHTML(false);

            $mail->send();

            return ['success' => true, 'message' => '邮件发送成功'];
        } catch (PHPMailerException $e) {
            return [
                'success' => false,
                'message' => '邮件发送失败：' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '邮件发送异常：' . $e->getMessage(),
            ];
        }
    }
}
