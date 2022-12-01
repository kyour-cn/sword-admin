<?php declare (strict_types = 1);

namespace app\model\system;

use think\Model;

class RoleModel extends Model
{
    protected $name = 'role';

    // 定义自动时间戳
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'create_time';
    protected $updateTime = false;

}