<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int $id
 * @property int $uid
 * @property string $money
 * @property string $username
 * @property string $name
 * @property string $real_name
 * @property string $address
 * @property int $status
 * @property int $create_time
 * @property int $update_time
 * @property string $deny_reason
 * @property string $bz
 * @property int $type
 * @property string $trade_no
 */
class PlatformWithdraw extends Model implements CacheableInterface
{
    use Cacheable;

    public const STATUS_APPLY = 0;
    public const STATUS_PASS = 1;
    public const STATUS_DENY = 2;
    public const STATUS_FINISH = 3;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'platform_withdraw';
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
        'type' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer'
    ];
    protected ?string $dateFormat = 'U';
}