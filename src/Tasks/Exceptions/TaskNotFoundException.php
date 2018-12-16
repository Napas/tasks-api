<?php

namespace App\Tasks\Exceptions;

class TaskNotFoundException extends \Exception
{
    /**
     * @var int
     */
    private $taskId;

    public function __construct(int $taskId)
    {
        $this->taskId = $taskId;

        parent::__construct(
            sprintf(
                'Task with id %d was not found',
                $taskId
            )
        );
    }

    public function getTaskId(): int
    {
        return $this->taskId;
    }
}
