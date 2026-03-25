<?php
declare(strict_types=1);

namespace App\Caches;

use Hyperf\HttpServer\Contract\RequestInterface;
use App\Common\Utils;
use \App\Model\Article;

class ArticlesCache extends BaseCache
{
    /**
     * @var string
     */
    protected static string $modelName = Article::class;

    /**
     * @param RequestInterface $request
     * @return ?object
     */
    public static function getArticles(RequestInterface $request): ?object
    {
        $hasCached = false;
        $result = self::getAllByPage($request, $hasCached, [['id', '>', 20]]);
        if (!$result) {
            return null;
        }

        if (!$hasCached) {
            $host = $request->getHeader('host')[0] ?? 'info.local';
            foreach ($result->rows as $k => &$r) {
                $r->content = Utils::mergeImageUrl($r->content, $host);
                $r->images = "http://${host}" . $r->images;
            }
        }

        return $result;
    }
}
