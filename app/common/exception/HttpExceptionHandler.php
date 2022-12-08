<?php

namespace App\common\exception;

use app\common\service\CodeService;
use app\common\service\ResponseService;
use support\exception\BusinessException;
use Tinywan\Jwt\Exception\JwtTokenException;
use Webman\Exception\ExceptionHandler;
use Webman\Http\Request;
use Webman\Http\Response;
use Throwable;

class HttpExceptionHandler extends ExceptionHandler
{
    public $dontReport = [
        BusinessException::class,
    ];

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    public function render(Request $request, Throwable $exception): Response
    {
        $code = 0;
        $message = '';
        $data = [];

        //处理通用消息异常
        if($exception instanceof MsgException) {
            //错误提示消息
            $code = $exception->getCode();
            $message = $exception->getMessage();
            $data = $exception->getData();
        }elseif($exception instanceof JwtTokenException) {
            // Jwt验证异常
            $code = CodeService::JWT_AUTH_ERROR['code'];
            $message = CodeService::JWT_AUTH_ERROR['message'];
        }

        if($code != 0){
            if($request->isAjax()){
                return ResponseService::jsonPack($code, $message, $data);
            }else{
                return response($message);
            }
        }

        if(($exception instanceof BusinessException) && ($response = $exception->render($request)))
        {
            return $response;
        }

        return parent::render($request, $exception);
    }

}