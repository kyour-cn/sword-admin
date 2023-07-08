<?php
namespace plugin\admin\app\controller;

use app\BaseController;
use app\service\AuthService;
use plugin\admin\app\service\AdminRbacService;
use support\Response;
use think\db\exception\DbException;
use Tinywan\Jwt\JwtToken;

class Index extends BaseController
{

    public function index(): string
    {
        return "hello admin.";
    }

    /**
     * 获取用户菜单
     * @return Response
     * @throws DbException
     */
    public function menu(): Response
    {
        //获取当前用户角色
        $roleId = JwtToken::getExtendVal('role');
        $uid = JwtToken::getExtendVal('id');

        $rbacService = new AdminRbacService();
        $authService = new AuthService();
        if($authService->isRootUser($uid)){
            $menu = $rbacService->getRootMenu(1);
        }else{
            $menu = $rbacService->getUserMenu($roleId);
        }

        return $this->withData(0, 'success', [
            'menu' => $menu
        ]);
    }

}
