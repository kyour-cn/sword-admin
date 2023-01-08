<?php
namespace plugin\admin\app\controller;

use App\BaseController;
use App\common\service\AuthService;
use plugin\admin\app\service\MenuService;
use Tinywan\Jwt\JwtToken;

class Index extends BaseController
{
    /**
     * 控制器中间件
     */
    const middleware = [];

    public function index()
    {
        return "hello admin.";
    }

    /**
     * 获取用户菜单
     */
    public function menu()
    {
        //获取当前用户角色
        $roleId = JwtToken::getExtendVal('role');
        $uid = JwtToken::getExtendVal('id');

        $menuService = new MenuService();
        $authService = new AuthService();
        if($authService->isRootUser($uid)){
            $menu = $menuService->getRootMenu(1);
        }else{
            $menu = $menuService->getUserMenu($roleId);
        }

        return $this->withData(0, 'success', [
            'menu' => $menu
        ]);
    }

}
