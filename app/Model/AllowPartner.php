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
 * @property int $create_time
 * @property int $update_time
 */
class AllowPartner extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'allow_partner';

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
