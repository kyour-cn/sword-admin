<?php

namespace app\model;

/**
 * 应用列表
 * @property int $id 
 * @property string $name 应用名称
 * @property string $key 应用KEY 别名
 * @property string $remark 备注
 * @property int $status 状态
 * @property int $sort 排序 ASC
 */
class App extends BaseModel
{

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'app';

    /**
     * The attributes that aren't mass assignable.
     * @var array
     */
    protected $guarded = ['id'];

}