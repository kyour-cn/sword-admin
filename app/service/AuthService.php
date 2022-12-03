<?php

namespace app\service;

use app\admin\service\MenuService;
use app\exception\MsgException;
use app\model\system\MenuModel;
use app\model\system\RoleModel;
use app\model\system\RuleModel;
use thans\jwt\facade\JWTAuth;

class AuthService
{

    /**
     * 获取超级管理员的后台菜单
     * @param int $appId 应用ID
     * @param array $exclude 需要排除掉的菜单
     * @return array
     */
    public function getRootMenu(int $appId, array $exclude = []): array
    {
        $model = new MenuModel();

        //需要排除掉的菜单
        if($exclude)
            $model = $model->whereNotIn('id', $exclude);

        $list = $model->order('sort')
            ->where('status', 1)
            ->where('appid', $appId)
            ->column('*','id');

        return MenuService::recursionMenu($list, 0);
    }

    /**
     * 获取用户的后台菜单
     * @param int $roleId 角色ID
     * @param array $exclude 需要排除掉的菜单
     * @return array
     */
    public function getUserMenu(int $roleId, array $exclude = []): array
    {
        $model = new MenuModel();

        //取出role的权限树
        $role = RoleModel::where('id', $roleId)
            ->where('status', 1)
            ->field('id,rules,appid')
            ->find();
        if($role){
            $rules = explode(',', $role->rules);
            $model = $model->whereIn('rid', $rules)
                ->where('appid', $role->appid);
        }else{
            return [];
        }

        //需要排除掉的菜单
        if($exclude)
            $model = $model->whereNotIn('id', $exclude);

        $list = $model->order('sort')
            ->where('status', 1)
            ->column('*','id');

        return MenuService::recursionMenu($list, 0);
    }

    /**
     * 检查当前访问权限
     * @throws MsgException
     */
    public function checkAuth(): bool
    {
        //获取登录信息
        $payload = JWTAuth::auth();

        $uid = $payload['uid']->getValue();
        $roleId = $payload['role']->getValue();

        //验证是否超级管理员
        if($this->isRootUser($uid)) return true;

        $request = request();
        $appName = app('http')->getName();
        $controller =  $request->controller();
        $action = $request->action();

        //当前请求的Path
        $path =  "$appName/$controller/$action";

        if(!$this->checkRulePath($roleId, $path)){
            throw CodeService::makeException('API_AUTH_ERROR');
        }

        return true;
    }

    /**
     * 根据路由匹配权限
     * @param int $roleId
     * @param $path
     * @return bool
     */
    public function checkRulePath(int $roleId, $path): bool
    {
        //取出rule信息
        $ruleId = RuleModel::where('path', $path)
            ->where('status', 1)
            ->value('id');
        if(!$ruleId) return false;

        $check = RoleModel::where('id', $roleId)
            ->where("FIND_IN_SET('{$ruleId}',rules)")
            ->value('id');

        return (bool) $check;
    }

    /**
     * 根据别名匹配权限
     * @param int $roleId
     * @param $alias
     * @return bool
     */
    public function checkRuleAlias(int $roleId, $alias): bool
    {
        //取出rule信息
        $ruleId = RuleModel::where('alias', $alias)
            ->where('status', 1)
            ->value('id');
        if(!$ruleId) return false;

        $check = RoleModel::where('id', $roleId)
            ->where("FIND_IN_SET('{$ruleId}',rules)")
            ->value('id');

        return (bool) $check;
    }

    /**
     * 判断用户是否为超级管理员
     * @param int $uid
     * @return bool
     */
    public function isRootUser(int $uid): bool
    {
        return $uid == 1;
    }

}