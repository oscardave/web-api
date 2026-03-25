<?php
declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Common\Uploader;
use App\Http\Frontend\Dao\Users as UsersDao;

/**
 * 前台上传（C 端），逻辑与后台上传一致，需登录态
 */
#[Controller(prefix: "api/v1/upload")]
class UploadController extends FrontendController
{
    /**
     * 上传图片（通用）
     * POST multipart/form-data，字段名: file
     * 仅允许图片类型（png/jpeg/jpg/gif/bmp/webp），最大约 5MB，依据文件内容检测 MIME
     * 仅落盘并返回 url，不更新用户头像；若需用作头像请调 upload/avatar。
     */
    #[PostMapping(path: "image")]
    public function image(RequestInterface $request)
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $field = (string)($request->input('field', 'file'));
        $result = Uploader::upload($request, $field, true);

        if (!$result->isOk()) {
            return self::jsonErr($result->error ?: '上传失败');
        }

        return self::jsonArray([
            'url'      => $result->fileName,
            'fileName' => $result->fileName,
            'fileSize' => $result->fileSize,
        ]);
    }

    /**
     * 上传头像并直接生效（前台上传头像后无需再调保存接口）
     *
     * 流程：1) 上传图片落盘 2) 将返回的 url 设为当前用户头像并同步多表与 Synapse。
     * 效果与后台「上传图片后点击保存」一致，由 UserExtDao::updateProfile 统一完成：
     * - 更新 ext_users.avatar_url、profiles.avatar_url
     * - 更新 ext_circle_users、ext_group_users、ext_circle_content、ext_circle_notice、
     *   ext_circle_invite、circle_content_report、ext_user_notes 等业务表头像字段
     * - 若非 mxc 则先上传到 Synapse 媒体库，再 PUT Synapse Admin API 更新 room_memberships/user_directory
     *
     * 排查时可查：UsersDao::setAvatar -> UserExtDao::updateProfile；Synapse 失败仅打日志，业务表已更新。
     */
    #[PostMapping(path: "avatar")]
    public function avatar(RequestInterface $request)
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $field = (string)($request->input('field', 'file'));
        $result = Uploader::upload($request, $field, true);

        if (!$result->isOk()) {
            return self::jsonErr($result->error ?: '上传失败');
        }

        // 前台上传头像直接生效：与后台保存头像走同一套逻辑（多表 + Synapse），便于排查见 UserExtDao::updateProfile
        $setResult = UsersDao::setAvatar($auth['user_id'], $result->fileName);
        if ($setResult['error'] !== '') {
            return self::jsonErr($setResult['error']);
        }

        return self::jsonArray([
            'url'      => $result->fileName,
            'fileName' => $result->fileName,
            'fileSize' => $result->fileSize,
        ]);
    }
}
