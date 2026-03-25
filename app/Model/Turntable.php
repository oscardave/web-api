<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int $id
 * @property float $rate
 * @property float money
 * @property string $image
 * @property int $status
 *  @property int $level
 * @property int $type
 * @property int $created
 * @property int $updated
 */
class Turntable extends Model implements CacheableInterface
{
    use Cacheable;
    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'turntable';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [];

    protected ?string $dateFormat = 'U';
}
