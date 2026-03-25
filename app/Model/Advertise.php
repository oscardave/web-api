<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property string $title
 * @property string $url
 * @property string $link
 * @property string $language
 * @property int $created
 * @property int $status
 * @property int $updated
 * @property int $second
 * @property int $sort
 */
class Advertise extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'advertise';

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
