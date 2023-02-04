<?php

use app\common\middleware\AccessControlMiddleware;
use app\common\middleware\ControllerMiddleware;

return [
    '' => [
        //跨域中间件
        AccessControlMiddleware::class,

        //控制器中间件
        ControllerMiddleware::class
    ]
];
