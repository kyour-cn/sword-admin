<?php

namespace App\common\middleware;

use app\BaseController;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class ControllerMiddleware implements MiddlewareInterface
{

    /**
     * 实现控制器中间件处理
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler) : Response
    {
        $className = $request->controller;

        /**
         * @var BaseController $controller
         */
        $controller = new $className();

        if($controller->middleware){
            //如果存在中间件，则调用控制器中间件
            foreach ($controller->middleware as $middlewareClass) {
                /**
                 * @var ControllerMiddlewareInterface $middleware
                 */
                $middleware = new $middlewareClass();

                //调用中间件，若有响应则终端请求
                if($res = $middleware->process($request)){
                    return $res;
                }
            }

        }

        return $handler($request);
    }
}