<?php
declare(strict_types=1);

namespace App\Caches;

use App\Model\Welfare;

class WelFaresCache extends BaseCache
{
    /**
     * @var string
     */
    protected static string $modelName = Welfare::class;

    /**
     * @var array
     */
    protected static array $fields = ['id', 'name', 'rate', 'type', 'money', 'color'];
}
