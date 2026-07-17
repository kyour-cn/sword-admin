<?php declare(strict_types=1);

namespace app\common\utils;

use app\common\constants\AuditLogDict;
use app\model\AuditLog as AuditLogModel;
use support\Log;
use Throwable;
use Webman\Http\Request;

/**
 * 操作审计日志工具
 */
class AuditLog
{
    private const SENSITIVE_KEYS = [
        'password',
        'new_password',
        'old_password',
        'token',
        'access_token',
        'refresh_token',
        'authorization',
        'secret',
        'secret_key',
        'access_key',
        'session_token',
    ];

    private const MASK = '******';

    /**
     * 记录成功操作
     * @param string $action 操作动作
     * @param string $module 业务模块
     * @param string $title 审计标题
     * @param array $data 附加审计数据
     * @return AuditLogModel|null
     */
    public static function success(string $action, string $module, string $title, array $data = []): ?AuditLogModel
    {
        return self::record(array_merge($data, [
            'action' => $action,
            'module' => $module,
            'title' => $title,
            'status' => 1,
        ]));
    }

    /**
     * 记录失败操作
     * @param string $action 操作动作
     * @param string $module 业务模块
     * @param string $title 审计标题
     * @param array $data 附加审计数据
     * @return AuditLogModel|null
     */
    public static function fail(string $action, string $module, string $title, array $data = []): ?AuditLogModel
    {
        return self::record(array_merge($data, [
            'action' => $action,
            'module' => $module,
            'title' => $title,
            'status' => 0,
        ]));
    }

    /**
     * 记录审计日志
     * @param array $data 审计数据
     * @return AuditLogModel|null
     */
    public static function record(array $data): ?AuditLogModel
    {
        try {
            $request = $data['request'] ?? request();
            unset($data['request']);

            $data = array_merge(self::requestData($request), $data);
            $data = self::fillActor($data, $request);
            $data['context'] = self::sanitizeContext($data['context'] ?? null);
            $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');

            $log = new AuditLogModel();
            $log->fill(self::normalize($data));
            $log->save();

            return $log;
        } catch (Throwable $e) {
            Log::error('操作审计日志写入失败：' . $e->getMessage(), [
                'exception' => $e,
                'data' => self::sanitizeContext($data ?? []),
            ]);
            return null;
        }
    }

    private static function requestData(?Request $request): array
    {
        if (!$request) {
            return [
                'method' => '',
                'path' => '',
                'ip' => '',
                'user_agent' => '',
            ];
        }

        return [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->getRealIp() ?: '',
            'user_agent' => $request->header('user-agent', ''),
        ];
    }

    private static function fillActor(array $data, ?Request $request): array
    {
        if (!$request || !empty($data['actor_id'])) {
            return $data;
        }

        try {
            $claims = JwtUtils::decodeFromRequest($request);
            $data['actor_id'] = $claims['id'] ?? 0;
            $data['actor_name'] = $claims['name'] ?? '';
        } catch (Throwable) {
        }

        return $data;
    }

    private static function normalize(array $data): array
    {
        $module = (string)($data['module'] ?? '');

        return [
            'app_id' => (int)($data['app_id'] ?? 0),
            'actor_id' => (int)($data['actor_id'] ?? 0),
            'actor_name' => self::limit((string)($data['actor_name'] ?? ''), 64),
            'action' => self::limit((string)($data['action'] ?? ''), 64),
            'module' => self::limit($module, 64),
            'module_title' => self::limit((string)($data['module_title'] ?? AuditLogDict::FALLBACK_MODULES[$module] ?? $module), 64),
            'resource_type' => self::limit((string)($data['resource_type'] ?? ''), 64),
            'resource_id' => self::limit((string)($data['resource_id'] ?? ''), 64),
            'title' => self::limit((string)($data['title'] ?? ''), 255),
            'description' => self::limit((string)($data['description'] ?? ''), 500),
            'method' => self::limit((string)($data['method'] ?? ''), 10),
            'path' => self::limit((string)($data['path'] ?? ''), 255),
            'ip' => self::limit((string)($data['ip'] ?? ''), 64),
            'user_agent' => self::limit((string)($data['user_agent'] ?? ''), 500),
            'status' => empty($data['status']) ? 0 : 1,
            'context' => $data['context'] ?? null,
            'created_at' => $data['created_at'] ?? date('Y-m-d H:i:s'),
        ];
    }

    private static function sanitizeContext(mixed $context): mixed
    {
        if (!is_array($context)) {
            return $context;
        }

        $result = [];
        foreach ($context as $key => $value) {
            $normalizedKey = strtolower((string)$key);
            $result[$key] = in_array($normalizedKey, self::SENSITIVE_KEYS, true)
                ? self::MASK
                : self::sanitizeContext($value);
        }

        return $result;
    }

    private static function limit(string $value, int $limit): string
    {
        return strlen($value) > $limit ? substr($value, 0, $limit) : $value;
    }
}
