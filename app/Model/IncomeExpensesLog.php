<?php
declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property int $uid
 * @property string $money
 * @property string $title
 * @property string $create_time
 * @property int $type
 * @property int $order_id
 * @property int $product_id
 * @property int $to_uid
 */
class IncomeExpensesLog extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'income_expenses_log';
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
        'order_id' => 'integer',
        'product_id' => 'integer',
        'to_uid' => 'integer',
    ];


    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
}
