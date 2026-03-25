<?php
declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use App\Caches\AppsCache;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Http\Middleware\Cors;
use App\Http\Traits\BaseJsonTrait;

//@Middleware(Cors::class)


#[Controller(prefix: "api/v1/app")]
class AppController extends FrontendController
{
    use BaseJsonTrait;
    /**
     *
     * @return array
     */
    #[GetMapping(path: "edition")]
    public function edition(): object
    {
        $rows = AppsCache::all();
        if (!$rows) {
            return self::jsonErr('请求失败');
        }

        if (empty($rows)) {
            return self::jsonErr('版本信息获取失败');
        }

        $row = $rows[0];
        $row->apk_url = $row->appstore_url ?? ''; // 设置apk_url
        return self::jsonObject($row);
    }
}
