<?php declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 配置
 * @property int $id
 * @property string $name 配置中文名
 * @property string $key 配置信息Key
 * @property string $value 配置值
 * @property string $remark 备注
 */
class ConfigModel extends Model
{
    protected $name = 'config';

}