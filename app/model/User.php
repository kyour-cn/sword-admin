<?php

namespace app\model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use support\Model;

/**
 * 用户表
 * @property int $id 
 * @property string $nickname 昵称
 * @property string $username 用户名(登录账号)
 * @property string $mobile 手机号
 * @property string $avatar 头像
 * @property string $password 密码 md5
 * @property int $status 状态
 * @property string $login_time 登录时间
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $deleted_at 删除时间
 * @property UserRole[] $userRole UserRole模型一对多关联
 */
class User extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';

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

    public function userRole(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }
}