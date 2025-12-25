<?php

namespace app\admin\controller;

use app\model\User;
use support\Request;

class Auth extends \app\common\controller\Auth
{
    public function index(Request $request)
    {
        $model = new User();
        $user = $model->where('username', 'admin')->first();

        return $this->withData(0, '登录成功', $user);
    }

}
