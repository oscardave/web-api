<?php
declare(strict_types=1);

namespace App\Common;

use Hyperf\HttpServer\Contract\RequestInterface;

class Uploader
{
    /** 默认最大 约 20MB */
    public const FILE_UPLOAD_MAX = 20048000;

    /** 仅图片时最大 约 5MB */
    public const FILE_UPLOAD_IMAGE_MAX = 5242880;

    /** 允许的扩展名（去重） */
    public const ALLOW_TYPES = [
        'png',
        'jpeg',
        'jpg',
        'bmp',
        'gif',
        'mp4',
    ];

    /** 仅图片扩展名 */
    public const IMAGE_TYPES = [
        'png',
        'jpeg',
        'jpg',
        'bmp',
        'gif',
        'webp',
    ];

    /** 允许的 MIME 与保存用扩展名映射（服务端根据内容检测，不信任客户端） */
    private const MIME_TO_EXT = [
        'image/png'   => 'png',
        'image/jpeg'  => 'jpg',
        'image/jpg'   => 'jpg',
        'image/gif'   => 'gif',
        'image/bmp'   => 'bmp',
        'image/webp'  => 'webp',
        'video/mp4'   => 'mp4',
    ];

    /**
     * 上传文件（通用）
     * @param RequestInterface $request
     * @param string $field 表单字段名
     * @param bool $imageOnly 是否仅允许图片（不含 mp4）
     * @return UploadResult
     */
    public static function upload(RequestInterface $request, string $field = 'file', bool $imageOnly = false): UploadResult
    {
        if (!$request->hasFile($field)) {
            return UploadResult::error('无法获取上传文件信息');
        }

        $file = $request->file($field);
        if (!$file || !$file->isValid()) {
            return UploadResult::error('上传文件无效');
        }

        $fileSize = $file->getSize();
        $maxSize = $imageOnly ? self::FILE_UPLOAD_IMAGE_MAX : self::FILE_UPLOAD_MAX;
        if (!$fileSize || $fileSize > $maxSize) {
            return UploadResult::error('上传文件大小超出限制');
        }

        $tmpPath = $file->getRealPath() ?: $file->getPath();
        if (!$tmpPath || !is_readable($tmpPath)) {
            return UploadResult::error('无法读取上传文件');
        }

        $detectedMime = self::detectMimeType($tmpPath);
        if ($detectedMime === '') {
            return UploadResult::error('无法识别文件类型');
        }

        if (!isset(self::MIME_TO_EXT[$detectedMime])) {
            return UploadResult::error('不允许的文件类型: ' . $detectedMime);
        }

        $fileExt = self::MIME_TO_EXT[$detectedMime];
        $allowedTypes = $imageOnly ? self::IMAGE_TYPES : self::ALLOW_TYPES;
        if (!in_array($fileExt, $allowedTypes, true)) {
            return UploadResult::error('不允许的文件类型');
        }

        $uploadDir = trim((string)env('UPLOAD_DIR', ''));
        if ($uploadDir === '') {
            $uploadDir = BASE_PATH . '/public/uploads';
        }
        $dir = realpath($uploadDir);
        if ($dir === false) {
            $dir = $uploadDir;
            if (!is_dir($dir)) {
                if (!@mkdir($dir, 0755, true)) {
                    return UploadResult::error('上传目录不可用');
                }
            }
        }

        $month = date('Ym');
        $monthDir = $dir . DIRECTORY_SEPARATOR . $month;
        if (!is_dir($monthDir)) {
            if (!@mkdir($monthDir, 0755, true)) {
                return UploadResult::error('上传目录不可用');
            }
        }

        $fileName = date('YmdHis') . 'T' . Utils::randString(4) . '.' . $fileExt;
        $moveToFile = $monthDir . DIRECTORY_SEPARATOR . $fileName;

        $file->moveTo($moveToFile);
        if (!$file->isMoved()) {
            return UploadResult::error('文件保存失败');
        }

        $uploadFile = '/uploads/' . $month . '/' . $fileName;

        $result = new UploadResult();
        $result->fileName = $uploadFile;
        $result->fileSize = $fileSize;
        $result->filePath = $moveToFile;
        $result->tmpFile = $tmpPath;
        $result->success = true;
        return $result;
    }

    /**
     * 根据文件内容检测 MIME（不信任客户端）
     */
    private static function detectMimeType(string $path): string
    {
        if (!extension_loaded('fileinfo')) {
            return '';
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return '';
        }
        $mime = finfo_file($finfo, $path);
        return is_string($mime) ? $mime : '';
    }
}
