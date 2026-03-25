<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Common\Cache;
use App\Model\Menu;
use App\Model\AdminLog;
use App\Model\Channel;
use App\Caches\RolesCache;
use Hyperf\DbConnection\Db;

class Helper
{
    /**
     * @param int $level
     * @return string
     */
    public static function menuLevel(int $level): string
    {
        return Menu::$menuLevels[$level] ?? '';
    }

    /**
     * 转换为时间
     * @param int $timestamp
     * @return string
     */
    public static function datetime(int $timestamp): string
    {
        if ($timestamp <= 127) {
            return '-';
        }
        return date('Y-m-d H:i:s', $timestamp);
    }

    //const LOG_LEVEL_DEBUG = 0;
    //const LOG_LEVEL_GENERAL = 1;
    //const LOG_LEVEL_WARN = 2;
    //const LOG_LEVEL_DANGER = 3;
    //const LOG_LEVEL_FAULT = 4;
    //const LOG_LEVEL_ERROR = 5;
    public static function logLevel(int $level): string
    {
        switch ($level) {
            case AdminLog::LEVEL_DEBUG:
                return '<span style="color: grey">调试</span>';
            case AdminLog::LEVEL_GENERAL:
                return '<span style="color: olive">普通</span>';
            case AdminLog::LEVEL_WARNING:
                return '<span style="color: orange">警告</span>';
            case AdminLog::LEVEL_DANGER:
                return '<span style="color: orangered">危险</span>';
            case AdminLog::LEVEL_FAULT:
                return '<span style="color: rebeccapurple">致命</span>';
            case AdminLog::LEVEL_ERROR:
                return '<span style="color: red">错误</span>';
            default:
                return '异常';
        }
    }

    // const LOG_TYPE_IO = 0;
    // const LOG_TYPE_FINANCE = 1;
    // const LOG_TYPE_USER = 2;
    // const LOG_TYPE_CONTEXT = 3;
    // const LOG_TYPE_SYSTEM = 4;
    // const LOG_TYPE_OTHER = 5;
    public static function logType(int $type): string
    {
        switch ($type) {
            case AdminLog::TYPE_IO:
                return '<span style="color: grey">登录/退出</span>';
            case AdminLog::TYPE_FINANCE:
                return '<span style="color: red">财务管理</span>';
            case AdminLog::TYPE_USER:
                return '<span style="color: indianred">用户管理</span>';
            case AdminLog::TYPE_CONTEXT:
                return '<span style="color: blue">内容管理</span>';
            case AdminLog::TYPE_SYSTEM:
                return '<span style="color: darkred">系统设置</span>';
            case AdminLog::TYPE_OTHER:
                return '<span style="color: blueviolet">其他类型</span>';
            default:
                return '异常';
        }
    }

    // public const TYPE_PRODUCT = 1; // 商品
    // public const TYPE_PAGE = 2; // 独立页
    // public const TYPE_FUND = 3; // 基金
    // public const TYPE_EQUITY = 4; // 股权
    // public const TYPE_COIN = 5; // 纪念币
    public static function channelType(int $type): string
    {
        switch ($type) {
            case Channel::TYPE_PRODUCT:
                return '商品';
            case Channel::TYPE_PAGE:
                return '独立页面';
            case Channel::TYPE_FUND:
                return '基金';
            case Channel::TYPE_EQUITY:
                return '股权';
            case Channel::TYPE_COIN:
                return '纪念货币';
            default:
                return '';
        }
    }

    /**
     * @param int $id
     * @return string
     */
    public static function roleName(int $id): string
    {
        $role = RolesCache::get($id);
        if ($role) {
            return $role->name;
        }
        return '未知';
    }

    /**
     * @param string $content
     * @param int $length
     * @return string
     */
    public static function truncate(string $content, int $length = 20): string
    {
        if (mb_strlen($content) <= $length) {
            return $content;
        }

        return mb_substr($content, 0, $length) . '...';
    }

    /**
     * @param string $content
     * @param int $length
     * @return string
     */
    public static function getColor(string $username): string
    {
        $temp = Db::table("user")->where("username", $username)->first();
        if (!$temp) {
            return "black";
        }
        $redis = Cache::get();
        $colorList = $redis->get("admin_color");
        $tempColorList = [];
        if (!$colorList) {
            $temps = Db::table("user")->where("color", "!=", "")->select("id", "color")->Get();
            foreach ($temps as $v) {
                $tempColorList[$v->id] = $v->color;
            }
            $redis->set("admin_color", json_encode($tempColorList));
        }
        if ($temp->path != "") {
            if (empty($tempColorList)) {
                $tempColorList = json_decode($colorList);
            }

            $arr = explode(",", $temp->path);
            $arr = array_reverse($arr);
            foreach ($arr as $v) {
                if (isset($tempColorList->$v)) {
                    if ($tempColorList->$v == "") {
                        return "black";
                    } else {
                        return $tempColorList->$v;
                    }

                }
            }

        }

        return "black";
    }

    /**
     * @param string $username
     * @return string
     */
    public static function getNickname(string $username): string
    {
        $temp = Db::table("user")->where("username", $username)->first();
        if (!$temp) {
            return "";
        }
        $redis = Cache::get();
        $colorList = $redis->get("admin_nickname");
        $tempColorList = [];
        if (!$colorList) {
            $temps = Db::table("user")->where("nick", "!=", "")->select("id", "nick")->Get();
            foreach ($temps as $v) {
                $tempColorList[$v->id] = $v->nick;
            }
            $redis->set("admin_nickname", json_encode($tempColorList));
        }
        if ($temp->path != "") {
            if (empty($tempColorList)) {
                $tempColorList = json_decode($colorList);
            }

            $arr = explode(",", $temp->path);
            $arr = array_reverse($arr);
            foreach ($arr as $v) {
                if (isset($tempColorList->$v)) {
                    if ($tempColorList->$v == "") {
                        return "";
                    } else {
                        return $tempColorList->$v;
                    }

                }
            }

        }

        return "";
    }
}
