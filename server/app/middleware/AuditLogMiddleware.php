<?php declare(strict_types=1);

namespace app\middleware;

use app\common\constants\AuditLogDict;
use app\common\utils\AuditLog;
use app\model\Menu;
use app\model\MenuApi;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * 操作审计中间件
 */
class AuditLogMiddleware implements MiddlewareInterface
{
    private const SKIP_PREFIXES = [
        '/admin/system/auditLog',
        '/admin/auth/',
        '/admin/site/',
    ];

    /**
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        $response = $handler($request);

        if ($this->shouldRecord($request)) {
            $this->record($request, $response);
        }

        return $response;
    }

    private function shouldRecord(Request $request): bool
    {
        $path = $request->path();
        foreach (self::SKIP_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return false;
            }
        }

        return AuditLogDict::standardAction($this->actionName($request)) !== null;
    }

    private function record(Request $request, Response $response): void
    {
        $actionName = $this->actionName($request);
        $action = AuditLogDict::standardAction($actionName);
        $module = $this->moduleName($request);
        [$ruleMenu, $moduleMenu] = $this->menusFromApi($request);
        $moduleLabel = $this->moduleLabel($moduleMenu, $module);
        $actionLabel = AuditLogDict::actionLabel((string)$action);
        $title = $this->operationTitle($ruleMenu, $actionLabel, $moduleLabel);
        $isSuccess = $this->isSuccess($response);
        $claims = $request->jwt ?? [];

        AuditLog::record([
            'actor_id' => $claims['id'] ?? 0,
            'actor_name' => $claims['name'] ?? '',
            'action' => $action,
            'module' => $module,
            'resource_type' => $module,
            'resource_id' => $this->resourceId($request),
            'title' => $title,
            'description' => $title . ($isSuccess ? '成功' : '失败'),
            'status' => $isSuccess ? 1 : 0,
            'context' => $this->context($request),
            'request' => $request,
        ]);
    }

    private function isSuccess(Response $response): bool
    {
        if ($response->getStatusCode() >= 400) {
            return false;
        }

        $body = json_decode($response->rawBody(), true);
        if (is_array($body) && isset($body['code'])) {
            return (int)$body['code'] === 0;
        }

        return true;
    }

    private function moduleName(Request $request): string
    {
        $path = trim($request->path(), '/');
        $parts = explode('/', $path);

        return $parts[2] ?? 'admin';
    }

    private function moduleLabel(?Menu $menu, string $module): string
    {
        return $menu?->title ?? AuditLogDict::FALLBACK_MODULES[$module] ?? $module;
    }

    /**
     * 根据接口权限配置拆分出当前规则菜单和所属模块菜单。
     * @return array{0: Menu|null, 1: Menu|null}
     */
    private function menusFromApi(Request $request): array
    {
        $ruleMenu = $this->ruleMenuFromApi($request);
        $moduleMenu = $ruleMenu;

        while ($moduleMenu && $moduleMenu->type !== 'menu') {
            $moduleMenu = $moduleMenu->pid > 0 ? Menu::find($moduleMenu->pid) : null;
        }

        return [$ruleMenu, $moduleMenu];
    }

    private function ruleMenuFromApi(Request $request): ?Menu
    {
        $api = $this->apiFromRequest($request);
        return $api ? Menu::find($api->menu_id) : null;
    }

    private function apiFromRequest(Request $request): ?MenuApi
    {
        $path = $request->path();
        $queryString = $request->queryString();

        if ($queryString !== '') {
            $api = MenuApi::where('path', "{$path}?{$queryString}")->first();
            if ($api) {
                return $api;
            }

            $api = $this->apiFromQuerySubset($path, $request->get());
            if ($api) {
                return $api;
            }
        }

        return MenuApi::where('path', $path)->first();
    }

    private function apiFromQuerySubset(string $path, array $query): ?MenuApi
    {
        $rows = MenuApi::where('path', 'like', "{$path}?%")->get();
        $matched = null;
        $matchedCount = -1;

        foreach ($rows as $row) {
            $params = $this->queryParams((string)$row->path);
            if ($params === [] || !$this->queryContains($query, $params)) {
                continue;
            }

            if (count($params) > $matchedCount) {
                $matched = $row;
                $matchedCount = count($params);
            }
        }

        return $matched;
    }

    private function queryParams(string $path): array
    {
        $query = parse_url($path, PHP_URL_QUERY);
        if (!is_string($query) || $query === '') {
            return [];
        }

        parse_str($query, $params);
        return $params;
    }

    private function queryContains(array $source, array $expected): bool
    {
        foreach ($expected as $key => $value) {
            if (!array_key_exists($key, $source) || (string)$source[$key] !== (string)$value) {
                return false;
            }
        }

        return true;
    }

    private function operationTitle(?Menu $ruleMenu, string $actionLabel, string $moduleLabel): string
    {
        if ($ruleMenu && $ruleMenu->type === 'rule' && $ruleMenu->title !== '') {
            return $ruleMenu->title;
        }

        return $actionLabel . $this->operationTargetLabel($moduleLabel);
    }

    private function operationTargetLabel(string $moduleLabel): string
    {
        $suffix = '管理';
        if (str_ends_with($moduleLabel, $suffix)) {
            return substr($moduleLabel, 0, -strlen($suffix));
        }

        return $moduleLabel;
    }

    private function actionName(Request $request): string
    {
        return $request->action ?: basename($request->path());
    }

    private function resourceId(Request $request): string
    {
        $ids = $request->post('ids');
        if (is_array($ids)) {
            return implode(',', array_map('strval', $ids));
        }

        $id = $request->post('id', $request->get('id', ''));
        return is_scalar($id) ? (string)$id : '';
    }

    private function context(Request $request): array
    {
        return [
            'query' => $request->get(),
            'body' => $request->post(),
        ];
    }
}
