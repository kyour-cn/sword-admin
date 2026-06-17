<?php

namespace app\model;

/**
 * 操作审计日志
 * @property int $id
 * @property int $app_id 应用ID 0为未知
 * @property int $actor_id 操作人ID 0为匿名或系统
 * @property string $actor_name 操作人名称
 * @property string $action 操作动作
 * @property string $module 业务模块
 * @property string $resource_type 资源类型
 * @property string $resource_id 资源ID
 * @property string $title 审计标题
 * @property string $description 操作摘要
 * @property string $method HTTP方法
 * @property string $path 请求路径
 * @property string $ip 请求IP
 * @property string $user_agent 客户端信息
 * @property int $status 结果状态 1=成功 0=失败
 * @property array|null $context 结构化补充信息
 * @property string $created_at 创建时间
 */
class AuditLog extends BaseModel
{

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'audit_log';

    /**
     * The attributes that aren't mass assignable.
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'context' => 'array',
    ];

}
