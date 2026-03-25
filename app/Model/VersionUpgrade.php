<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int $id
 * @property string $version_code
 * @property int $type
 * @property string $apk_url
 * @property string $upgrade_point
 * @property int $create_time
 * @property int $update_time
 */
class VersionUpgrade extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'version_upgrade';
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
        'type' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
    protected ?string $dateFormat = 'U';
}
