<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\service\BaseLoginService;
use App\common\service\CaptchaService;
use app\common\service\LoginService;
use Tinywan\Jwt\JwtToken;
use support\Request;

class Login extends BaseController
{
    public function index(Request $request)
    {
        $params = $request->all();

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
        $uid = JwtToken::getExtendVal('id');

        $service = new CaptchaService();

        return $service->captcha($uid);
    }

    /**
     * 检查验证码
     */
    public function check(Request $request)
    {
        $input = $request->all();
        $uid = JwtToken::getExtendVal('id');

        $service = new CaptchaService();

        if($service->check($uid, $input['code']??'')){
            return $this->withData(0, 'success');
        }else{
            return $this->withData(0, 'error');
        }
    }

}
