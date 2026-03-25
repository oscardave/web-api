<?php
declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property string $trade_no
 * @property int $uid
 * @property int $vip_id
 * @property int $grade
 * @property int $type
 * @property int $pay_type
 * @property string $money
 * @property int $tid
 * @property int $pay_status
 * @property int $status
 * @property string $img
 * @property int $create_time
 * @property int $update_time
 */
class UserVipLog extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'user_vip_log';
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
        'vip_id' => 'integer',
        'grade' => 'integer',
        'type' => 'integer',
        'pay_type' => 'integer',
        'tid' => 'integer',
        'pay_status' => 'integer',
        'status' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    protected ?string $dateFormat = 'U';
}
