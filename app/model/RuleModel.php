<?php declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 权限规则表
 * @property int $id
 * @property int $app_id 应用ID
 * @property string $name 名字
 * @property string $alias 英文别名
 * @property string $path 规则
 * @property int $pid 上级Id
 * @property int $status 状态 0:1
 * @property int $sort 排序
 * @property int $addon_id 插件ID 为0=不验证插件
 */
class RuleModel extends Model
{
    protected $name = 'rule';

}