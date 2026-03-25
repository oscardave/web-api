<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int $id
 * @property int $type
 * @property int $price
 * @property string $title
 * @property float $management_fee
 * @property int $num
 * @property int $buy_num
 * @property int $management_fee_type
 * @property int $created
 * @property int $updated
 * @property int $end_time
 */
class Mining extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected ?string $dateFormat = 'U';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'mining';
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
}
