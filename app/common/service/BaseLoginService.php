<?php

namespace app\common\service;

use app\common\exception\MsgException;
use app\common\validate\LoginValidate;
use think\db\exception\DbException;

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

    //登录失败锁定时间，秒
    private int $lockTime = 5;

    /**
     * 登录
     * @param array $params
     * @return bool 登录是否成功
     */
    public function login(array $params): bool
    {
        return true;
    }

    /**
     * 检查登录 并返回登录成功的Login服务对象
     * @param array $params
     * @return BaseLoginService[]
     * @throws MsgException|DbException
     */
    public function checkLogin(array $params): array
    {
        //登录验证器
        $validate = new LoginValidate();
        if (!$validate->check($params)) {
            throw new MsgException($validate->getError());
        }

        //限制登录频率 -验锁
        if($lockTime = UtilsService::getLock("login:{$params['username']}")){
            $lockTime = $this->lockTime - (time() - $lockTime);
            throw new MsgException("请等待{$lockTime}秒后再试");
        }

        //密码转换
        if(empty($params['md5'])){
            $params['password'] = md5($params['password']);
        }

        //从配置中获取注册的登录服务
        $serviceList = config('app.login_service', []);

        $res = [];

        foreach ($serviceList as $serviceClass) {
            /**
             * 遍历并实例化登录服务
             * @var $service BaseLoginService
             */
            $service = new $serviceClass();
            if($service->login($params)){
                $res[] = $service;
            }
        }

        //没有成功的登录匹配 -默认登录服务
        if(!$res){
            $service = new LoginService();
            if($service->login($params)){
                $res[] = $service;
            }
        }

        if(!$res){
            //限制登录频率 -加锁
            UtilsService::setLock("login:{$params['username']}", $this->lockTime);
            throw new MsgException("账号或密码不正确");
        }

        return $res;
    }
}