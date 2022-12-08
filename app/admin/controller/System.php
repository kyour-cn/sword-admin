<?php
namespace app\admin\controller;

use app\admin\service\MenuService;
use app\BaseController;
use app\common\service\AppService;
use app\common\service\RoleService;
use app\common\service\RuleService;
use app\common\service\UserService;
use support\Request;

class System extends BaseController
{
    public array $middleware = [
        \App\common\middleware\AuthMiddleware::class
    ];

    public function index()
    {
        echo "hello admin.";
    }

    /**
     * 应用列表
     */
    public function appList(Request $request)
    {
        $params = $request->all();
        $service = new AppService();
        $list = $service->getList($params);
        return $this->withData(200, 'success', $list);
    }

    /**
     * 编辑、新增应用
     */
    public function editApp(Request $request)
    {
        $data = $request->all();
        $service = new AppService();
        if(!empty($data['id'])){
            $res = $service->edit($data);
        }else{
            $res = $service->add($data);
        }
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除应用
     */
    public function deleteApp(Request $request)
    {
        $ids = $request->post('ids');
        $service = new AppService();
        $res = $service->delete($ids);
        return $this->withData(0, '删除成功', $res);
    }

    /**
     * 菜单列表
     */
    public function menuList(Request $request)
    {
        $params = $request->all();
        $service = new MenuService();
        $menu = $service->getList($params);
        return $this->withData(0, 'success', $menu);
    }

    /**
     * 编辑、新增菜单
     */
    public function editMenu(Request $request)
    {
        $data = $request->all();
        $service = new MenuService();
        if(!empty($data['id'])){
            $res = $service->edit($data);
        }else{
            $res = $service->add($data);
        }
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除菜单
     */
    public function deleteMenu(Request $request)
    {
        $ids = $request->post('ids');
        $service = new MenuService();
        $res = $service->delete($ids);
        return $this->withData(0, '删除成功', $res);
    }

    /**
     * 权限菜单
     */
    public function ruleList(Request $request)
    {
        $params = $request->all();
        $service = new RuleService();
        $list = $service->getList($params);
        return $this->withData(200, 'success', $list);
    }

    /**
     * 编辑、新增权限
     */
    public function editRule(Request $request)
    {
        $data = $request->all();
        $service = new RuleService();
        if(!empty($data['id'])){
            $res = $service->edit($data);
        }else{
            $res = $service->add($data);
        }
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除权限
     */
    public function deleteRule(Request $request)
    {
        $ids = $request->post('ids');
        $service = new RuleService();
        $res = $service->delete($ids);
        return $this->withData(0, '删除成功', $res);
    }

    /**
     * 角色菜单
     */
    public function roleList(Request $request)
    {
        $params = $request->all();
        $service = new RoleService();

        //只显示手动创建的
        $params['is_auto'] = 0;

        $list = $service->getList($params);
        return $this->withData(200, 'success', $list);
    }

    /**
     * 编辑、新增角色
     */
    public function editRole(Request $request)
    {
        $data = $request->all();
        $service = new RoleService();
        if(!empty($data['id'])){
            $res = $service->edit($data);
        }else{
            $res = $service->add($data);
        }
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除角色
     */
    public function deleteRole(Request $request)
    {
        $ids = $request->post('ids');
        $service = new RoleService();
        $res = $service->delete($ids);
        return $this->withData(0, '删除成功', $res);
    }

    /**
     * 用户
     */
    public function userList(Request $request)
    {
        $params = $request->all();
        $service = new UserService();
        $list = $service->getList($params);
        return $this->withData(200, 'success', $list);
    }

    /**
     * 编辑、新增用户
     */
    public function editUser(Request $request)
    {
        $data = $request->all();
        $service = new UserService();
        if(!empty($data['id'])){
            $res = $service->edit($data);
        }else{
            $res = $service->add($data);
        }
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除用户
     */
    public function deleteUser(Request $request)
    {
        $ids = $request->post('ids');
        $service = new UserService();
        $res = $service->delete($ids);
        return $this->withData(0, '删除成功', $res);
    }
}
