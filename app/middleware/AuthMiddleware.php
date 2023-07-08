<?php

namespace app\middleware;

use app\exception\MsgException;
use app\service\AuthService;
use sword\http\middleware\ControllerMiddlewareInterface;
use sword\service\ResponseService;
use Webman\Http\Request;
use Webman\Http\Response;

/**
 * 鉴权中间件（控制器）
 */
class AuthMiddleware implements ControllerMiddlewareInterface
{

    /**
     * @param Request $request
     * @return ?Response
     */
    public function before(Request $request): ?Response
    {
        try{
            //接口鉴权
            $service = new AuthService();
            $service->checkAuth($request);
        }catch (MsgException $e){
            return ResponseService::jsonPack($e->getCode(), $e->getMessage());
        }

        return null;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return ?Response
     */
    public function after(Request $request, Response $response): ?Response
    {
        return null;
    }
}