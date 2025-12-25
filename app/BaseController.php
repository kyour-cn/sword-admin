<?php declare (strict_types=1);

namespace app;

use support\Response;

/**
 * 控制器基础类
 */
abstract class BaseController
{

    /**
     * api接口返回数据
     * @param int $code 错误代码，0为无错误
     * @param string $message 响应提示文本
     * @param mixed|array $data 响应数据主体
     * @return Response
     */
    protected function withData(int $code = 0, string $message = '', mixed $data = []): Response
    {
        $ret = [
            'code'   => $code,
            'data'   => $data,
            'message'=> $message
        ];
        return new Response(200, [
            'Content-Type' => 'application/json'
        ], json_encode($ret, JSON_UNESCAPED_UNICODE));
    }

}
