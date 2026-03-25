<?php
declare(strict_types=1);

namespace App\Caches;

use App\Model\Notice;

class NoticesCache extends BaseCache
{
    /**
     * @var string
     */
    protected static string $modelName = Notice::class;

    /**
     * @var array
     */
    protected static array $fields = ['id', 'title', 'is_show', 'create_time', 'update_time', 'content'];
}
