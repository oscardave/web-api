<?php
declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property int $uid
 * @property int $welfare_id
 * @property string $welfare_name
 * @property string $remark
 * @property string $create_time
 */
class UserWelfareLog extends Model
{
    const CREATED_AT = 'create_time';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'user_welfare_log';
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
        'welfare_id' => 'integer',
    ];
    protected ?string $dateFormat = 'U';
}
