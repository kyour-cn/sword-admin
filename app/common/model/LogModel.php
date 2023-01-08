<?php declare (strict_types = 1);

namespace App\common\model;

use think\Model;

/**
 * 日志
 */
class LogModel extends Model
{
    protected $name = 'log';

    //定义自动时间戳
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'create_time';
    protected $updateTime = null;
    //输出自动时间戳不自动格式化
    protected $dateFormat = false;

}