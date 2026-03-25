<?php
declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Model;

use Hyperf\DbConnection\Model\Model as BaseModel;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * Class Model
 * @package App\Model
 * @property int $id
 */
abstract class Model extends BaseModel implements CacheableInterface
{
    use Cacheable;

    // protected string $dateFormat = 'U';
    // //public $timestamps = FALSE;
    // protected string $connection = 'default';
    // public static array $autoFields = ['created', 'updated'];
    // protected $fillable = ['created', 'updated'];
    //protected $casts = ['id' => 'integer'];
}
