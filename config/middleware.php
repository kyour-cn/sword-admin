<?php

use app\middleware\AccessControlMiddleware;
use sword\http\middleware\ControllerMiddleware;

/**
 * 中间件配置
 */
return [
    '' => [
        //跨域中间件
        AccessControlMiddleware::class,

        //控制器中间件
        ControllerMiddleware::class
    ]
];