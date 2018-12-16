<?php

namespace App\Tasks\Exceptions;

use App\Tasks\TaskRequest;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class InvalidTaskRequestException extends \Exception
{
    /**
     * @var ConstraintViolationListInterface
     */
    private $validationErrors;

    /**
     * @var TaskRequest
     */
    private $taskRequest;

    public function __construct(
        ConstraintViolationListInterface $validationErrors,
        TaskRequest $taskRequest
    ) {
        $this->validationErrors = $validationErrors;
        $this->taskRequest      = $taskRequest;

        parent::__construct('Invalid task');
    }

    public function getValidationErrors(): ConstraintViolationListInterface
    {
        return $this->validationErrors;
    }

    public function getTaskRequest(): TaskRequest
    {
        return $this->taskRequest;
    }
}
