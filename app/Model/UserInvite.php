<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property int $uid
 * @property int $pid
 * @property int $levels
 * @property string $create_time
 */
class UserInvite extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'user_invite';
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
        'pid' => 'integer',
        'levels' => 'integer',
    ];
}
