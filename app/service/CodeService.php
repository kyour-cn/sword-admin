<?php

namespace app\service;

use app\exception\MsgException;

class CodeService
{
    //Jwt验证失败 -未登录
    const JWT_AUTH_ERROR = [
        'code' => 510,
        'message' => 'Token验证失败'
    ];

    //权限验证失败 -无接口权限
    const API_AUTH_ERROR = [
        'code' => 511,
        'message' => '无接口权限'
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

    /**
     * 将错误代码创建为 MsgException
     * @param $name
     * @return MsgException
     */
    public static function makeException($name): MsgException
    {
        $codeData = self::get($name);
        return new MsgException($codeData['message'], $codeData['code']);
    }

}