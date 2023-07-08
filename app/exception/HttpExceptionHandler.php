<?php

namespace app\exception;

use support\exception\BusinessException;
use sword\service\HttpExceptionService;
use Throwable;
use Webman\Exception\ExceptionHandler;
use Webman\Http\Request;
use Webman\Http\Response;

class HttpExceptionHandler extends ExceptionHandler
{
    public $dontReport = [
        BusinessException::class,
    ];

    /**
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     * @throws Throwable
     */
    public function render(Request $request, Throwable $exception): Response
    {
        //处理异常响应
        return HttpExceptionService::render($request, $exception);
    }

}