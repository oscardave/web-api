<?php
declare(strict_types=1);

namespace App\Caches;

use App\Model\Banner;

class BannersCache extends BaseCache
{
    /**
     * @var string
     */
    protected static string $modelName = Banner::class;

    /**
     * @var array
     */
    protected static array $fields = ['id', 'title', 'uri', 'status', 'images'];
}
