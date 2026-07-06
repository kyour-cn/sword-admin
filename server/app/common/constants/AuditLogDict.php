<?php declare(strict_types=1);

namespace app\common\constants;

/**
 * 操作审计字典
 */
class AuditLogDict
{
    public const ACTIONS = [
        'login' => '登录',
        'create' => '新增',
        'update' => '编辑',
        'delete' => '删除',
        'export' => '导出',
        'import' => '导入',
        'upload' => '上传',
    ];

    public const STANDARD_ACTIONS = [
        'add' => 'create',
        'create' => 'create',
        'edit' => 'update',
        'update' => 'update',
        'save' => 'update',
        'delete' => 'delete',
        'menuAdd' => 'create',
        'menuDelete' => 'delete',
        'resetPassword' => 'update',
        'upload' => 'upload',
        'export' => 'export',
        'import' => 'import',
    ];

    public const FALLBACK_MODULES = [
        'auth' => '认证',
        'admin' => '后台',
    ];

    public const STATUS = [
        1 => '成功',
        0 => '失败',
    ];

    public static function actionLabel(string $action): string
    {
        return self::ACTIONS[$action] ?? $action;
    }

    public static function standardAction(string $action): ?string
    {
        return self::STANDARD_ACTIONS[$action] ?? null;
    }
}
