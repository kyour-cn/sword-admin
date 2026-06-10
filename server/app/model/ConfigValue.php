<?php

namespace app\model;

/**
 * 配置值
 * @property int $id
 * @property int $form_id 配置表单ID
 * @property string $form_key 表单唯一标识
 * @property string $value 配置值
 * @property int $version 版本号
 * @property int $status 状态
 */
class ConfigValue extends BaseModel
{
    protected $table = 'config_value';

    public $timestamps = true;

    protected $guarded = ['id'];
}
