<?php
declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use App\Http\Frontend\Dao\ConfigsDao;

/**
 * C 端配置接口：用户等级、身份徽章、诚信徽章列表
 */
#[Controller(prefix: "api/v1/configs")]
class ConfigsController extends FrontendController
{
    /**
     * 用户等级列表（status=1）
     */
    #[GetMapping(path: "levels")]
    public function levels(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $levels = ConfigsDao::getLevels();
        return self::jsonResult(['levels' => $levels]);
    }

    /**
     * 身份徽章列表（ext_user_badges 表 type=1），直接返回表数据
     */
    #[GetMapping(path: "identityBadges")]
    public function identityBadges(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $badges = ConfigsDao::getIdentityBadges();
        return self::jsonResult(['badges' => $badges]);
    }

    /**
     * 身份徽章列表（同 identityBadges），路径为单数形式 /configs/identityBadge
     */
    #[GetMapping(path: "identityBadge")]
    public function identityBadge(RequestInterface $request): mixed
    {
        return $this->identityBadges($request);
    }

    /**
     * 诚信徽章列表（ext_user_badges 表 type=2），直接返回表数据
     */
    #[GetMapping(path: "creditBadges")]
    public function creditBadges(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $badges = ConfigsDao::getCreditBadges();
        return self::jsonResult(['badges' => $badges]);
    }

    /**
     * 诚信徽章列表（同 creditBadges），路径为单数形式 /configs/creditBadge
     */
    #[GetMapping(path: "creditBadge")]
    public function creditBadge(RequestInterface $request): mixed
    {
        return $this->creditBadges($request);
    }

    /**
     * 圈子徽章列表（ext_circle_badges 表 status=1），供 C 端根据 profile 的 circle_badge_id 取对应图标
     */
    #[GetMapping(path: "circleBadges")]
    public function circleBadges(RequestInterface $request): mixed
    {
        $auth = $this->getAuthenticatedUser($request);
        if ($auth['error'] !== '') {
            return self::jsonErr($auth['error']);
        }

        $badges = ConfigsDao::getCircleBadges();
        return self::jsonResult(['badges' => $badges]);
    }
}
