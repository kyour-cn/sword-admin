<?php

namespace app\process;

use app\model\Task as TaskModel;
use app\task\TaskException;
use app\task\TaskHandlerInterface;

/**
 * 异步任务
 */
class Task
{

    public function onWorkerStart()
    {
        while (true) {
            // 从数据库中获取待处理任务
            $task = TaskModel::where('status', 0)
                ->orderBy('id', 'asc')
                ->first();
            if (!$task) {
                sleep(5);
                continue;
            }
            echo "处理任务: " . $task->id. ' ' . $task->title . PHP_EOL;

            $taskClass = 'app\task\\' . $this->underscoreToCamelCase($task->label);
            if (!class_exists($taskClass)) {
                $task->status = -1;
                $task->result = '任务类不存在';
                $task->save();
                continue;
            }

            $task->status = 1;
            $task->save();

            try {
                /** @var TaskHandlerInterface $taskHandler */
                $taskHandler = new $taskClass();
                $res = $taskHandler->run($task);
                $task->status = 2;
                $task->result = $res;
                $task->save();
            } catch (TaskException $e) {
                $task->status = -1;
                $task->result = $e->getMessage();
                $task->save();
            } catch (\Throwable $e) {
                $task->status = -1;
                $task->result = $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() .
                    "\n" . $e->getTraceAsString();
                $task->save();
            } finally {
                echo "任务完成: " . $task->id. ' ' . $task->title . PHP_EOL;
            }
        }
    }

    private function underscoreToCamelCase($string) {
        // 将下划线分隔的单词转换为首字母大写
        $words = explode('_', $string);
        $camelCase = '';

        foreach ($words as $word) {
            $camelCase .= ucfirst($word);
        }

        return $camelCase;
    }

}