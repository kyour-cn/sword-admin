<?php

namespace app\model;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 文件
 * @property int $id 
 * @property string $file_name 文件名
 * @property string $file_type 文件类型（MIME类型，如 image/png）
 * @property string $file_ext 文件后缀（如 .jpg/.pdf）文件后缀
 * @property int $file_size 文件大小（字节）
 * @property string $url 链接地址
 * @property string $file_path 存储路径
 * @property int $menu_id 
 * @property int $storage_id 存储方式id
 * @property string $storage_key 储存方式key
 * @property string $hash 文件的哈希值
 * @property int $user_id 上传用户id
 * @property int $status 状态 1=正常 0=停用
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $deleted_at 删除时间
 */
class File extends BaseModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'file';

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