<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property int $channel_pid
 * @property int $channel_id
 * @property int $type
 * @property int $term
 * @property string $title
 * @property string $images
 * @property string $desc
 * @property string $end_time
 * @property string $aims_money
 * @property string $least_money
 * @property int $input_money
 * @property string $content
 * @property string $plan
 * @property string $proportion
 * @property int $create_time
 * @property int $status
 * @property int $sub_qc
 * @property int $pattern
 * @property int $number
 * @property int $p_type
 * @property string $contract
 * @property int $is_give
 * @property int $give_type
 * @property int $give_product_id
 * @property string $give_power
 * @property int $is_recommend
 * @property int $jindu
 * @property string $fenhong
 * @property int $cumulative_number
 * @property int $product_limit
 * @property int $end_day
 * @property string $commission_first
 * @property string $commission_second
 * @property string $commission_third
 * @property int $is_one
 */
class Product extends Model implements CacheableInterface
{
    public const TYPE_CURRENCY = 1;
    public const TYPE_FUND = 2;
    public const TYPE_EQUITY = 3;
    public const TYPE_COIN = 4;

    public const STATUS_READY = 1;
    public const STATUS_OPEN = 2;
    public const STATUS_END = 3;

    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'product';
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
        'channel_pid' => 'integer',
        'channel_id' => 'integer',
        'type' => 'integer',
        'term' => 'integer',
        'input_money' => 'integer',
        'create_time' => 'integer',
        'status' => 'integer',
        'sub_qc' => 'integer',
        'pattern' => 'integer',
        'number' => 'integer',
        'p_type' => 'integer',
        'is_give' => 'integer',
        'give_type' => 'integer',
        'give_product_id' => 'integer',
        'is_recommend' => 'integer',
        'jindu' => 'integer',
        'update_time' => 'integer',

    ];
    protected ?string $dateFormat = 'U';
}