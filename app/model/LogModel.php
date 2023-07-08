<?php declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 日志表
 * @property int $id
 * @property int $level_id 日志级别 <10为系统日志
 * @property string $level_name 日志级别名称
 * @property string $title 标题
 * @property string $value 日志内容
 * @property string $value_type 日志类型  text,json
 * @property string $request_source 请求来源
 * @property string $request_ip 请求来源IP
 * @property int $request_user_id 操作人ID
 * @property string $request_user 操作人
 * @property int $create_time 记录时间
 * @property int $status 状态 0=未处理 1=已查看 2=已处理
 */
class LogModel extends Model
{
    protected $name = 'log';

    //定义自动时间戳
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'create_time';
    protected $updateTime = null;
    //输出自动时间戳不自动格式化
    protected $dateFormat = false;

}