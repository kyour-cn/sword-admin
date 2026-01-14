<?php

namespace app\model;

/**
 * 日志级别
 * @property int $id <10为系统日志
 * @property int $app_id 应用ID 0为通用
 * @property string $name 中文名称
 * @property string $label 英文别名
 * @property string $remark 备注
 * @property int $status 日志开启状态
 * @property string $color 日志颜色 #ff0000
 */
class LogType extends BaseModel
{

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'log_type';

    /**
     * The attributes that aren't mass assignable.
     * @var array
     */
    protected $guarded = ['id'];

}