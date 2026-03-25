<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property int $type
 * @property string $name
 * @property string $code
 * @property string $channel
 * @property int $created
 * @property int $updated
 * @property string $remark
 * @property int $status
 * @property int $sort
 * @property int $money_type
 * @property int $max
 * @property int $min
 * @property int $group_id
 */
class PaymentChannel extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'payment_channel';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected ?string $dateFormat = 'U';
}
