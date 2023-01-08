<?php

namespace App\common\service;

use support\Response;

class ResponseService
{

    /**
     * 返回json数据的统一格式数据封装
     * @param int|string $code 错误代码，0为无错误
     * @param string $message 响应提示文本
     * @param array|object $data 响应数据主体
     * @param bool $isResponse 是否返回Response格式并添加header
     * @return array|Response
     */
    public static function jsonPack($code = 0, string $message = '', $data = [], bool $isResponse = true)
    {
        if(gettype($code) == 'string') {
            $codeData = CodeService::get($code);
            $code = $codeData['code'];
            $message or $message = $codeData['message'];
        }

        $ret = [
            'status' => $code === 0?1:0,
            'code'   => $code,
            'data'   => $data,
            'message'=> $message
        ];

        if($isResponse){
            return new Response(200, [
                'Content-Type' => 'application/json'
            ], \json_encode($ret, JSON_UNESCAPED_UNICODE));
        }else{
            return $ret;
        }
    }

}