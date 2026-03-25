<?php
declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Frontend\Dao\Users as UsersDao;
use App\Http\Frontend\Dao\VipDao;

#[Controller(prefix: "api/v1/vip")]
class VipController extends FrontendController
{
    /**
     * 兑换会员等级：扣积分、写账变、更新会员等级与到期时间。
     * 仅支持 10001 初级、10002 高级、10003 超级，且目标等级 is_exchangeable=true。
     * 与当前等级相同时直接返回成功不扣费。
     */
    #[PostMapping(path: "exchangeMemberLevel")]
    public function exchangeMemberLevel(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $initResult = UsersDao::initializeExtUser($auth['user_id']);
        if ($initResult['error'] !== '') {
            return self::jsonErr($initResult['error']);
        }

        $extUserId = (int)$initResult['ext_user_id'];
        $levelId = (int)($request->input('level_id') ?? 0);

        if ($levelId <= 0) {
            return self::jsonErr('请选择要兑换的会员等级');
        }

        $result = VipDao::exchangeMemberLevel($extUserId, $levelId);
        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }

        // 当前已是目标等级时未扣费、无账变，返回明确提示避免用户误以为已扣积分
        if (!empty($result['same_level'])) {
            return self::jsonOk('您当前已是该会员等级，无需兑换');
        }

        return self::jsonOk('兑换成功');
    }
}
