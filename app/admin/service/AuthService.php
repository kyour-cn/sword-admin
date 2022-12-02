<?php

namespace app\admin\service;

use app\model\system\MenuModel;

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

        $model = MenuModel::newQuery()
            ->order('sort')
            ->where('appid', 1)
            ->where('status', 1);

        if($exclude)
            $model->whereNotIn('rid', $exclude);

        $list = $model->column('*','id');

        return MenuService::recursionMenu($list, 0);
    }

    /**
     * @return void
     */
    public function checkAuth(int $uid)
    {

        $request = request();
        $appName = app('http')->getName();
        $controller =  $request->controller();
        $action = $request->action();

        //当前请求的Path
        $path =  "$appName/$controller/$action";


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