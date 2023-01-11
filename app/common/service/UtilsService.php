<?php

namespace App\common\service;

use support\Redis;
use Webman\Http\Request;

class UtilsService
{

    /**
     * 时间锁
     * @param string $key
     * @param int $expire 自动解锁时间 秒
     * @return bool
     */
    public static function setLock(string $key, int $expire = 1): bool
    {
        $key = config('app.app_key'). '_lock:'.$key;

        if($expire == null){
            Redis::del($key);
            return true;
        }

        $res = Redis::setNx($key, time());
        if($res){
            Redis::expire($key, $expire);
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param string $key
     * @return string|bool
     */
    public static function getLock(string $key)
    {
        $key = config('app.app_key'). '_lock:'.$key;
        return Redis::get($key);
    }

    /**
     * 获取请求路由地址
     * @param Request $request
     * @return string
     */
    public static function getRequestPath(Request $request): string
    {
        $appName = $request->app;
        $path = $request->path();
        //判断该请求是否为插件
        if(substr($path, 0, 5) == "/app/"){
            $appName = "app/". explode("/", $path)[2];
        }
        $controller = explode('\\',$request->controller);
        $controller = $controller[count($controller) -1];
        $action = $request->action;

        //当前请求的Path
        return "$appName/$controller/$action";
    }

    /**
     * 获取调用方法的来源
     * @param int $index
     * @return string
     */
    public static function getCallFunc(int $index = 1): string
    {
        $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $index +1);
        $trace = $traces[$index]??null;
        return $trace ? "{$trace['class']}::{$trace['function']}" : '';
    }

    /**
     * 获取请求来源信息
     * @param Request $request
     * @return string
     */
    public static function getRequestSource(Request $request): string
    {
        return $request->method(). ':'. self::getRequestPath($request);
    }

}