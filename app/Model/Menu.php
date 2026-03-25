<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property string $name
 * @property int $parent_id
 * @property int $method
 * @property string $url
 * @property int $is_blank
 * @property int $is_view
 * @property int $state
 * @property int $level
 * @property int $sort
 * @property string $icon
 * @property string $remark
 * @property int $created
 * @property int $updated
 */
class Menu extends Model implements CacheableInterface
{
    use Cacheable;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';
    const statusTypes = [
        0 => '禁用',
        1 => '显示',
    ];
    /**
     * 菜单类型
     * @var array
     */
    public static array $menuLevels = [
        1 => '左侧主菜单',
        2 => '左侧子菜单',
        3 => '右侧标签页',
        4 => '动作/路由',
        5 => '弹出窗口/路由',
        6 => '弹窗二级菜单',
    ];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'menu';
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
        'parent_id' => 'integer',
        'method' => 'integer',
        'is_blank' => 'integer',
        'is_view' => 'integer',
        'state' => 'integer',
        'level' => 'integer',
        'sort' => 'integer',
        'created' => 'integer',
        'updated' => 'integer',
    ];
    protected ?string $dateFormat = 'U';

    /**
     * @return array
     */
    public static function rootMenus(): array
    {
        $rows = self::query()
            ->where(['state' => 1, 'parent_id' => 0])
            ->orderBy('sort', 'DESC')
            ->get()
            ->toArray();
        $result = [];
        foreach ($rows as $row) {
            $result[] = (object)$row;
        }
        return $result;
    }

    /**
     * @param int $id
     * @param array $permission_list_arr
     * @param array $params
     * @return array
     */
    public static function menusAll(int $id = 0, array $permission_list_arr = [], array $params = []): array
    {
        $result = self::query()
            ->where(['state' => 1, 'parent_id' => $id])
            ->orderBy('sort', 'DESC')
            ->whereIn('level', [1, 2])
            ->get()
            ->toArray();
        $arr = [];
        if ($result) {
            foreach ($result as $key => $value) {
                if (in_array($value['id'], $permission_list_arr)) {
                    $rows['id'] = $value['id'];
                    $rows['title'] = $value['name'];
                    $rows['url'] = $value['url'];
                    $rows['icon'] = $value['icon'];
                    $rows['children'] = self::menusAll($value['id'], $permission_list_arr, $params);
                    $arr[] = $rows;
                }
            }
        }
        return $arr;
    }

    /**
     * @return array
     */
    public static function ids(): array
    {
        $arr = [];
        $rows = self::query()->select("id")->get();
        foreach ($rows as $r) {
            $arr[] = $r->id;
        }
        return $arr;
    }

    /**
     * @param int $id
     * @param array $permission_list_arr
     * @param array $params
     * @return array
     */
    public function getAll(int $id = 0, array $permission_list_arr = [], array $params = []): array
    {
        $result = Menu::getParent($id);
        $arr = [];
        if ($result) {
            foreach ($result as $key => $value) {
                $rows['id'] = $value['id'];
                $rows['title'] = $value['name'];
                $rows['field'] = '';
                $rows['children'] = $this->getAll($value['id'], $permission_list_arr, $params);
                $checked = isset($params['id']) && !empty($params['id']) && empty($rows['children']) && in_array($value['id'], $permission_list_arr);
                $rows['checked'] = $checked;
                $arr[] = $rows;
            }
        }
        return $arr;
    }

    /**
     * @param int $id
     * @return array
     */
    public static function getParent(int $id): array
    {
        return self::query()->where(['parent_id' => $id])->get()->toArray();
    }
}
