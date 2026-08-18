<?php

namespace app\admin\controller\system;

use app\admin\services\AppService;
use app\BaseController;
use support\Request;
use support\Response;

/**
 * 应用管理
 * @api
 */
class App extends BaseController
{
    public function list(Request $req): Response
    {
        $serv = new AppService();
        $res = $serv->getList($req->get());
        return $this->success(data: $res);
    }

    public function add(Request $req): Response
    {
        $serv = new AppService();
        $serv->create($req->post());
        return $this->success();
    }

    public function edit(Request $req): Response
    {
        $serv = new AppService();
        $serv->update($req->post());
        return $this->success();
    }

    public function delete(Request $req): Response
    {
        $serv = new AppService();
        $serv->delete((array)$req->post('ids', []));
        return $this->success();
    }
}
