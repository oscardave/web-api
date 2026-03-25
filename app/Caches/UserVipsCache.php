<?php
declare(strict_types=1);

namespace App\Caches;

use App\Model\Vip;

class UserVipsCache extends BaseCache
{
    /**
     * @var string
     */
    protected static string $modelName = Vip::class;

    /**
     * @var array
     */
    protected static array $fields = ['id', 'name', 'grade', 'rate'];
}
