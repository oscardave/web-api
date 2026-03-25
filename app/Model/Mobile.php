<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property int $uid
 * @property string $mobile
 * @property string $money
 * @property int $status
 * @property int $create_time
 */
class Mobile extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'mobile';
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
        'uid' => 'integer',
        'status' => 'integer',
        'create_time' => 'integer',
    ];

    protected ?string $dateFormat = 'U';
}
