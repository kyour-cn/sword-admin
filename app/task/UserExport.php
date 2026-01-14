<?php

namespace app\task;


use app\model\Task;

/**
 * 用户导出任务处理类
 * @api
 */
class UserExport  implements TaskHandlerInterface
{

    public function getName(): string
    {
        return '用户导出';
    }

    public function getDescription(): string
    {
        return '导出用户到表格';
    }

    /**
     *  任务处理
     * @param Task $task
     * @return string
     */
    public function run(Task $task): string
    {
        $params = json_decode($task->content, true);

        // TODO: 导出用户到表格

        // 返回结果
        return json_encode([
            'message' => '导出成功',
            'success' => 100,
            'file' => 'user_export.xlsx'
        ], JSON_UNESCAPED_UNICODE);
    }
}
