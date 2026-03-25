<?php
declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property int $uid
 * @property string $money
 * @property int $type
 * @property string $create_time
 */
class RechargeLog extends Model
{
    const UPDATED_AT = null;
    const CREATED_AT = 'create_time';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'recharge_log';
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
    ];
}
