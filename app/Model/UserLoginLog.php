<?php
declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $username
 * @property string $url
 * @property string $login_ip
 * @property int $error_count
 * @property string $user_agent
 * @property int $created
 * @property string $remark
 */
class UserLoginLog extends Model
{
    const CREATED_AT = 'created';
    const UPDATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'user_login_log';
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
        'user_id' => 'integer',
        'error_count' => 'integer',
        'created' => 'integer',
    ];
    protected ?string $dateFormat = 'U';
}
