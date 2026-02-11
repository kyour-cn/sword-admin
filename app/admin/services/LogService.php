<?php declare(strict_types=1);

namespace app\admin\services;

use app\common\exception\BusinessException;
use app\common\services\BaseService;
use app\model\Log;
use app\model\LogType;
use DateTime;

class LogService extends BaseService
{

    public function getTypeList(): array
    {
        $row = LogType::where('status', 1)
            ->paginate(perPage: $params['page_size'] ?? 10, page: $params['page'] ?? 1);

        return [
            'rows' => $row->items(),
            'total' => $row->total(),
            'page' => $row->currentPage(),
            'page_size' => $row->perPage()
        ];
    }

    /**
     * 获取列表
     * @param array $params
     * @return array
     */
    public function getList(array $params): array
    {
        $conds = [];

        if (!empty($params['type_id'])) {
            $conds[] = ['type_id', '=', $params['type_id']];
        }

        if (empty($params['start_time']) or empty($params['end_time'])) {
            throw new BusinessException('时间范围不能为空');
        }

        $row = Log::where($conds)
            ->whereBetween('created_at', [$params['start_time'], $params['end_time']])
            ->paginate(perPage: $params['page_size'] ?? 10, page: $params['page'] ?? 1);

        return [
            'rows' => $row->items(),
            'total' => $row->total(),
            'page' => $row->currentPage(),
            'page_size' => $row->perPage()
        ];
    }

    /**
     * 获取统计
     * @param array $params
     * @return array
     */
    public function getStat(array $params): array
    {
        if (empty($params['start_time']) or empty($params['end_time'])) {
            throw new BusinessException('时间范围不能为空');
        }
        $conds = [];

        $days = $this->generateDays(
            $params['start_time'],
            $params['end_time']
        );

        $rows = Log::where($conds)
            ->whereBetween('created_at', [$params['start_time'], $params['end_time']])
            ->selectRaw("
                    date_format(`created_at`, '%Y-%m-%d') AS `date`,
                    count(*) AS `count`,
                    type_name,
                    type_id
                ")
            ->groupBy('date', 'type_name', 'type_id')
            ->get();

        return [
            'rows' => $rows,
            'days' => $days,
        ];
    }

    /**
     * 生成日期列表
     *
     * @param string $startDate 开始日期
     * @param string $endDate 结束日期
     * @param string $format 日期格式，默认 'Y-m-d'
     * @return array 日期字符串数组
     * @throws \DateMalformedStringException
     */
    private function generateDays($startDate, $endDate, $format = 'Y-m-d')
    {
        $days = [];

        // 将字符串转换为 DateTime 对象
        $current = new DateTime($startDate);
        $end = new DateTime($endDate);

        // 循环生成日期列表
        while ($current <= $end) {
            $days[] = $current->format($format);
            $current->modify('+1 day');
        }

        return $days;
    }


}