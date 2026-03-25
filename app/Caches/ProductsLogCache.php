<?php
declare(strict_types=1);

namespace App\Caches;

use App\Model\Order;
use Hyperf\DbConnection\Db;

class ProductsLogCache extends BaseCache
{
    /**
     * @var string
     */
    protected static string $modelName = Order::class;
    /**
     * @var string
     */
    protected static string $orderBy = 'order.created';

    /**
     * @var string
     */
    protected static string $orderSort = 'DESC';

    //	"SELECT a.*,b.`title`,b.`images`,b.`p_type`,b.`contract`
    // FROM `order` a LEFT  JOIN `product` b  on a.`product_id` = b.`id` WHERE `uid` = ? and b.`p_type`= ?
    // and a.`status` in (0,1,2) GROUP BY b.`title` order by a.id desc";
    /**
     * $request
     * @return object|null
     */
    public static function getOrders(int $id = 0): ?array
    {
        return Db::table("order as a")
            ->leftjoin("product as b", "a.product_id", "=", "b.id")
            ->where("a.uid", 2)
            ->where("b.type", 3)
            ->whereIn("a.status", [0, 1, 2])
            ->get()
            ->toArray();
    }
}
