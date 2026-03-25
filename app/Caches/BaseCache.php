<?php
declare(strict_types=1);

namespace App\Caches;

use App\Common\Cache;
use Hyperf\HttpServer\Contract\RequestInterface;

class BaseCache
{

    /**
     * 默认过期时间: 1000 天
     */
    protected const EXPIRE_TIME = 86400000;

    /**
     * 目前已经加载的缓存类
     * @var array|string[]
     */
    protected static array $loadedClasses = [
        AdminsCache::class,
        AppsCache::class,
//        ArticlesCache::class,
//        BankCache::class,
//        BannersCache::class,
//        ChannelsCache::class,
//        ConfigsCache::class,
//        MenusCache::class,
//        NoticesCache::class,
        //PermissionIpsCache::class,
        // ProductsCache::class,
//        ProductsLogCache::class,
//        RolesCache::class,
//        UsersCache::class,
//        UserVipsCache::class,
//        WelFaresCache::class,
        // WithdrawsCache::class,
    ];

    /**
     * @var int
     */
    protected static int $pageSize = 40;

    /**
     * @var string
     */
    protected static string $modelName = '';

    /**
     * @var array
     */
    protected static array $fields = [];

    /**
     * @var array
     */
    protected static array $cachedData = [];

    /**
     * @var string
     */
    protected static string $orderBy = 'id';

    /**
     * @var string
     */
    protected static string $orderSort = 'DESC';

    /**
     * 默认全部缓存
     * @return array
     */
    public static function all(): array
    {
        $key = static::getCacheKey();
//        $rows = static::getAllFromMemory($key);
//        if ($rows) {
//            return $rows;
//        }

        $rows = static::getAllFromCache($key);
        if ($rows) {
            return $rows;
        }

        return static::getAllFromDb($key);
    }

    /**
     * @param bool $isMap
     * @return string
     */
    protected static function getCacheKey(bool $isMap = false): string
    {
        return str_replace('\\', '_', static::$modelName) . ($isMap ? '_Map' : '_All');
    }

    /**
     * @param string $key
     * @return array
     */
    protected static function getAllFromMemory(string $key = ''): array
    {
        if ($key == '') {
            $key = static::getCacheKey();
        }
        if (isset(static::$cachedData[$key])) { // 判断内存当中数据
            return static::$cachedData[$key];
        }
        return [];
    }

    /**
     * @param string $key
     * @return array
     */
    protected static function getAllFromCache(string $key = ''): array
    {
        if ($key == '') {
            $key = static::getCacheKey();
        }
        $cache = Cache::get();
        $result = $cache->get($key);
        if ($result) {
            $rows = json_decode($result);
            if ($rows) {
                // static::$cachedData[$key] = $rows; // 保存到内存当中
                return $rows;
            }
        }
        return [];
    }

    /**
     * @param string $key
     * @return array
     */
    protected static function getAllFromDb(string $key = ''): array
    {
        $className = static::$modelName;
        $queryBuilder = $className::query();
        if (static::$fields) { // 用于缓存的字段
            $queryBuilder = $queryBuilder->select(static::$fields);
        }

        $rows = $queryBuilder->orderBy(static::$orderBy, static::$orderSort)->get()->toArray();
        if (!$rows) {
            return [];
        }

        $cache = Cache::get();
        if ($key == '') {
            $key = static::getCacheKey();
        }
        $cache->set($key, json_encode($rows), static::EXPIRE_TIME); // 保存到redis当中 - list
        $keyMap = static::getCacheKey(true); // 以 map 方式保存, 用于获取单个项
        $arr = [];
        foreach ($rows as $r) {
            $value = (object)$r;
            $keyId = 'id_' . $value->id;
            $cache->hSet($keyMap, $keyId, json_encode($value));
            $arr[] = $value;
        }
        //static::$cachedData[$key] = $arr; // 保存到内存当中

        return $arr;
    }

    /**
     * 初始化加载缓存
     * @return void
     */
    public static function load()
    {
        static::getAllFromDb();
    }

    /**
     * @param RequestInterface $request
     * @param bool $cachedMemory 是否从内存当中读取 - 用于防止多次对内存结果进行处理操作
     * @param array $cond 查询条件
     * @return object
     */
    public static function getAllByPage(RequestInterface $request, bool &$cachedMemory = false, array $cond = []): object
    {
        $queries = $request->all();
        $page = $queries['page'] ?? 1; // 页数
        $queryKey = empty($cond) ? '' : '_' . md5(json_encode($cond)); // 可能的查询条件
        $cacheKey = static::getCacheKey(); // -- 缓存key
        $keyPage = $cacheKey . '_Page_Total' . $queryKey; // -- 缓存总页数key
        $returnData = (object)['rows' => [], 'page_count' => 0];
        if (isset(static::$cachedData[$keyPage]) && intval(static::$cachedData[$keyPage]) < $page) { // 超出最大页数限制
            return $returnData;
        }

        // 从内存当中获取
        $key = $cacheKey . '_Page_' . $page . $queryKey; // -- 分页
//        if (isset(static::$cachedData[$key])) {
//            $cachedMemory = true;
//            $returnData->rows = static::$cachedData[$key];
//            $returnData->page_count = intval(static::$cachedData[$keyPage]);
//            return $returnData;
//        }

        // 从缓存当中获取
        $cache = Cache::get();
        $resultPage = $cache->get($keyPage);
        if ($resultPage && intval($resultPage) < $page) { // 超出页数限制
            return $returnData;
        }
        $result = $cache->get($key);
        if ($result) {
            $rows = json_decode($result);
            static::$cachedData[$key] = $rows;
            static::$cachedData[$keyPage] = intval($resultPage);
            $returnData->rows = $rows;
            $returnData->page_count = intval($resultPage);
            return $returnData;
        }

        $className = static::$modelName;
        $where = [];
        if ($cond) {
            $where = $cond;
        }
        $pager = (object)$className::where($where)->orderBy(static::$orderBy, static::$orderSort)->paginate(static::$pageSize)->toArray(); // 如果已存在, 则使用已有分页
        $rows = array_map(function ($r) {
            return (object)$r;
        }, $pager->data);
        $value = json_encode($rows);
        $totalRecords = $pager->total;
        $pageCount = intval($totalRecords / static::$pageSize);
        if ($pageCount == 0) {
            $pageCount = 1;
        } else if ($pageCount >= 1 && $totalRecords % static::$pageSize != 0) {
            $pageCount += 1;
        }
        $cache->set($keyPage, $pageCount, static::EXPIRE_TIME); // 写入内存
        $cache->set($key, $value, static::EXPIRE_TIME); // - 写入缓存
//        static::$cachedData[$key] = $rows; // 写入内存
//        static::$cachedData[$keyPage] = $pageCount; // 写入内存
        $returnData->rows = $rows;
        $returnData->page_count = $pageCount;
        return $returnData;
    }

    /**
     * @return void
     */
    public static function refresh()
    {
        static::clear();
    }

    /**
     * @return void
     */
    public static function clear()
    {
        // -- 清除内存缓存
        echo "清理内存缓存 ... \n";
        foreach (static::$cachedData as $k => $v) {
            echo "清理内存缓存: $k ...\n";
            unset(static::$cachedData[$k]);
        }
        static::$cachedData = [];

        // -- 清除默认
        $cache = Cache::get();
        $key = static::getCacheKey();
        $cache->del($key);
        echo "删除缓存: $key ...\n";

        // -- 清除 - map
        $keyMap = static::getCacheKey(true);
        $cache->del($keyMap);
        echo "删除缓存: $keyMap ...\n";

        // -- 清除 -page
        $keyPage = $key . '*';
        $keys = $cache->keys($keyPage);
        foreach ($keys as $k) {
            $cache->del($k);
        }
        echo "清理缓存: $keyPage ...\n";
    }

    /**
     * @param int $id
     * @return ?object
     */
    public static function get(int $id): ?object
    {
        $row = static::getFromMemory($id);
        if (!empty($row)) {
            return $row;
        }

        $row = static::getFromCache($id);
        if (!empty($row)) {
            return $row;
        }

        $key = static::getCacheKey();
        $rows = static::getAllFromDb($key);
        foreach ($rows as $r) {
            if ($r->id == $id) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param int $id
     * @return ?object
     */
    protected static function getFromMemory(int $id): ?object
    {
        $key = static::getCacheKey();
        $hasCached = isset(static::$cachedData[$key]);
        if ($hasCached && static::$cachedData[$key]) {
            foreach (static::$cachedData[$key] as $r) {
                if ($r->id == $id) {
                    return $r;
                }
            }
        };
        return null;
    }

    /**
     * @param int $id
     * @return ?object
     */
    protected static function getFromCache(int $id): ?object
    {
        $keyId = 'id_' . $id;
        $keyMap = static::getCacheKey(true);
        $cache = Cache::get();
        $result = $cache->hGet($keyMap, $keyId);
        if ($result) {
            $key = static::getCacheKey();
            if (!isset(static::$cachedData[$key])) {
                $rows = static::getAllFromDb($key);
                static::$cachedData[$key] = $rows;
            }
            return json_decode($result);
        }

        return null;
    }

    /**
     * 依据用户名称得到相关数据
     * @param string $userName
     * @return object|null
     */
    public static function getByUserName(string $userName): ?object
    {
        $key = static::getCacheKey();
//        if (isset(static::$cachedData[$key])) {
//            foreach (static::$cachedData[$key] as $r) {
//                if (isset($r->username) && $r->username == $userName) {
//                    return $r;
//                }
//            }
//        }

        $rows = static::getAllFromDb();
        foreach ($rows as $r) {
            if (isset($r->username) && $r->username == $userName) {
                return $r;
            }
        }

        return null;
    }

    /**
     * @param string $key
     * @return void
     */
    public static function delKey(string $key)
    {
        $Cache = Cache::get();
        $Cache->del($key);
    }

    /**
     * @return void
     */
    public static function initialize()
    {
        foreach (self::$loadedClasses as $class) {
            echo "正在清理缓存: $class\n";
            $class::clear();
            echo "正在加载缓存: $class\n";
            $class::all();
        }
    }

    /**
     * @param array $r
     * @param object $model
     * @return void
     */
    protected static function toModel(array $r, object &$model)
    {
        foreach ($r as $k => $v) {
            $model->$k = $v;
        }
    }
}
