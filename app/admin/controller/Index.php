<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\service\AuthService;
use Tinywan\Jwt\JwtToken;

class Index extends BaseController
{
    public array $middleware = [];

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

        $service = new AuthService();
        if($service->isRootUser($uid)){
            $menu = $service->getRootMenu(1);
        }else{
            $menu = $service->getUserMenu($roleId);
        }

        return $this->withData(0, 'success', [
            'menu' => $menu
        ]);
    }

}
