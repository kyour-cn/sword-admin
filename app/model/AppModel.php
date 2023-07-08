<?php declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 功能应用
 * @property int $id
 * @property string $name 应用名称
 * @property string $key 应用KEY 别名
 * @property string $remark 备注
 * @property int $status 状态
 * @property int $sort 排序 ASC
 */
class AppModel extends Model
{
    protected $name = 'app';

}