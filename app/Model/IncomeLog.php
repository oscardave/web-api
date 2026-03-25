<?php
declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property int $uid
 * @property string $today_income
 * @property int $today_date
 */
class IncomeLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'income_log';

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
        'today_date' => 'integer',
    ];
}
