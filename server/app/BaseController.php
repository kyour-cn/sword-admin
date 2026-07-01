<?php declare (strict_types=1);

namespace app;

use app\common\utils\ResponseUtils;
use support\Response;

/**
 * 控制器基础类
 */
abstract class BaseController
{

    /**
     * 成功响应
     * @param string $message 响应提示文本
     * @param mixed|array $data 响应数据主体
     * @return Response
     */
    protected function success(string $message = '', mixed $data = []): Response
    {
        return $this->withData(0, $message, $data);
    }

    /**
     * 失败响应
     * @param int $code 错误代码，1为默认错误
     * @param string $message 响应提示文本
     * @param mixed|array $data 响应数据主体
     * @return Response
     */
    protected function fail(int $code = 1, string $message = '', mixed $data = []): Response
    {
        return $this->withData($code, $message, $data);
    }

    /**
     * api接口返回数据
     * @param int $code 错误代码，0为无错误
     * @param string $message 响应提示文本
     * @param mixed|array $data 响应数据主体
     * @return Response
     */
    protected function withData(int $code = 0, string $message = '', mixed $data = []): Response
    {
        $res = [
            'code'   => $code,
            'message'=> $message,
            'data'   => $data
        ];
        return ResponseUtils::json($res);
    }

}
