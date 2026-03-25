<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property string $key
 * @property string $value
 */
class System extends Model implements CacheableInterface
{
    public const REG_OFF = 2;
    public const SMS_OFF = 2;
    public const SMS_ACTIONS = [
        'sms', // 注册发送短信
        'sms_code', // 修改支付密码发送短信
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected ?string $dateFormat = 'U';

    use Cacheable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'system';
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
    protected array $casts = ['id' => 'integer'];

    /**
     * @return object
     */
    public static function getConfigs(): object
    {
        $rows = self::query()->select(['id', 'key', 'value'])->get()->toArray();
        $arr = [];
        foreach ($rows as $r) {
            $arr[$r['key']] = $r['value'];
        }

        return (object)$arr;
    }
}
