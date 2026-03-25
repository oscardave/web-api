<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int $id
 * @property int $type
 * @property string $username
 *  @property int $user_id
 * @property string $bill_no
 * @property float $price
 * @property float $number
 * @property float $total_money
 * @property float $cost
 * @property string $cost_rate
 * @property int $status
 * @property int $created
 * @property int $updated
 */
class CToC extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'c_to_c';
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
        'id' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
    protected ?string $dateFormat = 'U';
}
