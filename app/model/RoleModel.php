<?php declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 用户角色
 * Class RoleModel
 * @property int $id
 * @property int $app_id 应用ID
 * @property string $name 角色名称
 * @property string $rules 权限ID ,分割a
 * @property string $rules_checked 权限树选中的字节点ID
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 * @property string $remark 简介
 * @property int $status 状态
 * @property int $sort 排序
 * @property int $is_auto 是否为系统自动创建
 */
class RoleModel extends Model
{
    protected $name = 'role';

    // 定义自动时间戳
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    //输出自动时间戳不自动格式化
    protected $dateFormat = false;

}