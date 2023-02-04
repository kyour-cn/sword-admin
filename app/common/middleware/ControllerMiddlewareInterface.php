<?php

namespace app\common\middleware;

use Webman\Http\Request;
use Webman\Http\Response;

/**
 * 控制器专用中间件接口
 */
interface ControllerMiddlewareInterface
{

    /**
     * @param Request $request
     * @return Response|null
     */
    public function process(Request $request): ?Response;

}