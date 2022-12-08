<?php

namespace App\common\middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * 跨域请求中间件
 */
class AccessControlMiddleware implements MiddlewareInterface
{

    /**
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler) : Response
    {
        // 如果是opitons请求则返回一个空的响应，否则继续向洋葱芯穿越，并得到一个响应
        $response = $request->method() == 'OPTIONS' ? response('') : $handler($request);

        $headers = config('app.response_cors', [
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => '*',
            'Access-Control-Allow-Headers' => '*'
        ]);

        // 给响应添加跨域相关的http头
        $response->withHeaders($headers);

        return $response;
    }
}