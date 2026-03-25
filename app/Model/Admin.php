<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property int $last_login_time
 * @property string $last_login_ip
 * @property int $status
 * @property int $role_id
 * @property int $create_time
 * @property int $update_time
 * @property string $allow_ip
 */
class Admin extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'admin';
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
        'last_login_time' => 'integer',
        'status' => 'integer',
        'role_id' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    protected ?string $dateFormat = 'U';

    /**
     * @param string $visitIP
     * @return bool
     */
    public function allowLogin(string $visitIP): bool
    {
        if (!$this->enable()) {
            return false;
        }

        $arr = explode(',', $this->allow_ip);
        // print_r([$arr, $this->allow_ip]);
        foreach ($arr as $r) {
            $allowIP = trim($r);
            // echo "allow ip = [$allowIP], visit ip = [$visitIP] \n";
            if ($allowIP == $visitIP) {
                return true;
            }
        }

        return false;
    }

    /**
     * 是否启动状态
     * @return bool
     */
    public function enable(): bool
    {
        return $this->status == 1;
    }
}
