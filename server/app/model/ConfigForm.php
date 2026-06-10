<?php

namespace app\model;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 配置表单
 * @property int $id
 * @property string $key 表单唯一标识
 * @property string $title 表单名称
 * @property string $group_key 分组标识
 * @property string $group_title 分组名称
 * @property string $schema 表单结构
 * @property int $status 状态
 * @property int $sort 排序
 * @property string $remark 备注
 */
class ConfigForm extends BaseModel
{
    use SoftDeletes;

    protected $table = 'config_form';

    public $timestamps = true;

    protected $guarded = ['id'];
}
