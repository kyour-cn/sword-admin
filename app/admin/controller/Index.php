<?php
namespace app\admin\controller;

use app\BaseController;
use app\middleware\JwtMiddleware;
use app\service\AuthService;

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
        $service = new AuthService();
        $menu = $service->getUserMenu();

        return $this->withData(0, 'success', [
            'menu' => $menu
        ]);
    }

}
