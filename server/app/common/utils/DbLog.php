<?php declare(strict_types=1);

namespace app\common\utils;

use app\common\exception\BusinessException;
use app\model\LogType;
use app\model\Log;
use Throwable;
use Webman\Http\Request;

/**
 * 数据库日志工具
 * @method static void debug(string|array $title, mixed $value = [], string $valType = 'text')
 * @method static void info(string|array $title, mixed $value = [], string $valType = 'text')
 * @method static void warning(string|array $title, mixed $value = [], string $valType = 'text')
 * @method static void error(string|array $title, mixed $value = [], string $valType = 'text')
 */
class DbLog
{

    /**
     * 系统日志类型
     */
    const array SYS_LEVEL = [
        'debug' => 1,
        'info' => 2,
        'warn' => 3,
        'error' => 4,
    ];

    /**
     * @param $method
     * @param $args
     * @return Log
     */
    public static function __callStatic($method, $args): Log
    {
        if (isset(self::SYS_LEVEL[$method])) {
            $typeId = self::SYS_LEVEL[$method];
            $typeName = $method;
        } else {
            //查询应用日志级别
            $level = LogType::where('label', $method)->first();
            if (!$level) {
                throw new BusinessException("Log::$method() is not a valid method");
            }
            $typeId = $level->id;
            $typeName = $level->label;
        }

        $title = $args[0];
        $logValue = $args[1] ?? '';
        $valueType = $args[2] ?? 'text';

        $data = [
            'type_id' => $typeId,
            'type_name' => $typeName,
            'title' => $title,
            'value' => $logValue,
            'value_type' => $valueType
        ];
        if (is_array($args[0])) {
            $data = array_merge($data, $args[0]);
        }

        return self::writeLog($data);
    }

    /**
     * 记录其他日志
     * @param string $level
     * @param string|array $data
     * @param mixed $value
     * @param string $valType
     * @return Log
     */
    public static function log(string $level, string|array $data, mixed $value = [], string $valType = 'text'): Log
    {
        return self::$level($data, $value, $valType);
    }

    /**
     * 写入日志
     * @param array $data
     * @return Log
     */
    private static function writeLog(array $data): Log
    {
        $request = $data['request'] ?? request();
        //自动填充请求来源信息
        if (empty($data['request_source'])) {
            if ($request) {
                $data['request_source'] = '['.$request->method() . ']' . self::getRequestPath($request);
            } else {
                $traces = self::getCallFunc(3);
                $data['request_source'] = $traces;
            }
        }

        //填充IP地址
        if ($request and $ip = $request->getRealIp()) {
            $data['request_ip'] = $ip;
        }
        //填充用户数据
        if ($request and empty($data['request_user_id'])) {
            try {
                $claims = JwtUtils::decodeFromRequest($request);
                $data['request_user_id'] = $claims['id'] ?? 0;
                $data['request_user'] = $claims['name'] ?? '';
            } catch (Throwable) {
            }
        }

        $log = new Log();
        $log->fill($data);
        if (!$log->save()) {
            //日志保存到数据库失败
            throw new BusinessException('日志保存到数据库失败');
        }
        return $log;
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
        if (str_starts_with($path, "/app/")) {
            $appName = "app/" . explode("/", $path)[2];
        }
        $controller = explode('\\', $request->controller);
        $controller = lcfirst($controller[count($controller) - 1]);
        $action = lcfirst($request->action);

        //当前请求的Path
        return "/$appName/$controller/$action";
    }

    /**
     * 获取调用方法的来源
     * @param int $index
     * @return string
     */
    public static function getCallFunc(int $index = 1): string
    {
        $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $index + 1);
        $trace = $traces[$index] ?? null;
        return $trace ? "{$trace['class']}::{$trace['function']}" : "";
    }
}