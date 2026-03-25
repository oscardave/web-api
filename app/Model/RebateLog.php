<?php
declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property int $order_id
 * @property string $money
 * @property string $create_time
 */
class RebateLog extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'rebate_log';
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
        'order_id' => 'integer',
    ];
}
