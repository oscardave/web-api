<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int $id
 * @property int $type
 * @property float $money
 * @property string $username
 * @property float $fee
 * @property int $user_id
 * @property int $copies
 * @property int $status
 * @property int $created
 * @property int $updated
 * @property int $end_time
 */
class UserMining extends Model implements CacheableInterface
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
    protected ?string $table = 'user_mining';
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