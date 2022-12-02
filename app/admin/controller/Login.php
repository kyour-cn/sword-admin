<?php
namespace app\admin\controller;

use app\BaseController;
use app\model\system\UserModel;
use thans\jwt\facade\JWTAuth;

class Login extends BaseController
{
    public function index()
    {
        $username = input('username');
        $password = input('password');

        $field = 'id,username,realname,mobile,avatar,status';

        //登录判断
        $user = UserModel::where('id', 1)
            ->where('username|mobile', $username)
            ->where('password', $password)
            ->field($field)
            ->find();
        if(!$user){
            return $this->withData(1, '账号或密码不正确');
        }


        //更新登录时间
        $user->save([
            'login_time' => time()
        ]);

        //开始创建token
        $token = JWTAuth::builder([
            'uid' => $user->id
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
