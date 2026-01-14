<?php

namespace app\model;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 日志表
 * @property int $id 
 * @property int $app_id 应用ID 0为未知
 * @property int $type_id 日志级别 <10为系统日志
 * @property string $type_name 日志级别名称
 * @property string $title 标题
 * @property string $value 日志内容
 * @property string $value_type 日志类型  text,json,html
 * @property string $request_source 请求来源
 * @property string $request_ip 请求来源IP
 * @property int $request_user_id 操作人ID
 * @property string $request_user 操作人
 * @property int $status 状态 0=未处理 1=已查看 2=已处理
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property MenuApi $menuApi MenuApi模型一对一关联
 */
class Log extends BaseModel
{

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'log';

    /**
     * The attributes that aren't mass assignable.
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * 关联MenuApi
     */
    public function menuApi(): HasOne
    {
        return $this->hasOne(MenuApi::class, 'menu_id', 'id');
    }

}