<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property int $uid
 * @property string $title
 * @property string $content
 * @property int $type
 * @property int $is_read
 * @property int $create_time
 * @property int $update_time
 */
class Message extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public const TYPE_UNKNOWN = 0;
    public const TYPE_REBATE = 1;
    public const TYPE_BUY = 2;
    public const TYPE_COMMISSION = 3;
    public const TYPE_RECHARGE = 4;
    public const TYPE_WITHDRAW = 5;
    public const TYPE_CASH_RED = 6;
    public const TYPE_FUND = 7;
    public const TYPE_MOBILE = 8;
    public const TYPE_REG = 9;
    public const TYPE_DENY = 10;
    public const TYPE_VIP = 11;

    public static array $types = [
        0 => '未知类型',
        1 => '返利',
        2 => '购买产品',
        3 => '分佣',
        4 => '充值',
        5 => '用户提现',
        6 => '现金红包',
        7 => '理财金',
        8 => '手机充值',
        9 => '注册现金礼',
        10 => '审核拒绝',
        11 => 'VIP',
    ];
    protected ?string $dateFormat = 'U';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'message';
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
        'uid' => 'integer',
        'type' => 'integer',
        'is_read' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}
