<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property string $trade_no
 * @property int $uid
 * @property string $money
 * @property string $img
 * @property int $type
 * @property int $status
 * @property int $create_time
 * @property int $update_time
 * @property int $is_show
 * @property string $bz
 */
class Recharge extends Model implements CacheableInterface
{
    public const STATUS_PAY_NO = 0;
    public const STATUS_PAY = 1;
    public const STATUS_AUDIT = 2;
    public const STATUS_PASS = 3;
    public const STATUS_DENY = 4;

    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected ?string $dateFormat = 'U';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'recharge';
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
        'type' => 'integer',
        'status' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
        'is_show' => 'integer',
    ];
}
