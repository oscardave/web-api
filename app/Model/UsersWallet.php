<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int $id
 * @property float $money
 * @property string $username
 * @property float $balance
 * @property float $lock_balance
 * @property float $fund_balance
 * @property int $status
 * @property int $create_time

 */
class UsersWallet extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';

    protected ?string $dateFormat = 'U';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'users_wallet';
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
}