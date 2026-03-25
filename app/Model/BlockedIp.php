<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int $id
 * @property string $ip
 * @property string $remark
 * @property int $create_time
 * @property int $update_time
 */
class BlockedIp extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'blocked_ip';
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
    ];
    protected ?string $dateFormat = 'U';

    /**
     * @param string $ip
     * @return void
     */
    public static function addRow(string $ip)
    {
        $currentTime = time();
        $data = [
            'ip' => $ip,
            'remark' => '恶意攻击检测, 自动加入IP黑名单',
            'create_time' => $currentTime,
            'update_time' => $currentTime,
        ];
        try {
            self::query()->insert($data);
        } catch (\Exception $err) {
        }
    }
}
