<?php

namespace App\Tests\Tasks;

use App\Entity\Task;
use App\Tasks\TasksReader;
use Doctrine\Common\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TasksReaderTest extends TestCase
{
    private const TASK_ID = 1;

    /**
     * @var ObjectRepository|MockObject
     */
    protected $repositoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var TasksReader
     */
    protected $tasksReader;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(ObjectRepository::class);
        $this->loggerMock     = $this->createMock(LoggerInterface::class);

        $this->tasksReader = new TasksReader(
            $this->repositoryMock,
            $this->loggerMock
        );
    }

    /**
     * @test
     */
    public function itRetrievesEntityFromTheRepositoryWithCorrectId(): void
    {
        $this
            ->repositoryMock
            ->expects(self::once())
            ->method('find')
            ->with(self::TASK_ID);

        $this->tasksReader->get(self::TASK_ID);
    }

    /**
     * @test
     */
    public function itReturnsTaskReturnedFromRepository(): void
    {
        $task = new Task();

        $this
            ->repositoryMock
            ->method('find')
            ->willReturn($task);

        self::assertSame(
            $task,
            $this->tasksReader->get(self::TASK_ID)
        );
    }
}
