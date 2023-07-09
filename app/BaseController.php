<?php declare (strict_types=1);

namespace app;

use app\enum\ResponseCode;
use support\Response;
use sword\service\ResponseService;

/**
 * 控制器基础类
 */
abstract class BaseController
{

    /**
     * api接口返回数据，封装统一规则
     * @param int|ResponseCode $code 错误代码，0为无错误
     * @param string $message 响应提示文本
     * @param mixed|array $data 响应数据主体
     * @return Response
     */
    protected function withData(int|ResponseCode $code = 0, string $message = '', mixed $data = []): Response
    {
        $message === '' and $code === 0 and $message = 'success';
        return ResponseService::jsonPack($code, $message, $data);
    }

}
