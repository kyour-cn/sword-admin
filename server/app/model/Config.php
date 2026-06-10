<?php

namespace app\model;

/**
 * 配置值
 * @property int $id
 * @property int $form_id 配置表单ID
 * @property string $form_key 表单唯一标识
 * @property string $value 配置值
 * @property int $version 版本号
 * @property int $status 状态 1=生效 0=停用
 */
class Config extends BaseModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'config';

    public $timestamps = true;

    /**
     * The attributes that aren't mass assignable.
     * @var array
     */
    protected $guarded = ['id'];

}