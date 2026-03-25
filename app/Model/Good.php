<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property int $uid
 * @property int $product_id
 * @property string $trade_no
 * @property string $img
 * @property string $money
 * @property string $sz_money
 * @property string $buy_money
 * @property string $number
 * @property string $message
 * @property int $status
 * @property int $pay_type
 * @property int $get_method
 * @property int $create_time
 * @property int $update_time
 */
class Good extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public const STATUS_PAY = 0; // 已经付款
    public const STATUS_DENY = 1; // 拒绝
    public const STATUS_PASS = 2; // 通过审核
    public const STATUS_NO_PROOF = 3; // 未传凭证
    public const STATUS_GIVE_OTHER = 4; // 已转他人

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'goods';
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
        'product_id' => 'integer',
        'status' => 'integer',
        'pay_type' => 'integer',
        'get_method' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    protected ?string $dateFormat = 'U';
}
