<?php

namespace app\task;

use app\model\Task;

interface TaskHandlerInterface
{
    public function getName(): string;

    public function getDescription(): string;

    /**
     * @param Task $task
     * @return mixed
     * @throws TaskException
     */
    public function run(Task $task);

}
