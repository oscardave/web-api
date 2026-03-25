<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property string $username
 * @property string $trade
 * @property int $user_id
 * @property float $money
 * @property int $status
 * @property string $image
 * @property string $user_usdt
 * @property string $deny_reason
 * @property string $company_usdt
 * @property int $created
 * @property int $updated
 */
class DepositUsdt extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'deposit_usdt';
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
