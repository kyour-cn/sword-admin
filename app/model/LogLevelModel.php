<?php declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 日志级别
 * Class LogLevelModel
 * @property int $id <10为系统日志
 * @property string $name 中文名称
 * @property string $label 英文别名
 * @property string $remark 备注
 * @property int $status 日志开启状态
 * @property string $color 日志颜色 #ff0000
 */
class LogLevelModel extends Model
{
    protected $name = 'log_level';

}