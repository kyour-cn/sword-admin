<?php

namespace app\model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use support\Model;

/**
 * 菜单
 * @property int $id 
 * @property int $app_id 应用ID
 * @property int $pid 上级ID
 * @property string $name 别名
 * @property string $title 显示名称
 * @property string $type 类型
 * @property string $path 路由地址
 * @property string $component 组件地址
 * @property int $sort 排序
 * @property string $meta meta路由参数
 * @property MenuApi[] $menuApi MenuApi模型一对多关联
 */
class Menu extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menu';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 关联MenuApi
     */
    public function menuApi(): HasMany
    {
        return $this->hasMany(MenuApi::class, 'menu_id', 'id');
    }

}