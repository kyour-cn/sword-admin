<?php

namespace app\middleware;

use thans\jwt\facade\JWTAuth;

class AuthMiddleware
{
    public function handle($request, \Closure $next)
    {
        //获取登录信息
        $payload = JWTAuth::auth();

//        var_dump($payload);

        return $next($request);
    }
}