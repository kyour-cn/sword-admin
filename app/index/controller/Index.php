<?php
namespace app\index\controller;

use app\BaseController;
use support\Request;

class Index extends BaseController
{
    public function index()
    {
        return 'hello world!';
    }

    public function test(Request $request)
    {
        $controller = explode('\\',$request->controller);

        $path = $request->app.'/'.$controller[count($controller) -1].'/'.$request->action;

        return $this->withData(0, "success", $path);
    }
}
