<?php
namespace app\admin\controller;

use app\BaseController;
use app\model\system\UserModel;
use thans\jwt\facade\JWTAuth;

class Login extends BaseController
{
    public function index()
    {
        //登录判断
        $user = UserModel::where('id', 1)->find();
        if(!$user){
            return $this->withData(1, '账号或密码不正确');
        }

        //开始创建token
        $token = JWTAuth::builder([
            'uid' => 1
        ]);

        return $this->withData(0, 'success', [
            'token' => $token,
            'userInfo' => $user
        ]);
    }

    public function state()
    {
        $payload = JWTAuth::auth();
        return $this->withData(0, 'success', $payload);
    }

}
