<?php

namespace app\common\services;

use app\admin\services\MenuService;
use app\common\exception\BusinessException;
use app\model\Menu;
use app\model\MenuApi;
use app\model\Role;
use app\model\RolePermission;
use app\model\User;
use app\model\UserRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use support\Cache;

class AuthService extends BaseService
{

    /**
     * 注册新账号。注册阶段只创建用户数据，不写入用户角色关联。
     * @param array $data
     * @return User
     * @throws BusinessException
     */
    public function register(array $data): User
    {
        $username = trim((string)($data['username'] ?? ''));
        $password = (string)($data['password'] ?? '');
        $confirmPassword = (string)($data['confirm_password'] ?? '');
        $nickname = trim((string)($data['nickname'] ?? ''));
        $mobile = trim((string)($data['mobile'] ?? ''));

        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]{2,31}$/', $username)) {
            throw new BusinessException('账号须为3-32位字母、数字、下划线、点或短横线');
        }
        if (strlen($password) < 8 || strlen($password) > 64 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            throw new BusinessException('请输入包含英文、数字的8-64位密码');
        }
        if ($password !== $confirmPassword) {
            throw new BusinessException('两次输入密码不一致');
        }
        if (!preg_match('/^[^\r\n]{1,32}$/u', $nickname)) {
            throw new BusinessException('请输入1-32个字符的昵称');
        }
        if ($mobile !== '' && !preg_match('/^1\d{10}$/', $mobile)) {
            throw new BusinessException('请输入合法的手机号');
        }
        if (User::withTrashed()->where('username', $username)->exists()) {
            throw new BusinessException('登录账号已存在');
        }

        $user = new User();
        $user->fill([
            'username' => $username,
            'password' => md5($password),
            'nickname' => $nickname,
            'mobile' => $mobile,
            'avatar' => '',
            'status' => 1,
        ]);

        try {
            $user->save();
        } catch (QueryException $e) {
            // 并发注册相同账号时，统一转换为明确的业务提示。
            if (User::withTrashed()->where('username', $username)->exists()) {
                throw new BusinessException('登录账号已存在');
            }
            throw $e;
        }

        return $user;
    }

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

        $roles = $this->getActiveRoles($userInfo, $appID);
        $isAdmin = $roles->contains(static fn (Role $role): bool => $role->is_admin == 1);

        $query = Menu::with('menuApi')
            ->where('type', 'menu')
            ->where('app_id', $appID);

        // 非管理员，根据角色权限筛选菜单
        if (!$isAdmin) {
            $roleIds = $roles->pluck('id')->map(static fn ($id): int => (int)$id)->all();
            if ($roleIds === []) {
                throw new BusinessException('暂无权限');
            }

            $permissionIds = RolePermission::whereIn('role_id', $roleIds)
                ->pluck('menu_id')
                ->map(static fn ($id): int => (int)$id)
                ->unique()
                ->values()
                ->all();
            $menuIds = $this->expandMenuIds($appID, $permissionIds);
            if ($menuIds === []) {
                throw new BusinessException('暂无权限');
            }

            $query->whereIn('id', $menuIds);
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
        $roles = $this->getActiveRoles($userInfo, $appID);
        $isAdmin = $roles->contains(static fn (Role $role): bool => $role->is_admin == 1);

        $query = MenuApi::where('tag', '<>', '')
            ->where('app_id', $appID);

        // 非管理员，根据角色权限筛选菜单API
        if (!$isAdmin) {
            $roleIds = $roles->pluck('id')->map(static fn ($id): int => (int)$id)->all();
            if ($roleIds === []) {
                return [];
            }

            $permissionIds = RolePermission::whereIn('role_id', $roleIds)
                ->pluck('menu_id')
                ->unique()
                ->values()
                ->all();
            if ($permissionIds === []) {
                return [];
            }

            $query->whereIn('menu_id', $permissionIds);
        }

        return $query->pluck('tag')->unique()->values()->all();
    }

    /**
     * 检查路径权限
     * 根据menu_api.path查询关联菜单，再通过用户角色及关系表匹配菜单权限。
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

        $roles = UserRole::query()
            ->where('user_id', $userId)
            ->get();

        if ($roles->isEmpty()) {
            return false;
        }

        $roleIds = $roles->pluck('role_id')->toArray();
        $roleList = Role::query()
            ->whereIn('id', $roleIds)
            ->where('status', 1)
            ->select(['id', 'app_id', 'is_admin'])
            ->get();

        if ($roleList->isEmpty()) {
            return false;
        }

        $adminAppIds = [];
        $normalRoleApps = [];
        foreach ($roleList as $role) {
            if ($role->is_admin == 1) {
                $adminAppIds[(int)$role->app_id] = true;
                continue;
            }

            $normalRoleApps[(int)$role->id] = (int)$role->app_id;
        }

        $menuMap = [];
        foreach ($menus as $menu) {
            $menuMap[(int)$menu->id] = $menu;
            if (isset($adminAppIds[(int)$menu->app_id])) {
                return true;
            }
        }

        if ($normalRoleApps === []) {
            return false;
        }

        $permissions = RolePermission::whereIn('role_id', array_keys($normalRoleApps))
            ->whereIn('menu_id', array_keys($menuMap))
            ->select(['role_id', 'menu_id'])
            ->get();

        foreach ($permissions as $permission) {
            $roleAppId = $normalRoleApps[(int)$permission->role_id] ?? 0;
            $menu = $menuMap[(int)$permission->menu_id] ?? null;
            if ($menu && $roleAppId === (int)$menu->app_id) {
                return true;
            }
        }

        return false;
    }

    private function getActiveRoles(User $userInfo, int $appID): Collection
    {
        $roleIds = $userInfo->userRole
            ->pluck('role_id')
            ->map(static fn ($id): int => (int)$id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($roleIds === []) {
            return new Collection();
        }

        return Role::whereIn('id', $roleIds)
            ->where('app_id', $appID)
            ->where('status', 1)
            ->select(['id', 'app_id', 'is_admin'])
            ->get();
    }

    /**
     * 将已授权节点向上补齐到根菜单，避免只授权操作节点时菜单树断层。
     */
    private function expandMenuIds(int $appID, array $permissionIds): array
    {
        if ($permissionIds === []) {
            return [];
        }

        $nodes = Menu::where('app_id', $appID)
            ->select(['id', 'pid', 'type'])
            ->get()
            ->keyBy('id');
        $menuIds = [];

        foreach ($permissionIds as $permissionId) {
            $nodeId = (int)$permissionId;
            $visited = [];
            while ($nodeId > 0 && !isset($visited[$nodeId])) {
                $visited[$nodeId] = true;
                $node = $nodes->get($nodeId);
                if (!$node) {
                    break;
                }
                if ($node->type === 'menu') {
                    $menuIds[(int)$node->id] = (int)$node->id;
                }
                $nodeId = (int)$node->pid;
            }
        }

        return array_values($menuIds);
    }

}
