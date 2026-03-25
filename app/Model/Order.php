<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int $id
 * @property int $uid
 * @property int $product_id
 * @property string $trade_no
 * @property string $img
 * @property int $status
 * @property string $money
 * @property string $message
 * @property int $create_time
 * @property int $update_time
 * @property int $end_time
 * @property int $fl_time
 * @property int $pay_type
 * @property int $get_method
 * @property float $proportion
 */
class Order extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    public const STATUS_RETURN = 0; // 正在返利
    public const STATUS_OVER = 1; // 返利结束
    public const STATUS_AUDIT = 2; // 审核中
    public const STATUS_NO_PROOF = 3; // 未传凭证
    public const STATUS_DENY = 4; // 拒绝
    protected ?string $dateFormat = 'U';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'order';
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
        'end_time' => 'integer',
        'fl_time' => 'integer',
        'pay_type' => 'integer',
        'get_method' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer'
    ];
}
