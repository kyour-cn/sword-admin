<?php

use support\Request;
use support\Response;
use sword\service\ResponseService;
use Webman\Route;

//404响应路由
Route::fallback(function(Request $request){
    // ajax请求时返回json
    if ($request->expectsJson()) {
        return ResponseService::jsonPack(404, '404 not found');
    }
    // 页面请求返回404
    return new Response(404, [], '404 not found');
});
