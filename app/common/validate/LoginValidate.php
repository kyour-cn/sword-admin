<?php

namespace app\common\validate;

use app\common\service\CaptchaService;

class LoginValidate extends BaseValidate
{
    protected $rule = [
        'username|用户名'  => 'require|min:3|max:32',
        'password|密码'   => 'require|min:6|max:32',
        'code|图形验证码' => 'require|checkCode',
    ];

    /**
     * 验证图形码
     * @param $value
     * @param $rule
     * @param array $data
     * @return bool|string
     */
    protected function checkCode($value, $rule, array $data = [])
    {
        if(empty($data['number'])) return "验证码编号必传";
        $service = new CaptchaService();
        if(!$service->check($data['number'], $data['code'])){
            return '验证码错误';
        }
        return true;
    }
}