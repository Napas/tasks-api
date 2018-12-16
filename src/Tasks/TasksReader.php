<?php

namespace App\Tasks;

use App\Entity\Task;
use Doctrine\Common\Persistence\ObjectRepository;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class TasksReader implements TasksReading
{
    use LoggerAwareTrait;

    /**
     * @var ObjectRepository
     */
    private $repository;

    public function __construct(
        ObjectRepository $repository,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->setLogger($logger);
    }

    function get(int $taskId): ?Task
    {
        $this->logger->debug(
            sprintf(
                'Retrieving task with id %d from DB.',
                $taskId
            )
        );

        /** @var Task|null $task */
        $task = $this->repository->find($taskId);

        return $task;
    }
}
