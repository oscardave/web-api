<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 * @property int $create_time
 * @property int $update_time
 * @property int $is_show
 *
 */
class Notice extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'notice';
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
        'create_time' => 'integer',
        'update_time' => 'integer',
        'is_show' => 'integer',
    ];
    protected ?string $dateFormat = 'U';
}
