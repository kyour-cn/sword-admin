<?php

namespace app\service;

use app\exception\MsgException;
use app\model\UserModel;

class UserService
{

    /**
     * 新增
     * @param array $data
     * @return UserModel
     * @throws MsgException
     */
    public function add(array $data): UserModel
    {
        //密码转md5
        if(!empty($data['password'])){
            $data['password'] = md5($data['password']);
        }else{
            unset($data['password']);
        }

        $model = new UserModel();
        //验证用户是否已存在
        if($check = $this->checkExits($data)){
            throw new MsgException('用户已存在，请勿重复添加：'. $check['field']);
        }

        $model->save($data);
        return $model;
    }

    /**
     * 判断用户是否已存在
     * @param array $param 检测数据
     * @param int|null $uid 修改用户时传入uid
     * @return ?array
     */
    public function checkExits(array $param, int $uid = null): ?array
    {
        if($uid){
            if(!empty($param['username'])){
                $check = UserModel::where('username', $param['username'])
                    ->where('id', '<>', $uid) //非本用户
                    ->value('id');
                if($check)
                    return ['id' => $check, 'field' => 'username'];
            }

            if(!empty($param['mobile'])){
                $check = UserModel::where('mobile', $param['mobile'])
                    ->where('id', '<>', $uid) //非本用户
                    ->value('id');
                if($check)
                    return ['id' => $check, 'field' => 'mobile'];
            }
        }else{
            if(!empty($param['username'])){
                $check = UserModel::where('username', $param['username'])->value('id');
                if($check)
                    return ['id' => $check, 'field' => 'username'];
            }

            if(!empty($param['mobile'])){
                $check = UserModel::where('mobile', $param['mobile'])->value('id');
                if($check)
                    return ['id' => $check, 'field' => 'mobile'];
            }
        }

        return null;
    }
}