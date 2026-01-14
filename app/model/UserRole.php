<?php

namespace app\model;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 用户角色
 * @property int $id 
 * @property int $user_id 用户ID
 * @property int $role_id 角色ID
 * @property string $created_at 创建时间
 * @property string $deleted_at 删除时间
 * @property Role $role Role模型一对一关联
 */
class UserRole extends BaseModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'user_role';

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

    public function role(): HasOne
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }
}