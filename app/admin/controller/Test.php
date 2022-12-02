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

    public function path()
    {
        $request = request();
        $appName = app('http')->getName();
        $controller =  $request->controller();
        $action = $request->action();

        return "$appName.$controller.$action";
    }
}
