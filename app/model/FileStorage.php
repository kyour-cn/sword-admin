<?php

namespace app\model;

/**
 * 文件存储
 * @property int $id 
 * @property string $name 名称
 * @property string $key 唯一标识
 * @property string $config 配置
 * @property int $is_default 是否默认
 * @property int $status 状态 1=正常 0=停用
 */
class FileStorage extends BaseModel
{

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'file_storage';

    /**
     * The attributes that aren't mass assignable.
     * @var array
     */
    protected $guarded = ['id'];

}