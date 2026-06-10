<?php

namespace app\admin\controller\system;

use app\admin\services\ConfigService;
use app\BaseController;
use support\Request;
use support\Response;

/**
 * 系统配置
 * @api
 */
class Config extends BaseController
{
    public function list(): Response
    {
        $res = ConfigService::instance()->getList();
        return $this->success(data: $res);
    }

    public function detail(Request $req): Response
    {
        $res = ConfigService::instance()->detail((string)$req->get('key'));
        return $this->success(data: $res);
    }

    public function save(Request $req): Response
    {
        ConfigService::instance()->save(
            (string)$req->post('key'),
            $req->post('value', []),
            $req->jwt ?? []
        );
        return $this->success();
    }
}
