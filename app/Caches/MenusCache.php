<?php
declare(strict_types=1);

namespace App\Caches;

use App\Model\Menu;

class MenusCache extends BaseCache
{
    /**
     * @var string
     */
    protected static string $modelName = Menu::class;
}
