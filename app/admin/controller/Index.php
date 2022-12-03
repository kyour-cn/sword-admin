<?php
namespace app\admin\controller;

use app\BaseController;
use app\middleware\JwtMiddleware;
use app\service\AuthService;
use thans\jwt\facade\JWTAuth;

class Index extends BaseController
{
    protected $middleware = [
        JwtMiddleware::class
    ];

    public function index()
    {
        echo "hello admin.";
    }

    /**
     * 获取用户菜单
     */
    public function menu()
    {
        //获取当前用户角色
        $payload = JWTAuth::auth();
        $roleId = $payload['role']->getValue();
        $uid = $payload['uid']->getValue();

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
