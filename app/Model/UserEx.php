<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property int $uid
 * @property string $name
 * @property string $user_name
 * @property string $idcard
 * @property string $address
 * @property string $hongbao
 * @property int $status
 * @property int $create_time
 * @property int $update_time
 */
class UserEx extends Model implements CacheableInterface
{
    public const STATUS_APPLY = 0;
    public const STATUS_PASS = 1;
    public const STATUS_DENY = 2;

    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'user_ex';
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
        'update_time' => 'integer',
    ];
    protected ?string $dateFormat = 'U';
}
