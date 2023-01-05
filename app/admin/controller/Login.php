<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\exception\MsgException;
use app\common\service\BaseLoginService;
use App\common\service\CaptchaService;
use app\common\service\LoginService;
use app\common\service\LogService;
use App\common\validate\LoginValidate;
use support\Request;
use Tinywan\Jwt\JwtToken;

class Login extends BaseController
{
    public function index(Request $request)
    {
        $params = $request->all();

        //登录验证器
        $validate = new LoginValidate();
        if (!$validate->check($params)) {
            throw new MsgException($validate->getError());
        }

        //密码转换
        if(empty($params['md5'])){
            $params['password'] = md5($params['password']);
        }

        //从配置中获取注册的登录服务
        $serviceList = config('app.login_service', [
            //用户登录 -系统默认
            LoginService::class
        ]);

        foreach ($serviceList as $serviceClass) {
            /**
             * 遍历并实例化登录服务
             * @var $service BaseLoginService
             */
            $service = new $serviceClass();
            if($service->login($params)){
                break; //跳出循环
            }
        }

        if(!isset($service) or !$service->success){
            return $this->withData(1, '账号或密码不正确');
        }

        //开始创建token
        $token = JwtToken::generateToken($service->jwtData);

        //记录登录日志
        LogService::app('login', [
            'title' => '用户登录',
            'request_user' => $service->userInfo['realname']??'',
            'request_user_id' => $service->userInfo['id']??0,
        ]);

        return $this->withData(0, 'success', [
            'token' => $token['access_token'],
            'userInfo' => $service->userInfo
        ]);
    }

    /**
     * 输出验证码图像
     */
    public function captcha(Request $request)
    {
        $id = $request->get('number');
        $service = new CaptchaService();
        return $service->captcha($id);
    }

}
