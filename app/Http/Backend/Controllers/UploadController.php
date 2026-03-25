<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Common\Uploader;

/**
 * 后台通用上传（如图片）
 */
#[Controller(prefix: "/ht/v1/upload")]
class UploadController extends BackendController
{
    /**
     * 上传图片
     * POST multipart/form-data，字段名: file
     * 仅允许图片类型（png/jpeg/jpg/gif/bmp/webp），最大约 5MB，依据文件内容检测 MIME
     */
    #[PostMapping(path: "image")]
    public function image(RequestInterface $request)
    {
        $field = (string)($request->input('field', 'file'));
        error_log('[UploadController] field=' . $field . ', hasFile(file)=' . ($request->hasFile('file') ? '1' : '0') . ', hasFile(' . $field . ')=' . ($request->hasFile($field) ? '1' : '0'));
        error_log('[UploadController] Content-Type: ' . ($request->getHeaderLine('content-type') ?? '(empty)'));
        error_log('[UploadController] Content-Length: ' . ($request->getHeaderLine('content-length') ?? '(empty)'));
        $result = Uploader::upload($request, $field, true);

        if (!$result->isOk()) {
            return $this->responseError($result->error ?: '上传失败');
        }

        return $this->responseData([
            'url'      => $result->fileName,
            'fileName' => $result->fileName,
            'fileSize' => $result->fileSize,
        ]);
    }
}
