<?php

namespace app\admin\controller\system;

use app\admin\services\ConfigFormService;
use app\BaseController;
use support\Request;
use support\Response;

/**
 * 配置表单管理
 * @api
 */
class ConfigForm extends BaseController
{
    public function list(Request $req): Response
    {
        $res = ConfigFormService::instance()->getList($req->get());
        return $this->success(data: $res);
    }

    public function add(Request $req): Response
    {
        ConfigFormService::instance()->create($req->post());
        return $this->success();
    }

    public function edit(Request $req): Response
    {
        ConfigFormService::instance()->update($req->post());
        return $this->success();
    }

    public function delete(Request $req): Response
    {
        ConfigFormService::instance()->delete($req->post('ids', []));
        return $this->success();
    }
}
