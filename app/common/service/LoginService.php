<?php

namespace app\common\service;

use app\common\model\UserModel;
use think\db\exception\DbException;

class LoginService extends BaseLoginService
{

    /**
     * 登录
     * @param array $params
     * @return bool
     * @throws DbException
     */
    public function login(array $params): bool
    {
        $field = 'id,username,realname,mobile,avatar,status,role_id';

        //登录判断
        $user = UserModel::where('username|mobile', $params['username'])
            ->where('password', $params['password'])
            ->field($field)
            ->find();
        if(!$user){
            return false;
        }

        //更新登录时间
        $user->save([
            'login_time' => time()
        ]);

        $this->jwtData = [
            'id' => $user->id,
            'name' => $user->realname,
            'role' => $user->role_id,
            'login_type' => 'user' //默认用户标识
        ];

        $this->userInfo = $user->toArray();

        $this->userInfo['login_type'] = 'user';//默认用户标识

        $this->success = true;

        return true;
    }

}