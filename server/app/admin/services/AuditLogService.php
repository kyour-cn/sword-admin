<?php declare(strict_types=1);

namespace app\admin\services;

use app\common\constants\AuditLogDict;
use app\common\services\BaseService;
use app\model\AuditLog;
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
            ->select(['id', 'action', 'module', 'module_title', 'status', 'created_at'])
            ->orderByDesc('id')
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
            $module = (string)$row->module;
            if (!isset($modules[$module])) {
                $modules[$module] = [
                    'label' => $this->moduleTitle($row),
                    'value' => $module,
                    'count' => 0,
                ];
            }
            $modules[$module]['count']++;
            empty($row->status) ? $status['fail']++ : $status['success']++;
        }

        return [
            'days' => array_keys($daily),
            'daily' => array_values($daily),
            'actions' => $this->formatCounter($actions, AuditLogDict::ACTIONS),
            'modules' => array_values($modules),
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
     * 模块筛选只展示实际审计数据中出现过的模块，并使用最新日志的名称快照。
     * @return array
     */
    private function moduleOptions(): array
    {
        $rows = AuditLog::query()
            ->where('module', '<>', '')
            ->orderByDesc('id')
            ->get(['module', 'module_title']);

        $options = [];
        $seen = [];
        foreach ($rows as $row) {
            $module = (string)$row->module;
            if (isset($seen[$module])) {
                continue;
            }

            $options[] = [
                'label' => $this->moduleTitle($row),
                'value' => $module,
            ];
            $seen[$module] = true;
        }

        return $options;
    }

    /**
     * 模块名称使用写入审计日志时保存的快照，避免菜单配置变更污染历史展示。
     */
    private function moduleTitle(AuditLog $row): string
    {
        $title = trim((string)$row->module_title);

        return $title !== ''
            ? $title
            : AuditLogDict::FALLBACK_MODULES[$row->module] ?? (string)$row->module;
    }
}
