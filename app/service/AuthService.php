<?php

namespace app\service;

use app\enum\ResponseCode;
use app\exception\MsgException;
use app\model\RoleModel;
use app\model\RuleModel;
use app\model\UserModel;
use sword\Cache\Facade\Cache;
use sword\service\UtilsService;
use Tinywan\Jwt\Exception\JwtTokenException;
use Tinywan\Jwt\JwtToken;
use Webman\Http\Request;

class AuthService
{

    /**
     * 检查当前访问权限
     * @throws MsgException
     * @throws JwtTokenException
     */
    public function checkAuth(Request $request): bool
    {
        //获取登录信息
        $roleId = JwtToken::getExtendVal('role');
        $uid = JwtToken::getExtendVal('id');

        //验证是否超级管理员
        if($this->isRootUser($uid)) return true;

        //当前请求的Path
        $path = UtilsService::getRequestPath($request);

        if(!$this->checkRulePath($roleId, $path)){
            //没有权限
            throw ResponseCode::makeException(ResponseCode::NoPermission);
        }

        return true;
    }

    /**
     * 检查是否登录
     * @return bool
     */
    public function checkLogin(): bool
    {
        if(!request()) return false;
        try{
            JwtToken::getCurrentId();
        }catch (JwtTokenException){
            return false;
        }
        return true;
    }

    /**
     * 根据路由匹配权限
     * @param int $roleId
     * @param $path
     * @return bool
     */
    public function checkRulePath(int $roleId, $path): bool
    {
        //取出rule信息
        $ruleId = RuleModel::where('path', $path)
            ->where('status', 1)
            ->value('id');
        if(!$ruleId) return false;

        $check = RoleModel::where('id', $roleId)
            ->where("FIND_IN_SET('$ruleId',rules)")
            ->value('id');

        return (bool) $check;
    }

    /**
     * 根据别名匹配权限
     * @param int $roleId
     * @param $alias
     * @return bool
     */
    public function checkRuleAlias(int $roleId, $alias): bool
    {
        //取出rule信息
        $ruleId = RuleModel::where('alias', $alias)
            ->where('status', 1)
            ->value('id');
        if(!$ruleId) return false;

        $check = RoleModel::where('id', $roleId)
            ->where("FIND_IN_SET('$ruleId',rules)")
            ->value('id');

        return (bool) $check;
    }

    /**
     * 判断用户是否为超级管理员
     * @param int $uid
     * @return bool
     */
    public function isRootUser(int $uid): bool
    {
        return Cache::remember("is_root_user_$uid", function () use ($uid) {
            return UserModel::where('id', $uid)->value('role_id') == 1;
        }, 600);
    }

}