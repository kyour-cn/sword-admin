<?php
namespace app\admin\controller;

use app\admin\service\RbacService;
use app\BaseController;
use app\middleware\JwtMiddleware;

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
        $service = new RbacService();
        $menu = $service->getUserMenu();

        return $this->withData(0, 'success', [
            'menu' => $menu
        ]);
    }

}
