<?php

use support\Request;

return [
    'debug' => env('APP_DEBUG', false),
    'error_reporting' => E_ALL,
    'default_timezone' => 'Asia/Shanghai',
    'request_class' => Request::class,
    'public_path' => base_path() . '/public',
    'runtime_path' => base_path(false) . '/runtime',
    'controller_suffix' => '',
    'controller_reuse' => true, //控制器复用

    //AppKey应用唯一标识,作为缓存前缀避免冲突
    'app_key' => 'rbac_app',

    //是否开启数据传输加密
    'data_encrypt' => false,
    //加密密钥（AES-128-ECB）
    'data_encrypt_key' => '123456782a4b4c5d',

    //响应跨域标识 (Access-Control-Allow-Origin)
    'response_cors' => [
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => '*',
        'Access-Control-Allow-Headers' => '*'
    ],

    //自定义登录服务类注册 -按顺序进行登录验证
    'login_service' => [
        //用户登录 -示例
//        \app\service\LoginService::class
    ]

];
