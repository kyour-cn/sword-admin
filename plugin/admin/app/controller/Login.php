<?php
namespace plugin\admin\app\controller;

use app\BaseController;
use app\common\exception\MsgException;
use app\common\service\BaseLoginService;
use app\common\service\CaptchaService;
use app\common\service\LogService;
use Psr\SimpleCache\InvalidArgumentException;
use support\Request;
use Tinywan\Jwt\JwtToken;

class Login extends BaseController
{
    public function index(Request $request)
    {
        $params = $request->all();

        $loginService = new BaseLoginService();
        $res = $loginService->checkLogin($params);

        if(count($res) == 1){

            //取出成功的登录服务
            $service = $res[0];

            //开始创建token
            $token = JwtToken::generateToken($service->jwtData);

            //记录登录日志
            LogService::app('login', [
                'title' => '用户登录',
                'request_user' => $service->userInfo['realname']??'',
                'request_user_id' => $service->userInfo['id']??0,
                'value' => json_encode($service->jwtData, JSON_UNESCAPED_UNICODE)
            ]);

            return $this->withData(0, 'success', [
                'token' => $token['access_token'],
                'userInfo' => $service->userInfo
            ]);
        }else{
            //TODO: 存在多个身份，需做选择身份功能
            throw (new MsgException("存在多身份，开发中..."))->setData($res);
        }

    }

    /**
     * 输出验证码图像
     * @throws InvalidArgumentException
     */
    public function captcha(Request $request): \support\Response
    {
        $id = $request->get('number');
        $service = new CaptchaService();
        return $service->captcha($id);
    }
}
