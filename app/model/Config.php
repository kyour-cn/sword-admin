<?php

namespace app\model;

/**
 * 配置
 * @property int $id 
 * @property string $key 标签
 * @property string $title 名称
 * @property string $group 分组
 * @property string $type 数据类型
 * @property string $value 变量值
 */
class Config extends BaseModel
{

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'config';

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     * @var array
     */
    protected $guarded = ['id'];

}