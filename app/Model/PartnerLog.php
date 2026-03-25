<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property string $username
 * @property int $user_id
 * @property int $period
 * @property float $money
 * @property int $type
 * @property int $created
 * @property int $updated
 * @property int $handle_type
 * @property float $rate
 */
class PartnerLog extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'updated';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'partner_log';
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
    protected array $casts = [];

    protected ?string $dateFormat = 'U';
}