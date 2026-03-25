<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property int $user_id
 * @property string $md5
 * @property string $file_name
 * @property string $file_path
 * @property int $file_size
 * @property int $create_time
 */
class Attachment extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'attachment';
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
        'user_id' => 'integer',
        'create_time' => 'integer',
        'file_size' => 'integer',
    ];

    protected ?string $dateFormat = 'U';

    /**
     * @param int $userId
     * @param string $fileName
     * @param string $filePath
     * @param int $fileSize
     * @return void
     */
    public static function addRecord(int $userId, string $fileName, string $filePath, int $fileSize)
    {
        $record = new Attachment();
        $record->file_name = $fileName;
        $record->file_size = $fileSize;
        $record->file_path = $filePath;
        $record->user_id = $userId;
        $record->md5 = md5($filePath);
        $record->save();
    }
}
