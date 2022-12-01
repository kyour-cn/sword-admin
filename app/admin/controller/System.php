<?php
namespace app\admin\controller;

use app\admin\service\AppService;
use app\admin\service\MenuService;
use app\admin\service\RoleService;
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
        $service = new AppService();
        $list = $service->getList();
        return $this->withData(200, 'success', $list);
    }

    /**
     * 菜单列表
     */
    public function menuList()
    {
        $service = new MenuService();
        $menu = $service->getList();
        return $this->withData(0, 'success', $menu);
    }

    /**
     * 编辑菜单
     */
    public function editMenu()
    {
        $data = input();
        $service = new MenuService();
        $res = $service->createMenu($data);
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除菜单
     */
    public function deleteMenu()
    {
        $ids = input('ids');
        $service = new MenuService();
        $res = $service->deleteMenu($ids);
        return $this->withData(0, '删除成功', $res);
    }

    /**
     * 角色菜单
     */
    public function roleList()
    {
        $service = new RoleService();
        $list = $service->getList();
        return $this->withData(200, 'success', $list);
    }
}
