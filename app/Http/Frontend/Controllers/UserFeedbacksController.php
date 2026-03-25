<?php 
declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Frontend\Dao\UserFeedbacks as UserFeedbacksDao;

#[Controller(prefix: "api/v1/feedbacks")]
class UserFeedbacksController extends FrontendController
{
    /**
     * 提交反馈（投诉或建议）
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[PostMapping(path: "submit")]
    public function submit(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }
        
        $data = $request->all();
        
        // 验证必填参数：type, content, images
        if (!isset($data['type'])) {
            return self::jsonErr('Missing key \'type\'');
        }
        
        if (!isset($data['content'])) {
            return self::jsonErr('Missing key \'content\'');
        }
        
        if (!isset($data['images'])) {
            return self::jsonErr('Missing key \'images\'');
        }
        
        // 获取必填参数
        $type = (int)($data['type'] ?? 0);
        $content = $data['content'] ?? '';
        $images = $data['images'] ?? '[]';
        
        // 如果 images 是数组，转换为 JSON 字符串
        if (is_array($images)) {
            $images = json_encode($images, JSON_UNESCAPED_UNICODE);
        }
        
        // 获取可选参数
        $targetType = $data['target_type'] ?? 'other';
        $targetId = (int)($data['target_id'] ?? 0);
        $sourceTag = $data['source_tag'] ?? '';
        
        // 调用 DAO 提交反馈
        $result = UserFeedbacksDao::submitFeedback(
            $auth['user_id'],
            $type,
            $content,
            $images,
            $targetType,
            $targetId,
            $sourceTag
        );
        
        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }
        
        return self::jsonArray($result['data'], '反馈提交成功');
    }
}

