<?php

namespace App\Tasks;

use App\Entity\Task;
use App\Tasks\Exceptions\InvalidTaskRequestException;
use App\Tasks\Exceptions\TaskNotFoundException;

interface TasksWriting
{
    /**
     * @throws InvalidTaskRequestException
     * @throws TaskNotFoundException
     */
    public function save(TaskRequest $taskRequest, ?int $taskId = null): Task;
}
