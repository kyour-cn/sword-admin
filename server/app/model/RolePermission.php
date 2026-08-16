<?php

namespace app\model;

/**
 * 角色与菜单节点的授权关系，是菜单、按钮和接口权限的统一数据来源。
 */
class RolePermission extends BaseModel
{
    protected $table = 'role_permission';

    protected $guarded = ['id'];
}
