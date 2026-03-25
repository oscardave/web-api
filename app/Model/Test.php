<?php
declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $money
 * @property int $order_id
 * @property int $product_id
 * @property int $to_uid
 * @property int $uid
 * @property string $create_time
 * @property int $type
 */
class Test extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'test';
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
    protected array $casts = ['id' => 'integer', 'order_id' => 'integer', 'product_id' => 'integer', 'to_uid' => 'integer', 'uid' => 'integer', 'type' => 'integer'];
}
