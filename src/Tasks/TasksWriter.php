<?php

namespace App\Tasks;

use App\Entity\Task;
use App\Tasks\Exceptions\InvalidTaskRequestException;
use App\Tasks\Exceptions\TaskNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TasksWriter implements TasksWriting
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var TasksReading
     */
    private $tasksReader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        TasksReading $tasksReader,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->validator     = $validator;
        $this->tasksReader   = $tasksReader;
        $this->logger        = $logger;
    }

    /**
     * @throws InvalidTaskRequestException
     * @throws TaskNotFoundException
     */
    public function save(TaskRequest $taskRequest, ?int $taskId = null): Task
    {
        $validationErrors = $this->validator->validate($taskRequest);

        if ($validationErrors->count()) {
            $this->logger->debug('Invalid task request');

            throw new InvalidTaskRequestException($validationErrors, $taskRequest);
        }

        $task = new Task();

        if ($taskId !== null) {
            $task = $this->tasksReader->get($taskId);

            if (!$task) {
                $this->logger->debug(
                    sprintf(
                        'Tasks with id %d was not found',
                        $taskId
                    )
                );

                throw new TaskNotFoundException($taskId);
            }
        }

        $task
            ->setName($taskRequest->getName())
            ->setDescription($taskRequest->getDescription())
            ->setDeadlineAt($taskRequest->getDeadlineAt());

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }
}
