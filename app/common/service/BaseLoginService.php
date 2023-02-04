<?php

namespace app\common\service;

/**
 * 登录服务基类
 * 新的登录方式请继承该类并配置到 config('app.login_service')数组中
 */
class BaseLoginService
{

    //登录后保存到jwt中的数据
    public array $jwtData = [];

    //登录后返回前端的用户信息
    public array $userInfo = [];

    //是否登录成功
    public bool $success = false;

    /**
     * 登录
     * @param array $params
     * @return bool 登录是否成功
     */
    public function login(array $params): bool
    {
        return true;
    }
}