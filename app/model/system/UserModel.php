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

}