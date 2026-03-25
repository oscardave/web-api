<?php
declare(strict_types=1);

namespace App\Common;

use Hyperf\Utils\Str;
use Hyperf\HttpServer\Contract\RequestInterface;

class UploadResult
{
    /**
     * @var int
     */
    public int $fileSize = 0;

    /**
     * @var string
     */
    public string $fileName = '';

    /**
     * @var string
     */
    public string $filePath = '';

    /**
     * @var bool
     */
    public bool $success = false;

    /**
     * @var string
     */
    public string $error = '';

    /**
     * @var string
     */
    public string $tmpFile = '';

    /**
     * @param string $message
     * @return UploadResult
     */
    public static function error(string $message): UploadResult
    {
        $result = new UploadResult();
        $result->error = $message;
        return $result;
    }

    /**
     * @return bool
     */
    public function isOk(): bool
    {
        return $this->success;
    }
}