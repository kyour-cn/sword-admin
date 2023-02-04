<?php declare (strict_types = 1);

namespace app\common\model;

use think\Model;

class RoleModel extends Model
{
    protected $name = 'role';

    // 定义自动时间戳
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    //输出自动时间戳不自动格式化
    protected $dateFormat = false;

}