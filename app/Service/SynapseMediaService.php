<?php
declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Synapse 媒体上传服务：将本地文件上传到 Synapse 媒体库，返回 mxc:// URL。
 * 用于头像等需同步到 Matrix（1v1 聊天、群聊、通讯录）的场景。
 */
class SynapseMediaService
{
    /**
     * 上传本地文件到 Synapse 媒体库。
     *
     * @param string $localFilePath 本地文件绝对路径
     * @param string $mimeType 文件 MIME 类型，如 image/jpeg
     * @return array{error: string, mxc_url: string|null} 成功时 mxc_url 为 mxc://server/media_id
     */
    public static function uploadFile(string $localFilePath, string $mimeType = 'image/jpeg'): array
    {
        $baseUrl = trim(rtrim((string) env('SYNAPSE_ADMIN_API_URL', ''), '/'));
        $token = (string) env('SYNAPSE_ADMIN_ACCESS_TOKEN', '');
        if ($baseUrl === '' || $token === '') {
            return ['error' => '未配置 SYNAPSE_ADMIN_API_URL 或 SYNAPSE_ADMIN_ACCESS_TOKEN', 'mxc_url' => null];
        }

        if (!is_file($localFilePath) || !is_readable($localFilePath)) {
            return ['error' => '文件不存在或不可读: ' . $localFilePath, 'mxc_url' => null];
        }

        $uploadUrl = $baseUrl . '/_matrix/media/v3/upload';
        $fileContent = file_get_contents($localFilePath);
        if ($fileContent === false) {
            return ['error' => '无法读取文件内容', 'mxc_url' => null];
        }

        try {
            $client = new Client([
                'timeout' => 30,
                'verify' => true,
            ]);
            $response = $client->post($uploadUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => $mimeType,
                ],
                'body' => $fileContent,
            ]);

            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();
            $json = json_decode($body, true);

            if ($statusCode >= 200 && $statusCode < 300 && isset($json['content_uri'])) {
                $mxc = $json['content_uri'];
                if (str_starts_with($mxc, 'mxc://')) {
                    return ['error' => '', 'mxc_url' => $mxc];
                }
            }

            $errMsg = $json['error'] ?? $body ?: 'HTTP ' . $statusCode;
            return ['error' => 'Synapse 媒体上传失败: ' . $errMsg, 'mxc_url' => null];
        } catch (GuzzleException $e) {
            return ['error' => 'Synapse 媒体上传请求失败: ' . $e->getMessage(), 'mxc_url' => null];
        }
    }

    /**
     * 根据存储路径解析为本地文件绝对路径。
     * 支持 /uploads/YYYYMM/xxx 及 /touxiang/xxx 等路径。
     *
     * @param string $path 存储路径，如 /uploads/202501/xxx.jpg
     * @return string|null 本地文件路径，无法解析时返回 null
     */
    public static function resolvePathToFile(string $path): ?string
    {
        $path = trim($path);
        if ($path === '' || $path[0] !== '/') {
            return null;
        }

        if (str_contains($path, '..')) {
            return null;
        }

        $uploadDir = trim((string) env('UPLOAD_DIR', ''));
        if ($uploadDir === '') {
            $basePathDir = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
            $uploadDir = $basePathDir . '/public/uploads';
        }
        $uploadDir = rtrim($uploadDir, '/');
        $basePath = rtrim(dirname($uploadDir), '/');

        $filePath = realpath($basePath . $path);
        if ($filePath === false) {
            return null;
        }

        if (!str_starts_with($filePath, $basePath . '/')) {
            return null;
        }

        return is_file($filePath) && is_readable($filePath) ? $filePath : null;
    }

    /**
     * 根据文件扩展名获取 MIME 类型。
     */
    public static function getMimeFromPath(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
        ];
        return $map[$ext] ?? 'image/jpeg';
    }
}
