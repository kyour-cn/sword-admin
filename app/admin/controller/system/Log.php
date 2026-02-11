<?php

namespace app\admin\controller\system;

use app\admin\services\LogService;
use app\BaseController;
use support\Request;
use support\Response;

/**
 * 日志
 * @api
 */
class Log extends BaseController
{

    /**
     * @param Request $req
     * @return Response
     * @api
     */
    public function typeList(Request $req): Response
    {
        $serv = new LogService();
        $res = $serv->getTypeList();
        return $this->success(data: $res);
    }

    /**
     * @param Request $req
     * @return Response
     * @api
     */
    public function list(Request $req): Response
    {
        $serv = new LogService();
        $res = $serv->getList($req->get());
        return $this->success(data: $res);
    }

    /**
     * @param Request $req
     * @return Response
     * @api
     */
    public function logStat(Request $req): Response
    {
        $serv = new LogService();
        $res = $serv->getStat($req->get());
        return $this->success(data: $res);
    }

}
