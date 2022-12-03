<?php

namespace app\middleware;

use app\exception\MsgException;
use app\service\AuthService;
use app\service\ResponseService;

class AuthMiddleware
{
    public function handle($request, \Closure $next)
    {
        try{
            //接口鉴权
            $service = new AuthService();
            $service->checkAuth();
        }catch (MsgException $e){
            return ResponseService::jsonPack($e->getCode(), $e->getMessage());
        }

        return $next($request);
    }
}