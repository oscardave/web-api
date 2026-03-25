<?php
declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property int $money
 *  @property int $num
 * @property int $created
 * @property int $updated
 */
class InviteReward extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'invite_reward';
    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [];

    protected ?string $dateFormat = 'U';
}
