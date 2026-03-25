<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property string $name
 * @property int $grade
 * @property string $rate
 * @property string $price
 * @property int $order_price
 * @property int $create_time
 * @property int $update_time
 * @property string $bet_rate
 * @property string $rate_1
 * @property string $rate_2
 * @property string $rate_3
 * @property string $rate_4
 * @property string $rate_5
 */
class Vip extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'vip';
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