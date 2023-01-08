<?php

namespace App\common\middleware;

use App\common\exception\MsgException;
use App\common\service\AuthService;
use App\common\service\ResponseService;
use Tinywan\Jwt\Exception\JwtTokenException;
use Webman\Http\Request;
use Webman\Http\Response;

/**
 * 鉴权中间件
 */
class AuthMiddleware implements ControllerMiddlewareInterface
{

    /**
     * @param Request $request
     * @return Response
     */
    public function process(Request $request) : ?Response
    {
        try{
            //接口鉴权
            $service = new AuthService();
            $service->checkAuth($request);
        }catch (MsgException $e){
            return ResponseService::jsonPack($e->getCode(), $e->getMessage());
        }catch (JwtTokenException $e){
            return ResponseService::jsonPack("JWT_AUTH_ERROR");
        }

        return null;
    }
}