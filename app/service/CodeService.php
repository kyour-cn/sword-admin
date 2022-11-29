<?php

namespace app\service;

class CodeService
{
    //Jwt验证失败 -未登录
    const JWT_AUTH_ERROR = [
        'code' => 510,
        'message' => 'Token验证失败'
    ];

    /**
     * 动态获取状态码数据
     * @param $name
     * @return mixed
     */
    public static function get($name)
    {
        return constant(self::class.'::'.$name);
    }

}