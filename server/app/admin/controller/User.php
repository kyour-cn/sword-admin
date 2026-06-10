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
    public function taskList(Request $req): Response
    {
        $serv = new UserService();
        $res = $serv->getTaskList();
        return $this->success(data: $res);
    }
}
