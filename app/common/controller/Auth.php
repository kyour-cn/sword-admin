<?php

namespace app\common\controller;

use app\BaseController;
use app\common\exception\BusinessException;
use app\common\service\AuthService;
use app\common\utils\JwtUtils;
use app\model\User;
use support\Request;
use support\Response;

class Auth extends BaseController
{
    /**
     * 登录
     * @param Request $request
     * @return Response
     * @api
     */
    public function login(Request $request): Response
    {
        $params = $request->post();

        //密码转换
        if(empty($params['md5'])){
            $params['password'] = md5($params['password']);
        }

        $auth = new AuthService();
        try{
            $user = $auth->login($params['username'], $params['password']);
        }catch (\Exception $e){
            return $this->withData(1, $e->getMessage());
        }

        if($user->status != 1){
            return $this->withData(1, '账号异常或被锁定');
        }

        $apps = [];
        foreach ($user->userRole as $item){
            $apps[$item->role->app->id] = $item->role->app;
        }

        $expire = 86400;

        // 生成token
        $claims = [
            'id' => $user->id,
            'name'  => $user->nickname,
            'access_exp' => $expire,
        ];
        $token = JwtUtils::encode($claims);

        return $this->withData(0, '登录成功', [
            'userInfo' => $user,
            'apps' => $apps,
            'token' => $token,
            'expire' => $expire
        ]);
    }

    public function menu(Request $request): Response
    {
        $appID = $request->input('app_id', 0);

        $claims = JwtUtils::decodeFromRequest($request);

        $userInfo = (new User)
            ->with(['userRole', 'userRole.role'])
            ->find($claims['id'] ?? 0);
        if (empty($userInfo) or $userInfo->userRole->isEmpty()) {
            throw new BusinessException('用户或角色不存在');
        }

        $auth = new AuthService();

        $menu = $auth->getMenu($userInfo, (int)$appID);

        return $this->withData(0, '登录成功', [
            'menu' => $menu,
            'permissions' => []
        ]);
    }

}
