<?php
declare(strict_types=1);

namespace App\Caches;

use App\Model\ExtAppVersion as App;

class AppsCache extends BaseCache
{
    /**
     * @var string
     */
    protected static string $modelName = App::class;
}
