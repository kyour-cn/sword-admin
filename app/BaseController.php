<?php declare (strict_types = 1);

namespace App;

use App\common\middleware\ControllerMiddlewareInterface;
use App\common\service\ResponseService;
use support\Response;

/**
 * 控制器基础类
 */
abstract class BaseController
{

    /**
     * @var ControllerMiddlewareInterface[]
     */
    const middleware = [];

    /**
     * api接口返回数据，封装统一规则
     * @param int|string $code 错误代码，0为无错误
     * @param string $message 响应提示文本
     * @param array|object $data 响应数据主体
     * @return array|Response
     */
    protected function withData($code = 0, string $message = '', $data = [])
    {
        return ResponseService::jsonPack($code, $message, $data);
    }

}
