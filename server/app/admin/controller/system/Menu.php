<?php

namespace app\admin\controller\system;

use app\admin\services\MenuService;
use app\BaseController;
use app\common\exception\BusinessException;
use support\Request;
use support\Response;

/**
 * 菜单管理
 * @api
 */
class Menu extends BaseController
{
    public function list(Request $req): Response
    {
        $serv = new MenuService();

        $appID = $req->get('app_id');
        if(empty($appID)) {
            throw new BusinessException('请选择应用');
        }

        $res = $serv->getMenuFromApp($appID);
        return $this->success(data: $res);
    }

    public function add(Request $req): Response
    {
        $serv = new MenuService();
        $data = $req->post();
        $data['title'] = $data['meta']['title'];
        $data['type'] = $data['meta']['type'];
        $data['meta'] = json_encode($data['meta'], JSON_UNESCAPED_UNICODE);
        $serv->create($data);
        return $this->success();
    }

    public function edit(Request $req): Response
    {
        $serv = new MenuService();
        $serv->update($req->post());
        return $this->success();
    }

    public function delete(Request $req): Response
    {
        $serv = new MenuService();
        $serv->delete($req->post('ids'));
        return $this->success();
    }
}
