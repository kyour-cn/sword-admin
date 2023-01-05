<?php declare (strict_types = 1);

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\model\relation\HasOne;

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
    //输出自动时间戳不自动格式化
    protected $dateFormat = false;

    /**
     * 角色模型管理
     * @return HasOne
     */
//    public function role(): HasOne
//    {
//        return $this->hasOne(RoleModel::class);
//    }

}