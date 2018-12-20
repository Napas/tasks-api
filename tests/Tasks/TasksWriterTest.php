<?php

namespace App\Tests\Tasks;

use App\Entity\Task;
use App\Tasks\TaskRequest;
use App\Tasks\TasksReading;
use App\Tasks\TasksWriter;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TasksWriterTest extends TestCase
{
    protected const TASK_ID = 1;

    /**
     * @var EntityManager|MockObject
     */
    private $entityManagerMock;

    /**
     * @var ValidatorInterface|MockObject
     */
    private $validatorMock;

    /**
     * @var TasksReading|MockObject
     */
    private $tasksReaderMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ConstraintViolationListInterface|MockObject
     */
    private $validationErrorsMock;

    /**
     * @var TasksWriter
     */
    private $tasksWriter;

    protected function setUp()
    {
        $this->entityManagerMock    = $this->createMock(EntityManagerInterface::class);
        $this->validatorMock        = $this->createMock(ValidatorInterface::class);
        $this->tasksReaderMock      = $this->createMock(TasksReading::class);
        $this->loggerMock           = $this->createMock(LoggerInterface::class);
        $this->validationErrorsMock = $this->createMock(ConstraintViolationListInterface::class);

        $this
            ->validatorMock
            ->method('validate')
            ->willReturn($this->validationErrorsMock);

        $this->tasksWriter = new TasksWriter(
            $this->entityManagerMock,
            $this->validatorMock,
            $this->tasksReaderMock,
            $this->loggerMock
        );
    }

    /**
     * @test
     */
    public function validatesRequest(): void
    {
        $taskRequest = new TaskRequest();
        $taskRequest
            ->setName('Task')
            ->setDeadlineAt(Carbon::now());

        $this
            ->validatorMock
            ->expects(self::once())
            ->method('validate')
            ->with($taskRequest)
            ->willReturn($this->validationErrorsMock);

        $this->validatorWillNotReturnErrors();

        $this->tasksWriter->save($taskRequest);
    }

    /**
     * @test
     * @expectedException App\Tasks\Exceptions\InvalidTaskRequestException
     */
    public function ifRequestIsInvalidItThrowsInvalidTaskRequestException(): void
    {
        $taskRequest = new TaskRequest();
        $taskRequest
            ->setName('Task')
            ->setDeadlineAt(Carbon::now());

        $validationErrors = $this->createMock(ConstraintViolationListInterface::class);

        $this
            ->validatorMock
            ->method('validate')
            ->willReturn($validationErrors);

        $this
            ->validationErrorsMock
            ->method('count')
            ->willReturn(1);

        $this->tasksWriter->save($taskRequest);
    }

    /**
     * @test
     */
    public function persistsTask(): void
    {
        $this->validatorWillNotReturnErrors();

        $taskRequest = new TaskRequest();
        $taskRequest
            ->setName('Task')
            ->setDescription('Description')
            ->setDeadlineAt(Carbon::now());

        $this
            ->entityManagerMock
            ->expects(self::once())
            ->method('persist')
            ->with(
                self::callback(
                    function (Task $task) use ($taskRequest): bool {
                        return $task->getName() === $taskRequest->getName() &&
                            $task->getDescription() === $taskRequest->getDescription() &&
                            $task->getDeadlineAt() === $taskRequest->getDeadlineAt();
                    }
                )
            );

        $this->tasksWriter->save($taskRequest);
    }

    /**
     * @test
     */
    public function flushesEntityManagerAfterPersistingTask(): void
    {
        $this->validatorWillNotReturnErrors();

        $taskRequest = new TaskRequest();
        $taskRequest
            ->setName('Task')
            ->setDeadlineAt(Carbon::now());

        $this
            ->entityManagerMock
            ->expects(self::at(0))
            ->method('persist');

        $this
            ->entityManagerMock
            ->expects(self::at(1))
            ->method('flush');

        $this->tasksWriter->save($taskRequest);
    }

    /**
     * @test
     */
    public function returnsSavedTask(): void
    {
        $this->validatorWillNotReturnErrors();

        $now = Carbon::now();

        $taskRequest = new TaskRequest();
        $taskRequest
            ->setName('Task')
            ->setDeadlineAt($now);

        $task = $this->tasksWriter->save($taskRequest);

        self::assertSame('Task', $task->getName());
        self::assertSame($now, $task->getDeadlineAt());
    }

    /**
     * @test
     */
    public function ifTaskIdIsPassedUpdateExistingTaskInsteadCreatingNewOne(): void
    {
        $this->validatorWillNotReturnErrors();

        $yesterday = Carbon::yesterday();

        $task = new Task();
        $task->setCreatedAt($yesterday);

        $taskRequest = new TaskRequest();
        $taskRequest
            ->setName('Task')
            ->setDeadlineAt(Carbon::now());

        $this
            ->tasksReaderMock
            ->expects(self::once())
            ->method('get')
            ->with(self::TASK_ID)
            ->willReturn($task);

        $updatedTask = $this->tasksWriter->save($taskRequest, self::TASK_ID);

        self::assertSame(
            $yesterday,
            $updatedTask->getCreatedAt()
        );
    }

    /**
     * @test
     * @expectedException App\Tasks\Exceptions\TaskNotFoundException
     */
    public function ifTaskIdIsPassedButTaskDoesNotExistThrowTaskNotFoundException(): void
    {
        $this->validatorWillNotReturnErrors();

        $this
            ->tasksReaderMock
            ->method('get')
            ->willReturn(null);

        $this->tasksWriter->save(new TaskRequest(), self::TASK_ID);
    }

    /**
     * @return TasksWriterTest
     */
    private function validatorWillNotReturnErrors(): self
    {
        $this
            ->validationErrorsMock
            ->method('count')
            ->willReturn(0);

        return $this;
    }
}
