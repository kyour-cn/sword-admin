<?php

namespace app\common\service;

use app\common\exception\BusinessException;
use app\model\Menu;
use app\model\MenuApi;
use app\model\User;
use support\Cache;

class AuthService
{

    /**
     * @param string $username
     * @param string $password
     * @return User
     * @throws BusinessException
     */
    public function login(string $username, string $password): User
    {
        $key = "login_lock_$username";
        $lock = Cache::get($key);
        if (!$lock) {
            $lock = 0;
        }

        // 10秒登录失败次数超过3次，禁止登录
        if ($lock > 3) {
            throw new BusinessException('登录失败次数过多，请稍后再试');
        }

        $model = new User();
        $user = $model->with(['userRole', 'userRole.role', 'userRole.role.app'])
            ->where('username', $username)
            ->where('password', $password)
            ->select(['id', 'nickname', 'username', 'avatar', 'created_at', 'status'])
            ->first();
        if (empty($user)) {
            Cache::set($key, $lock + 1, 10);
            throw new BusinessException('用户不存在');
        }

        // 登录成功，清空锁
        Cache::delete($key);

        return $user;
    }

    public function getMenu(User $userInfo, int $appID = 0): array
    {
        $isAdmin = false;

        // 筛选指定appid的菜单列表
        $mIds = [];
        foreach ($userInfo->userRole as $v) {
            if ($v->role->app_id != $appID) {
                continue;
            }
            if ($v->role->is_admin == 1) {
                $isAdmin = true;
                break;
            }
            $mIds = array_merge($mIds, explode(',', $v->role->rules));
        }

        $conditions = [
            ['type', '=', 'menu'],
        ];

        if($appID) {
            $conditions[] = ['app_id', '=', $appID];
        }

        // 非管理员，根据角色权限筛选菜单
        if(!$isAdmin) {
            if(empty($mIds)) {
                throw new BusinessException('暂无权限');
            }
            $conditions[] = ['id', 'in', $mIds];
        }

        $menus = (new Menu)
            ->with('menuApi')
            ->where($conditions)
            ->get();
        if($menus->isEmpty()) {
            throw new BusinessException('暂无可用菜单');
        }

        $list = $menus->toArray();
        return $this->recursionMenu($list, 0);
    }

    public function getPermission(User $userInfo, int $appID = 0): array
    {
        $isAdmin = false;

        // 筛选指定appid的菜单列表
        $mIds = [];
        foreach ($userInfo->userRole as $v) {
            if ($v->role->app_id != $appID) {
                continue;
            }
            if ($v->role->is_admin == 1) {
                $isAdmin = true;
                break;
            }
            $mIds = array_merge($mIds, explode(',', $v->role->rules));
        }

        $conditions = [
            ['tag', '<>', ''],
        ];

        // 非管理员，根据角色权限筛选菜单
        if(!$isAdmin) {
            if(empty($mIds)) {
                throw new BusinessException('暂无权限');
            }
            $conditions[] = ['id', 'in', $mIds];
        }

        if($appID) {
            $conditions[] = ['app_id', '=', $appID];
        }

        // 非管理员，根据角色权限筛选菜单
        if(!$isAdmin) {
            if(empty($mIds)) {
                return [];
            }
            $conditions[] = ['id', 'in', $mIds];
        }

        $apis = (new MenuApi)
            ->where($conditions)
            ->select(['id', 'tag'])
            ->get();
        if($apis->isEmpty()) {
            return [];
        }
        return array_column($apis->toArray(), 'tag');
    }

    /**
     * 递归数组 -前端组件使用格式
     * @param $arr
     * @param $pid
     * @return array
     */
    private function recursionMenu(&$arr, $pid): array
    {
        $data = [];
        foreach($arr as $value){
            if($value['pid'] == $pid){
                $menu = [
                    'pid' => $value['pid'],
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'title' => $value['title'],
                    'path' => $value['path'],
                    'component' => $value['component'],
                    'sort' => $value['sort'],
                    'meta' => json_decode($value['meta'], true),
                    'appId' => $value['app_id'],
                    'apiList' => $value['menu_api'],
                ];

                $children = $this->recursionMenu($arr, $value['id']);
                if(!empty($children)){
                    $menu['children'] = $children;
                }
                $data[] = $menu;
            }
        }
        return $data;
    }
}