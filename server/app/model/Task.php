<?php

namespace app\model;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 任务
 * @property int $id 
 * @property string $title 任务标题
 * @property string $group 分组
 * @property int $user_id 关联用户ID
 * @property string $type 任务类型
 * @property string $label 任务标识，用于区分业务
 * @property string $content 任务内容
 * @property string $result 任务结果
 * @property int $status 状态 0=待处理 1=处理中 2=已完成 -1=失败
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $deleted_at 删除时间
 */
class Task extends BaseModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'task';

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that aren't mass assignable.
     * @var array
     */
    protected $guarded = ['id'];

}