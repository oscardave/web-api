<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int $id
 * @property int $user_id
 * @property int $status
 * @property float $money
 * @property string $username
 * @property int $created
 * @property int $updated
 */
class Pledge extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'pledge_log';
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
