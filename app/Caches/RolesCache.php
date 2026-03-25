<?php
declare(strict_types=1);

namespace App\Caches;

use App\Model\Role;

class RolesCache extends BaseCache
{
    /**
     * @var string
     */
    protected static string $modelName = Role::class;

    /**
     * @var array
     */
    protected static array $fields = ['id', 'name'];
}
