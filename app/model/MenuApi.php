<?php

namespace app\model;

use support\Model;

/**
 * 菜单权限接口
 * @property int $id 
 * @property int $app_id 应用ID
 * @property int $menu_id 菜单ID
 * @property string $path API路由地址
 * @property string $tag 标识
 */
class MenuApi extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menu_api';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

}