<?php
declare (strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\{Cacheable, CacheableInterface};

/**
 * @property int $id
 * @property int $payment_group_id
 * @property string $bank_name
 * @property string $card_number
 * @property string $name
 * @property string $remark
 * @property int $create_time
 * @property int $update_time
 * @property int $status
 *
 */
class ReceiveCard extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'receive_card';

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
        'status' => 'integer',
    ];

    protected ?string $dateFormat = 'U';
}
