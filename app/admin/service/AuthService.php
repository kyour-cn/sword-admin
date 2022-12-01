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

}