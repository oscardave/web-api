<?php
declare(strict_types=1);

namespace App\Caches;

use App\Model\Channel;

class ChannelsCache extends BaseCache
{
    /**
     * @var string
     */
    protected static string $modelName = Channel::class;

    /**
     * @var string
     */
    protected static string $orderBy = 'sorts';

    /**
     * @var string
     */
    protected static string $orderSort = 'ASC';

    /**
     * @var array|string[]
     */
    protected static array $fields = ['id', 'name', 'is_show', 'url', 'type', 'images', 'p_id', 'content'];
}
