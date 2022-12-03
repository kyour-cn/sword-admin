<?php

namespace app\service;

use app\admin\service\MenuService;
use app\exception\MsgException;
use app\model\system\MenuModel;
use thans\jwt\facade\JWTAuth;

class AuthService
{

    /**
     * 获取当前用户的菜单
     * @param array $exclude
     * @return array
     */
    public function getUserMenu(array $exclude = []): array
    {
//        $payload = JWTAuth::auth();

        $model = (new MenuModel())
            ->order('sort')
            ->where('appid', 1)
            ->where('status', 1);

        if($exclude)
            $model->whereNotIn('rid', $exclude);

        $list = $model->column('*','id');

        return MenuService::recursionMenu($list, 0);
    }

    /**
     * 检查当前访问权限
     * @throws MsgException
     */
    public function checkAuth(): bool
    {
        $request = request();
        $appName = app('http')->getName();
        $controller =  $request->controller();
        $action = $request->action();

        //当前请求的Path
        $path =  "$appName/$controller/$action";

        //获取登录信息
        $payload = JWTAuth::auth();

        $uid = $payload['uid']->getValue();
        $roleId = $payload['role']->getValue();

        if($roleId != 1){
            throw CodeService::makeException('API_AUTH_ERROR');
        }

        return true;
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