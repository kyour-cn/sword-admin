<?php

namespace app\common\services;

use app\admin\services\MenuService;
use app\common\exception\BusinessException;
use app\model\Menu;
use app\model\MenuApi;
use app\model\User;
use support\Cache;

class AuthService extends BaseService
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
            throw new BusinessException('账号或密码不正确');
        }

        // 登录成功，清空锁
        Cache::delete($key);

        return $user;
    }

    public function getMenu(User $userInfo, int $appID = 0): array
    {
        if ($userInfo->userRole->isEmpty()) {
            throw new BusinessException('用户角色不存在');
        }

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
            foreach (explode(',', $v->role->rules ?? '') as $ruleId) {
                $mIds[] = (int) trim($ruleId);
            }
        }

        $query = Menu::with('menuApi')
            ->where('type', 'menu')
            ->where('app_id', $appID);

        // 非管理员，根据角色权限筛选菜单
        if (!$isAdmin) {
            if (empty($mIds)) {
                throw new BusinessException('暂无权限');
            }
            $query->whereIn('id', $mIds);
        }

        $menus = $query->get();
        if ($menus->isEmpty()) {
            throw new BusinessException('暂无可用菜单');
        }

        $list = $menus->toArray();

        return MenuService::instance()
            ->recursionMenu($list, 0);
    }

    public function getPermissions(User $userInfo, int $appID = 0): array
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
            foreach (explode(',', $v->role->rules ?? '') as $ruleId) {
                $mIds[] = (int) trim($ruleId);
            }
        }

        $query = MenuApi::where('tag', '<>', '')
            ->where('app_id', $appID);

        // 非管理员，根据角色权限筛选菜单API
        if (!$isAdmin) {
            if (empty($mIds)) {
                return [];
            }
            $query->whereIn('menu_id', $mIds);
        }

        $apis = $query->select(['id', 'tag'])->get();
        if ($apis->isEmpty()) {
            return [];
        }
        return array_column($apis->toArray(), 'tag');
    }

    /**
     * 检查路径权限
     * 参照Go版本CheckPath实现：根据menu_api.path查询关联菜单，再通过用户角色的rules匹配菜单权限
     * @param array $claims JWT claims
     * @param \Webman\Http\Request $request
     * @return bool
     */
    public function checkPath(array $claims, \Webman\Http\Request $request): bool
    {
        $path = $request->path();

        // 根据路由地址查询关联的菜单（通过menu_api表关联）
        $menuIds = (new MenuApi())
            ->where('path', $path)
            ->select(['menu_id'])
            ->get()
            ->pluck('menu_id')
            ->unique()
            ->toArray();

        // 路由未在menu_api中定义，不限制
        if (empty($menuIds)) {
            return true;
        }

        // 查询关联菜单的app_id
        $menus = (new Menu())
            ->whereIn('id', $menuIds)
            ->select(['id', 'app_id'])
            ->get();

        if ($menus->isEmpty()) {
            return true;
        }

        // 获取用户ID
        $userId = $claims['id'] ?? 0;
        if (empty($userId)) {
            return false;
        }

        // 查询用户关联的角色
        $user = (new User())->find($userId);
        if (empty($user)) {
            return false;
        }

        $roles = (new \app\model\UserRole())
            ->where('user_id', $userId)
            ->get();

        if ($roles->isEmpty()) {
            return false;
        }

        $roleIds = $roles->pluck('role_id')->toArray();
        $roleList = (new \app\model\Role())
            ->whereIn('id', $roleIds)
            ->where('status', 1)
            ->select(['id', 'app_id', 'is_admin', 'rules'])
            ->get();

        // 构建权限规则集，同时检查管理员角色
        $ruleSet = [];
        foreach ($roleList as $role) {
            // 管理员角色拥有所有权限
            if ($role->is_admin == 1) {
                foreach ($menus as $menu) {
                    if ($menu->app_id == $role->app_id) {
                        return true;
                    }
                }
            }
            // 普通角色收集规则ID
            if (!empty($role->rules)) {
                foreach (explode(',', $role->rules) as $ruleId) {
                    $ruleSet[(int)$ruleId] = true;
                }
            }
        }

        // 判断是否有匹配的权限
        foreach ($menus as $menu) {
            if (isset($ruleSet[$menu->id])) {
                return true;
            }
        }

        return false;
    }

}