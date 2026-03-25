<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int $id
 * @property int $client_type
 * @property int $release_type
 * @property string $appstore_url
 * @property string $version_number
 * @property string $release_time
 * @property int $upgrade_type
 * @property string $upgrade_message
 * @property string $min_compatible_version
 * @property int $version_status
 * @property string $created_at
 * @property string $updated_at
 */
class ExtAppVersion extends Model implements CacheableInterface
{
    use Cacheable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'ext_app_versions';

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
        'client_type' => 'integer',
        'release_type' => 'integer',
        'upgrade_type' => 'integer',
        'version_status' => 'integer',
    ];
}
