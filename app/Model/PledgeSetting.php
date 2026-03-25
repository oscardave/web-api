<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int $id
 * @property float $money
 * @property string $start_time
 * @property int $created
 * @property int $updated
 */
class PledgeSetting extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'pledge_setting';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected array $casts = [

    ];
    protected ?string $dateFormat = 'U';
}