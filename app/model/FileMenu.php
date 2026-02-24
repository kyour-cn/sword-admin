<?php

namespace app\model;

/**
 * 文件分组
 * @property int $id 
 * @property string $name 名称
 */
class FileMenu extends BaseModel
{

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'file_menu';

    /**
     * The attributes that aren't mass assignable.
     * @var array
     */
    protected $guarded = ['id'];

}