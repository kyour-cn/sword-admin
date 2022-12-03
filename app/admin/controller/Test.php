<?php
namespace app\admin\controller;

use app\admin\service\MenuService;
use app\BaseController;
use app\model\system\UserModel;
use app\service\AuthService;
use app\service\CodeService;

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

    public function code()
    {
        throw CodeService::makeException('API_AUTH_ERROR');
    }

    public function menu()
    {
        $service = new AuthService();
        $menu = $service->getUserMenu(1);

        return $this->withData(0, '111', $menu);
    }

//    public function model()
//    {
//        $user = UserModel::where('realname', 'like', "%1%")->select();
//
//        return $this->withData(0, '111', $user);
//    }
}
