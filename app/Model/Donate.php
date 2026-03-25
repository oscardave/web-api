<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int $id
 * @property int $user_id
 * @property string $article_title
 * @property string $username
 * @property int $money
 * @property int $created
 * @property int $updated
 */
class Donate extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updatede';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'donate_log';
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
