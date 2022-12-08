<?php

namespace App\common\middleware;

use Webman\Http\Request;
use Webman\Http\Response;

interface ControllerMiddlewareInterface
{

    /**
     * 控制器专用中间件接口
     */
    public function process(Request $request): ?Response;

}