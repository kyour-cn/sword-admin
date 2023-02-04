<?php

namespace app\common\exception;

use app\common\service\LogService;
use app\common\service\ResponseService;
use support\exception\BusinessException;
use Throwable;
use Tinywan\Jwt\Exception\JwtTokenException;
use Tinywan\Jwt\Exception\JwtTokenExpiredException;
use Webman\Exception\ExceptionHandler;
use Webman\Http\Request;
use Webman\Http\Response;

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
            $code = HttpCode::JWT_EXPIRED_ERROR['code'];
            $message = HttpCode::JWT_EXPIRED_ERROR['message'];
        }elseif($exception instanceof JwtTokenException) {
            // Jwt验证异常
            $code = HttpCode::JWT_AUTH_ERROR['code'];
            $message = HttpCode::JWT_AUTH_ERROR['message'];
        }else{
            //错误提示消息
            if(env('APP_DEBUG')){
                $code = $exception->getCode()?:1; //不能为0 因为jsonPack会响应success
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

            //向数据库插入错误日志
            LogService::error([
                'title' => mb_substr('Http异常: '. $exception->getMessage(), 0, 255),
                'value' => $exception->getMessage(). "\n". $exception->getTraceAsString()
            ]);
        }

        if($request->isAjax()){
            return ResponseService::jsonPack($code, $message, $data??[]);
        }else{
            return response($message);
        }

//        return parent::render($request, $exception);
    }

}