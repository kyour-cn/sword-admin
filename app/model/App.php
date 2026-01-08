<?php

namespace app\model;

use support\Model;

/**
 * 应用列表
 * @property int $id 
 * @property string $name 应用名称
 * @property string $key 应用KEY 别名
 * @property string $remark 备注
 * @property int $status 状态
 * @property int $sort 排序 ASC
 */
class App extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'app';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

}