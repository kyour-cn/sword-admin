<?php
namespace app\admin\controller;

use app\admin\service\RbacService;
use app\BaseController;

class Test extends BaseController
{
    public function index()
    {
        $service = new RbacService();

        $menu = $service->getUserMenu();

        return $this->withData(0, 'success', [
            'menu' => $menu
        ]);
    }
}
