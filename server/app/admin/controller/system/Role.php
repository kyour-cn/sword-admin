<?php

namespace app\admin\controller\system;

use app\admin\services\RoleService;
use app\BaseController;
use support\Request;
use support\Response;

/**
 * @api
 */
class Role extends BaseController
{
    public function list(Request $req): Response
    {
        $serv = new RoleService();
        $res = $serv->getList($req->get());
        return $this->success(data: $res);
    }

    public function add(Request $req): Response
    {
        $serv = new RoleService();
        $serv->create($req->post());
        return $this->success();
    }

    public function edit(Request $req): Response
    {
        $serv = new RoleService();
        $serv->update($req->post());
        return $this->success();
    }

    public function permission(Request $req): Response
    {
        $serv = new RoleService();
        $serv->updatePermissions(
            (int)$req->post('id'),
            (array)$req->post('permission_ids', [])
        );
        return $this->success();
    }

    public function delete(Request $req): Response
    {
        $serv = new RoleService();
        $serv->delete((array)$req->post('ids', []));
        return $this->success();
    }
}
