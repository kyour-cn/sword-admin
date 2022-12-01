<?php
namespace app\admin\controller;

use app\admin\service\AppService;
use app\admin\service\MenuService;
use app\admin\service\RoleService;
use app\admin\service\RuleService;
use app\admin\service\UserService;
use app\BaseController;
use app\middleware\JwtMiddleware;

class System extends BaseController
{
    protected $middleware = [
        JwtMiddleware::class
    ];

    public function index()
    {
        echo "hello admin.";
    }

    /**
     * 应用列表
     */
    public function appList()
    {
        $params = input();
        $service = new AppService();
        $list = $service->getList($params);
        return $this->withData(200, 'success', $list);
    }

    /**
     * 编辑、新增应用
     */
    public function editApp()
    {
        $data = input();
        $service = new AppService();
        $res = $service->editOrAdd($data);
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除应用
     */
    public function deleteApp()
    {
        $ids = input('ids');
        $service = new AppService();
        $res = $service->delete($ids);
        return $this->withData(0, '删除成功', $res);
    }

    /**
     * 菜单列表
     */
    public function menuList()
    {
        $params = input();
        $service = new MenuService();
        $menu = $service->getList($params);
        return $this->withData(0, 'success', $menu);
    }

    /**
     * 编辑、新增菜单
     */
    public function editMenu()
    {
        $data = input();
        $service = new MenuService();
        $res = $service->editOrAdd($data);
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除菜单
     */
    public function deleteMenu()
    {
        $ids = input('ids');
        $service = new MenuService();
        $res = $service->delete($ids);
        return $this->withData(0, '删除成功', $res);
    }

    /**
     * 权限菜单
     */
    public function ruleList()
    {
        $params = input();
        $service = new RuleService();
        $list = $service->getList($params);
        return $this->withData(200, 'success', $list);
    }

    /**
     * 编辑、新增权限
     */
    public function editRule()
    {
        $data = input();
        $service = new RuleService();
        $res = $service->editOrAdd($data);
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除权限
     */
    public function deleteRule()
    {
        $ids = input('ids');
        $service = new RuleService();
        $res = $service->delete($ids);
        return $this->withData(0, '删除成功', $res);
    }

    /**
     * 角色菜单
     */
    public function roleList()
    {
        $params = input();
        $service = new RoleService();
        $list = $service->getList($params);
        return $this->withData(200, 'success', $list);
    }

    /**
     * 编辑、新增角色
     */
    public function editRole()
    {
        $data = input();
        $service = new RoleService();
        $res = $service->editOrAdd($data);
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除角色
     */
    public function deleteRole()
    {
        $ids = input('ids');
        $service = new RoleService();
        $res = $service->delete($ids);
        return $this->withData(0, '删除成功', $res);
    }

    /**
     * 用户
     */
    public function userList()
    {
        $params = input();
        $service = new UserService();
        $list = $service->getList($params);
        return $this->withData(200, 'success', $list);
    }

    /**
     * 编辑、新增用户
     */
    public function editUser()
    {
        $data = input();
        $service = new UserService();
        $res = $service->editOrAdd($data);
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除用户
     */
    public function deleteUser()
    {
        $ids = input('ids');
        $service = new UserService();
        $res = $service->delete($ids);
        return $this->withData(0, '删除成功', $res);
    }
}
