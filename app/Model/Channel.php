<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property int $is_show
 * @property string $name
 * @property string $url
 * @property int $type
 * @property string $images
 * @property int $create_time
 * @property int $update_time
 * @property int $p_id
 * @property string $desc
 * @property string $content
 * @property int $sorts
 */
class Channel extends Model implements CacheableInterface
{
    public const TYPE_PRODUCT = 1; // 商品
    public const TYPE_PAGE = 2; // 独立页
    public const TYPE_FUND = 3; // 基金
    public const TYPE_EQUITY = 4; // 股权
    public const TYPE_COIN = 5; // 纪念币

    public const CHANNEL_ABOUT_US = 23; // 关于我们
    public const CHANNEL_QA = 15; //  QA
    public const CHANNEL_CONCAT = 17; // 联系我们

    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'channel';
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
        'is_show' => 'integer',
        'type' => 'integer',
        'create_time' => 'integer',
        'p_id' => 'integer',
        'sorts' => 'integer',
        'update_time' => 'integer',
    ];
    protected ?string $dateFormat = 'U';
}
