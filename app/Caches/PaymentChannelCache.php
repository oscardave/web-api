<?php
declare(strict_types=1);

namespace App\Caches;

use Hyperf\HttpServer\Contract\RequestInterface;
use App\Common\Cache;
use \App\Model\PaymentChannel;
use Hyperf\DbConnection\Db;

class PaymentChannelCache extends BaseCache
{
    /**
     * @var string
     */
    protected static string $modelName = PaymentChannel::class;
    /**
     * @var string
     */
    protected static string $orderBy = "id";
    /**
     * @var string
     */
    protected static string $orderSort = "desc";

    static public function getChannel()
    {
        $client = Cache::get();
        $key = static::getCacheKey();
        $temp = $client->get($key);
        if (!$temp) {
            $vip = Db::table("payment_channel")->where("status", 1)->get();
            $client->set($key, json_encode($vip));
            return $vip;
        }
        return json_decode($temp);
    }
}