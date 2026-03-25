<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property int $confirm_time
 * @property string $username
 * @property int $user_id
 * @property float $money
 * @property float $confirm_money
 * @property int $status
 * @property string $image
 * @property int $created
 * @property int $updated
 * @property string $admin
 * @property string $remark
 * @property string $code
 * @property string $bill_no
 * @property string $discount_remark
 * @property float $discount_money
 * @property int $pay_type
 * @property int $deposit_type
 * @property int $coin_type
 * @property string $channel_name
 * @property int $combo_id
 */
class Deposit extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'deposit';
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

    ];
    protected ?string $dateFormat = 'U';
}
