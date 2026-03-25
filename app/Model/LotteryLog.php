<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int $id
 * @property string $username
 * @property int $user_id
 * @property string $lower_name
 * @property int $status
 * @property float $money
 * @property int $created
 * @property int $updated
 */
class LotteryLog extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'created';
    //const UPDATED_AT = 'updated';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'lottery_log';
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
