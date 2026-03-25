<?php
declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property int $uid
 * @property int $to_uid
 * @property int $product_id
 * @property int $tid
 * @property int $new_id
 * @property int $type
 * @property int $create_time
 */
class AssignmentLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'assignment_log';
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
        'to_uid' => 'integer',
        'product_id' => 'integer',
        'tid' => 'integer',
        'new_id' => 'integer',
        'type' => 'integer',
        'create_time' => 'integer',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;

    protected ?string $dateFormat = 'U';
}
