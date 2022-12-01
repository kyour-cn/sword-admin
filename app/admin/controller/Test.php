<?php
namespace app\admin\controller;

use app\admin\service\AuthService;
use app\BaseController;

class Test extends BaseController
{
    public function index()
    {
        $service = new AuthService();

        $menu = $service->getUserMenu();

        return $this->withData(0, 'success', [
            'menu' => $menu
        ]);
    }
}
