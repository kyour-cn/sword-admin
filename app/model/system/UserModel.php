<?php declare (strict_types = 1);

namespace app\model\system;

use think\Model;
use think\model\concern\SoftDelete;

class UserModel extends Model
{
    use SoftDelete; //软删除

    protected $name = 'user';

    //软删除字段
    protected string $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    //定义自动时间戳
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'register_time';
    protected $updateTime = false;

    /**
     * 密码修改器
     * @param $value
     * @return string
     */
    public function setPasswordAttr($value): string
    {
        return md5($value);
    }

}