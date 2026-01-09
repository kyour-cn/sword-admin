<?php

namespace app\common\exception;

use Throwable;
use Webman\Exception\ExceptionHandler;
use Webman\Http\Request;
use Webman\Http\Response;

/**
 * Class BusinessHandler
 * @package app\common\exception
 */
class BusinessHandler extends ExceptionHandler
{
    public $dontReport = [
        BusinessException::class,
    ];

    /**
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     */
    public function render(Request $request, Throwable $exception): Response
    {
        if (method_exists($exception, 'render') && ($response = $exception->render($request))) {
            return $response;
        }
        $code = $exception->getCode();
        if ($request->expectsJson()) {
            $json = ['code' => $code ?: 500, 'message' => $this->debug ? $exception->getMessage() : 'Server internal error'];
            $this->debug && $json['traces'] = (string)$exception;
            return new Response(200, ['Content-Type' => 'application/json'],
                json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        $error = $this->debug ? nl2br((string)$exception) : 'Server internal error';
        return new Response(500, [], $error);
    }

}