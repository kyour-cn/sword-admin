<?php
declare (strict_types = 1);

namespace app;

use app\service\CodeService;
use app\service\ResponseService;
use think\App;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {}

    /**
     * api接口返回数据，封装统一规则
     * @param int|string $code 错误代码，0为无错误
     * @param string $message 响应提示文本
     * @param array|object $data 响应数据主体
     * @return null
     */
    protected function withData($code = 0, string $message = '', $data = [])
    {
        return ResponseService::jsonPack($code, $message, $data);
    }

}
