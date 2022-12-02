<?php
namespace app;

use app\exception\MsgException;
use app\service\CodeService;
use thans\jwt\exception\JWTException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,

        JWTException::class,
        MsgException::class
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        $code = 0;
        $message = '';

        if($e instanceof JWTException) {
            // Jwt验证异常
            $code = CodeService::JWT_AUTH_ERROR['code'];
            $message = CodeService::JWT_AUTH_ERROR['message'];
        }elseif($e instanceof MsgException) {
            //错误提示消息
            $code = $e->getCode();
            $message = $e->getMessage();
        }

        if($code != 0){
            if($request->isAjax()){
                return response($message);
            }else{
                return $this->withData($code, $message);
            }
        }

        // 其他错误交给系统处理
        return parent::render($request, $e);
    }

    /**
     * api接口返回数据，封装统一规则
     * @param int|string $code 错误代码，0为无错误
     * @param string $message 响应提示文本
     * @param array|object $data 响应数据主体
     * @return null
     */
    protected function withData($code = 0, string $message = '', $data = [])
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

        header('Content-Type: application/json;charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        return json($ret);
    }
}
