<?php declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 用户表
 * @property int $id
 * @property string $realname 姓名
 * @property string $username 用户名
 * @property string $mobile 手机号
 * @property string $avatar 头像
 * @property int $sex 性别 0=女 1=男
 * @property string $password 密码 md5
 * @property int $register_time 注册时间
 * @property int $login_time 登录时间
 * @property int $status 状态
 * @property int $delete_time 删除时间
 * @property int $role_id 角色ID
 */
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

}