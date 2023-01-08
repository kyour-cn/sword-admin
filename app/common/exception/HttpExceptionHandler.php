<?php

namespace App\common\exception;

use App\common\service\CodeService;
use App\common\service\ResponseService;
use support\exception\BusinessException;
use Tinywan\Jwt\Exception\JwtTokenException;
use Tinywan\Jwt\Exception\JwtTokenExpiredException;
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
        if(($exception instanceof BusinessException) && ($response = $exception->render($request))) {
            return $response;
        }

        //处理通用消息异常
        if($exception instanceof MsgException) {
            //错误提示消息
            $code = $exception->getCode();
            $message = $exception->getMessage();
            $data = $exception->getData();
        }elseif($exception instanceof JwtTokenExpiredException) {
            // Jwt验证异常
            $code = CodeService::JWT_EXPIRED_ERROR['code'];
            $message = CodeService::JWT_EXPIRED_ERROR['message'];
        }elseif($exception instanceof JwtTokenException) {
            // Jwt验证异常
            $code = CodeService::JWT_AUTH_ERROR['code'];
            $message = CodeService::JWT_AUTH_ERROR['message'];
        }else{
            //错误提示消息
            if(env('APP_DEBUG')){
                $code = $exception->getCode();
                $message = $exception->getMessage();
                if($request->isAjax()){
                    $data = [
                        'message' => $message,
                        'trace' => $exception->getTraceAsString()
                    ];
                }else{
                    $message .= "\n". $exception->getTraceAsString();
                }
            }else{
                $code = 500;
                $message = '系统发生错误';
            }
        }

        if($request->isAjax()){
            return ResponseService::jsonPack($code, $message, $data??[]);
        }else{
            return response($message);
        }

//        return parent::render($request, $exception);
    }

}