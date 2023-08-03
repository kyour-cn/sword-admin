<?php
namespace plugin\admin\app\controller;

use app\BaseController;
use app\exception\MsgException;
use app\service\BaseLoginService;
use plugin\admin\app\validate\LoginValidate;
use Psr\SimpleCache\InvalidArgumentException;
use support\Request;
use support\Response;
use sword\log\Log;
use sword\service\CaptchaService;
use think\db\exception\DbException;
use Tinywan\Jwt\JwtToken;

/**
 * 后台登录控制器
 * @api
 */
class Login extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     * @throws MsgException|DbException
     */
    public function index(Request $request): Response
    {
        $params = $request->all();

        //登录验证器
        $validate = new LoginValidate();
        if (!$validate->check($params)) {
            throw new MsgException($validate->getError());
        }

        $loginService = new BaseLoginService();
        $res = $loginService->checkLogin($params);

        if(count($res) == 1){
            //取出成功的登录服务
            $service = $res[0];

            //开始创建token
            $token = JwtToken::generateToken($service->jwtData);

            //记录登录日志
            Log::log('login', [
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
    public function captcha(Request $request): Response
    {
        $id = $request->get('number');
        $service = new CaptchaService();
        return $service->imgCaptcha($id);
    }
}
