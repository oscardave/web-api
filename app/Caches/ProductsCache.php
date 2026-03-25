<?php
declare(strict_types=1);

namespace App\Caches;

use App\Model\Product;
use Hyperf\HttpServer\Contract\RequestInterface;

class ProductsCache extends BaseCache
{
    private const KEY_CURRENCY = 'currencies';
    private const KEY_EQUITY = 'equities';
    private const KEY_FUND = 'funds';
    private const KEY_CHANNEL = 'channel';
    /**
     * @var string
     */
    protected static string $modelName = \App\Model\Product::class;
    /**
     * @var string
     */
    protected static string $orderBy = 'sort';

    /**
     * @var string
     */
    protected static string $orderSort = 'desc';
    /**
     * @var int
     */
    protected static int $pageSize = 100;

    /**
     * 保险
     * @return array
     */
    public static function currencies(): array
    {
        if (!isset(self::$cachedData[self::KEY_CURRENCY])) {
            self::$cachedData[self::KEY_CURRENCY] = array_filter(self::all(), function ($r) {
                return $r->p_type == 5;
            });
        }

        return self::$cachedData[self::KEY_CURRENCY];
    }

    /**
     * 股权
     * @return array
     */
    public static function equities(): array
    {
        if (!isset(self::$cachedData[self::KEY_EQUITY])) {
            self::$cachedData[self::KEY_EQUITY] = array_filter(self::all(), function ($r) {
                return $r->p_type == Product::TYPE_EQUITY;
            });
        }

        return self::$cachedData[self::KEY_EQUITY];
    }

    /**
     * 基金
     * @return array
     */
    public static function funds(): array
    {
        if (!isset(self::$cachedData[self::KEY_FUND])) {
            self::$cachedData[self::KEY_FUND] = array_filter(self::all(), function ($r) {
                return $r->p_type == Product::TYPE_FUND;
            });
        }

        return self::$cachedData[self::KEY_FUND];
    }

    /**
     * @param RequestInterface $request
     * @param int $type
     * @return object|null
     */
    public static function getProducts(RequestInterface $request, int $type): ?object
    {
        $hasCached = false;
        $result = self::getAllByPage($request, $hasCached, [["p_type", "=", $type]]);
        if (!$result) {
            return null;
        }
        // $num = [];
        if (!$hasCached) {
            foreach ($result->rows as $kk => $vv) {
                $fen = $vv->least_money * ($vv->proportion / 100);
                $result->rows[$kk]->fenhong = sprintf("%.3f", $fen);
                //$tempNum = $vv->cumulative_number + $vv->cumulative_number * rand(1, 5);
            }
        }

        return $result;
    }

    /**
     * @param RequestInterface $request
     * @param int $id
     * @return object|null
     */
    public static function detail(RequestInterface $request, int $id): ?object
    {
        $hasCached = false;
        $result = self::getAllByPage($request, $hasCached, [["id", "=", $id]]);
        if (!$result) {
            return null;
        }
        return $result;
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getProductTitle(int $id): string
    {
        $row = self::get($id);
        if (!$row) {
            return '';
        }

        return $row->title;
    }
}