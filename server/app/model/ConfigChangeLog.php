<?php

namespace app\model;

/**
 * 配置变更日志
 * @property int $id
 * @property int $form_id 配置表单ID
 * @property string $form_key 表单唯一标识
 * @property string $before_value 修改前配置
 * @property string $after_value 修改后配置
 * @property int $operator_id 操作人ID
 * @property string $operator_name 操作人名称
 */
class ConfigChangeLog extends BaseModel
{
    protected $table = 'config_change_log';

    protected $guarded = ['id'];
}
