<?php

namespace app\admin\controller;

use app\admin\services\UserService;
use app\BaseController;
use support\Request;
use support\Response;

/**
 * 当前登录用户相关接口
 * @api
 */
class User extends BaseController
{
    public function info(Request $req): Response
    {
        $serv = new UserService();

        if ($req->method() === 'POST') {
            $serv->updateCurrentUser($req->post());
            return $this->success('保存成功', $serv->getCurrentUser());
        }

        return $this->success(data: $serv->getCurrentUser());
    }

    public function password(Request $req): Response
    {
        $serv = new UserService();
        $serv->updateCurrentPassword($req->post());

        return $this->success('密码修改成功');
    }

    public function taskList(Request $req): Response
    {
        $serv = new UserService();
        $res = $serv->getTaskList();
        return $this->success(data: $res);
    }
}
