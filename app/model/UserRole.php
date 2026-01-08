<?php

namespace app\model;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use support\Model;

/**
 * 用户角色
 * @property int $id 
 * @property int $user_id 用户ID
 * @property int $role_id 角色ID
 * @property string $created_at 创建时间
 * @property string $deleted_at 删除时间
 * @property Role $role Role模型一对一关联
 */
class UserRole extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_role';

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

    public function role(): HasOne
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }
}