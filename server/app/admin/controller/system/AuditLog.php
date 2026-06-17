<?php

namespace app\admin\controller\system;

use app\admin\services\AuditLogService;
use app\BaseController;
use support\Request;
use support\Response;

/**
 * 操作审计
 * @api
 */
class AuditLog extends BaseController
{
    public function list(Request $req): Response
    {
        $serv = new AuditLogService();
        $res = $serv->getList($req->get());
        return $this->success(data: $res);
    }

    public function stat(Request $req): Response
    {
        $serv = new AuditLogService();
        $res = $serv->getStat($req->get());
        return $this->success(data: $res);
    }

    public function actions(Request $req): Response
    {
        $serv = new AuditLogService();
        $res = $serv->getActions();
        return $this->success(data: $res);
    }
}
