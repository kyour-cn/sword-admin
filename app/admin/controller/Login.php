<?php
namespace app\admin\controller;

use app\BaseController;
use thans\jwt\facade\JWTAuth;

class Login extends BaseController
{
    public function index()
    {
        //登录判断

        //开始创建token
        $token = JWTAuth::builder([
            'uid' => 1
        ]);

        return $this->withData(0, 'success', $token);
    }

    public function state()
    {
        $payload = JWTAuth::auth();
        return $this->withData(0, 'success', $payload);
    }

}
