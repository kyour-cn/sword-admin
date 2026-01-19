<?php

namespace app\admin\controller\system;

use app\admin\services\UserService;
use app\BaseController;
use support\Request;
use support\Response;

/**
 * @api
 */
class User extends BaseController
{
    public function list(Request $req): Response
    {
        $serv = new UserService();
        $res = $serv->getList($req->get());
        return $this->success(data: $res);
    }

    public function export(Request $req): Response
    {
        $serv = new UserService();
        $res = $serv->export($req->get());
        return $this->success(data: $res);
    }

    public function add(Request $req): Response
    {
        $serv = new UserService();
        $serv->create($req->post());
        return $this->success();
    }

    public function edit(Request $req): Response
    {
        $serv = new UserService();
        $serv->update($req->post());
        return $this->success();
    }

    public function delete(Request $req): Response
    {
        $serv = new UserService();
        $serv->delete($req->post('ids'));
        return $this->success();
    }

    public function resetPassword(Request $req): Response
    {
        $serv = new UserService();
        $serv->resetPassword($req->post('id'), $req->post('new_password'));
        return $this->success();
    }
}