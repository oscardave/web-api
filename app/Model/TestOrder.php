<?php
declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property int $uid
 * @property int $product_id
 * @property string $trade_no
 * @property string $img
 * @property int $status
 * @property string $money
 * @property string $message
 * @property string $create_time
 * @property int $end_time
 * @property int $fl_time
 * @property int $pay_type
 * @property int $get_method
 */
class TestOrder extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'test_order';
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
    ];
}
