<?php

namespace app\common\middleware;

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
        $controllerClass = $request->controller;

        try{
            $middlewareConfig = $controllerClass::middleware;
        }catch (\Throwable $e){
            //没有定义控制器中间件
            $middlewareConfig = [];
        }
        if($middlewareConfig){
            //如果存在中间件，则调用控制器中间件
            foreach ($middlewareConfig as $middlewareClass) {
                /**
                 * @var ControllerMiddlewareInterface $middleware
                 */
                $middleware = new $middlewareClass();

                //调用中间件，若有响应则中断请求
                if($res = $middleware->process($request)){
                    return $res;
                }
            }
        }

        return $handler($request);
    }
}