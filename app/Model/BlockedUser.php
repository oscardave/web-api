<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\Cacheable;

/**
 * @property int $id
 * @property string $username
 * @property string $remark
 * @property int $create_time
 * @property int $update_time
 */
class BlockedUser extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'blocked_user';
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
     * @param string $username
     * @return void
     */
    public static function addRow(string $username)
    {
        $currentTime = time();
        $data = [
            'ip' => $username,
            'remark' => '恶意攻击检测, 自动加入用户黑名单',
            'create_time' => $currentTime,
            'update_time' => $currentTime,
        ];
        try {
            self::query()->insert($data);
        } catch (\Exception $err) {
        }
    }
}
