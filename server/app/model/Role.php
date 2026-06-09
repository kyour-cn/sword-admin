<?php

namespace app\model;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 用户角色
 * @property int $id 
 * @property int $app_id 应用ID
 * @property string $name 角色名称
 * @property string $rules 权限ID ,分割
 * @property string $rules_checked 权限树选中的字节点ID
 * @property string $remark 简介
 * @property int $status 状态
 * @property int $sort 排序
 * @property int $is_admin 是否为管理员（所有权限）
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $deleted_at 删除时间
 * @property App $app App模型一对一关联
 */
class Role extends BaseModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'role';

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

    public function app(): HasOne
    {
        return $this->hasOne(App::class, 'id', 'app_id');
    }
}