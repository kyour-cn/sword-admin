<?php declare(strict_types=1);

namespace app\admin\services;

use app\common\constants\AuditLogDict;
use app\common\services\BaseService;
use app\model\AuditLog;
use app\model\Menu;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

class AuditLogService extends BaseService
{
    /**
     * 获取操作审计列表
     * @param array $params
     * @return array
     */
    public function getList(array $params): array
    {
        $row = $this->buildQuery($params)
            ->orderByDesc('id')
            ->paginate(perPage: $params['page_size'] ?? 10, page: $params['page'] ?? 1);

        return [
            'rows' => $row->items(),
            'total' => $row->total(),
            'page' => $row->currentPage(),
            'page_size' => $row->perPage()
        ];
    }

    /**
     * 获取操作审计统计
     * @param array $params
     * @return array
     */
    public function getStat(array $params): array
    {
        $rows = $this->buildQuery($params)
            ->select(['id', 'action', 'module', 'status', 'created_at'])
            ->get();

        $days = $this->generateDays($params['start_time'] ?? '', $params['end_time'] ?? '');
        $daily = array_fill_keys($days, 0);
        $actions = [];
        $modules = [];
        $status = ['success' => 0, 'fail' => 0];

        foreach ($rows as $row) {
            $day = substr((string)$row->created_at, 0, 10);
            $daily[$day] = ($daily[$day] ?? 0) + 1;
            $actions[$row->action] = ($actions[$row->action] ?? 0) + 1;
            $modules[$row->module] = ($modules[$row->module] ?? 0) + 1;
            empty($row->status) ? $status['fail']++ : $status['success']++;
        }

        return [
            'days' => array_keys($daily),
            'daily' => array_values($daily),
            'actions' => $this->formatCounter($actions, AuditLogDict::ACTIONS),
            'modules' => $this->formatCounter($modules, $this->moduleLabels()),
            'status' => $status,
            'total' => count($rows),
        ];
    }

    /**
     * 获取审计筛选字典
     * @return array
     */
    public function getActions(): array
    {
        return [
            'actions' => $this->formatOptions(AuditLogDict::ACTIONS),
            'modules' => $this->moduleOptions(),
        ];
    }

    /**
     * 构建操作审计查询
     * @param array $params
     * @return mixed
     */
    private function buildQuery(array $params): mixed
    {
        $query = AuditLog::query();

        if (!empty($params['keyword'])) {
            $keyword = trim((string)$params['keyword']);
            $query->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('actor_name', 'like', "%{$keyword}%")
                    ->orWhere('resource_id', 'like', "%{$keyword}%")
                    ->orWhere('path', 'like', "%{$keyword}%");
            });
        }

        if (!empty($params['module'])) {
            $query->where('module', '=', $params['module']);
        }

        if (!empty($params['action'])) {
            $query->where('action', '=', $params['action']);
        }

        if (isset($params['actor_id']) && $params['actor_id'] !== '') {
            $query->where('actor_id', '=', (int)$params['actor_id']);
        }

        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', '=', (int)$params['status']);
        }

        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $query->whereBetween('created_at', [$params['start_time'], $params['end_time']]);
        }

        return $query;
    }

    private function generateDays(string $startTime, string $endTime): array
    {
        $start = $startTime !== '' ? new DateTimeImmutable(substr($startTime, 0, 10)) : new DateTimeImmutable('first day of this month');
        $end = $endTime !== '' ? new DateTimeImmutable(substr($endTime, 0, 10)) : new DateTimeImmutable('today');

        if ($start > $end) {
            return [];
        }

        $days = [];
        $period = new DatePeriod($start, new DateInterval('P1D'), $end->modify('+1 day'));
        foreach ($period as $date) {
            $days[] = $date->format('Y-m-d');
        }

        return $days;
    }

    private function formatCounter(array $counter, array $labels): array
    {
        $rows = [];
        foreach ($counter as $value => $count) {
            $rows[] = [
                'label' => $labels[$value] ?? $value,
                'value' => $value,
                'count' => $count,
            ];
        }

        return $rows;
    }

    private function formatOptions(array $items): array
    {
        $options = [];
        foreach ($items as $value => $label) {
            $options[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $options;
    }

    /**
     * 模块筛选只展示实际审计数据中出现过的模块，避免兼容字典里的同名模块重复展示。
     * @return array
     */
    private function moduleOptions(): array
    {
        $labels = $this->moduleLabels();
        $modules = AuditLog::query()
            ->where('module', '<>', '')
            ->distinct()
            ->orderBy('module')
            ->pluck('module')
            ->toArray();

        $options = [];
        foreach ($modules as $module) {
            $module = (string)$module;
            $options[] = [
                'label' => $labels[$module] ?? $module,
                'value' => $module,
            ];
        }

        return $options;
    }

    /**
     * 从菜单生成模块字典，少量无菜单入口使用后端兜底字典。
     * @return array
     */
    private function moduleLabels(): array
    {
        $labels = AuditLogDict::FALLBACK_MODULES;
        $rows = Menu::where('type', 'menu')
            ->where('component', '<>', '')
            ->get(['name', 'title', 'component']);

        foreach ($rows as $row) {
            foreach ($this->moduleKeys($row->name, $row->component) as $key) {
                if ($key !== '') {
                    $labels[$key] = $row->title;
                }
            }
        }

        return $labels;
    }

    private function moduleKeys(string $name, string $component): array
    {
        $parts = array_values(array_filter(explode('/', $component)));
        $last = (string)end($parts);

        return array_unique(array_filter([
            $name,
            $last,
            lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $last)))),
        ]));
    }
}
