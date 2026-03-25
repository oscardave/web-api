<?php
declare(strict_types=1);

namespace App\Helpers;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\ApplicationContext;
use GuzzleHttp\Exception\GuzzleException;

class SmsService
{
    /**
     * 发送短信（多国家支持）
     * @param string $phone 手机号，建议为 E.164 数字串（如 8613800138000、14155551234），或中国 11 位
     * @param string $message 短信内容
     * @return array ['success' => bool, 'message' => string, 'data' => array|null]
     */
    public static function sendSms(string $phone, string $message): array
    {
        try {
            // 1. 读取配置
            $container = ApplicationContext::getContainer();
            $config = $container->get(ConfigInterface::class);
            $mock = $config->get('sms.mock', true);

            // 伪模式：不调用真实 API，直接返回成功（验证码已由调用方入库/Redis）
            if ($mock) {
                return [
                    'success' => true,
                    'message' => 'ok',
                    'data' => null
                ];
            }

            $apiUrl = $config->get('sms.api_url', 'https://app.gotonesms.net/api/v3/sms/send');
            $apiToken = $config->get('sms.api_token', '');
            $senderId = $config->get('sms.sender_id', 'YourName');

            if (empty($apiToken)) {
                return [
                    'success' => false,
                    'message' => '短信服务配置错误：API Token 未配置',
                    'data' => null
                ];
            }

            // 2. 转换手机号格式（中国手机号加86前缀）
            $internationalPhone = self::convertToInternationalFormat($phone);
            if (empty($internationalPhone)) {
                return [
                    'success' => false,
                    'message' => '手机号格式不正确',
                    'data' => null
                ];
            }

            // 3. 构建请求参数（recipient 使用 E.164 格式，如 +8613800138000）
            $recipient = $internationalPhone;
            if (strpos($recipient, '+') !== 0 && $recipient !== '') {
                $recipient = '+' . $recipient;
            }
            $requestData = [
                'recipient' => $recipient,
                'sender_id' => $senderId,
                'type' => 'plain',
                'message' => $message
            ];

            // 4. 构建请求头
            $headers = [
                'Authorization' => 'Bearer ' . $apiToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ];

            // 5. 创建 HTTP 客户端并发送请求
            $clientFactory = $container->get(ClientFactory::class);
            $client = $clientFactory->create();
            
            $response = $client->post($apiUrl, [
                'headers' => $headers,
                'json' => $requestData,
                'timeout' => 30
            ]);
            
            // 6. 解析响应
            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'message' => '短信服务响应解析失败：' . json_last_error_msg(),
                    'data' => ['raw_response' => $responseBody]
                ];
            }

            // 7. 检查响应状态
            if (isset($responseData['status']) && $responseData['status'] === 'success') {
                return [
                    'success' => true,
                    'message' => '短信发送成功',
                    'data' => $responseData['data'] ?? null
                ];
            } else {
                $errorMessage = $responseData['message'] ?? '短信发送失败';
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'data' => $responseData
                ];
            }

        } catch (GuzzleException $e) {
            return [
                'success' => false,
                'message' => '短信发送请求失败：' . $e->getMessage(),
                'data' => null
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '短信发送异常：' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * 将手机号转换为 E.164 数字串（多国家支持）
     * 支持：中国 11 位、86+11 位、其他 10-15 位 E.164
     * @param string $phone 手机号（可为 E.164 或中国 11 位）
     * @return string E.164 数字串，格式错误返回空字符串
     */
    private static function convertToInternationalFormat(string $phone): string
    {
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        $phone = ltrim($phone, '+');
        if (!preg_match('/^\d+$/', $phone)) {
            return '';
        }
        if (preg_match('/^86\d{11}$/', $phone)) {
            return $phone;
        }
        if (preg_match('/^1[3-9]\d{9}$/', $phone)) {
            return '86' . $phone;
        }
        if (strlen($phone) >= 10 && strlen($phone) <= 15) {
            return $phone;
        }
        return '';
    }
}

