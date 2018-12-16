<?php

namespace App\Tests\Controller;

use App\Controller\TasksController;
use App\Entity\Task;
use App\Tasks\Exceptions\InvalidTaskRequestException;
use App\Tasks\Exceptions\TaskNotFoundException;
use App\Tasks\TaskRequest;
use App\Tasks\TasksReading;
use App\Tasks\TasksWriting;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class TasksControllerTest extends TestCase
{
    private const TASK_ID          = 1;
    private const FORMAT_JSON      = 'json';
    private const HTTP_BAD_REQUEST = 400;
    private const HTTP_NOT_FOUND   = 404;
    private const REQUEST_BODY     = '{"name":"Task"}';

    /**
     * @var TasksReading|MockObject
     */
    private $tasksReaderMock;

    /**
     * @var TasksWriting|MockObject
     */
    private $tasksWriterMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * @var TasksController
     */
    private $tasksController;

    protected function setUp(): void
    {
        $this->tasksReaderMock = $this->createMock(TasksReading::class);
        $this->tasksWriterMock = $this->createMock(TasksWriting::class);
        $this->serializerMock  = $this->createMock(SerializerInterface::class);

        $this->tasksController = new TasksController(
            $this->tasksReaderMock,
            $this->tasksWriterMock,
            $this->serializerMock
        );
    }

    /**
     * @test
     */
    public function getRetrievesTaskFromTheTasksReaderWithCorrectId(): void
    {
        $this->serializeWillReturn();

        $this
            ->tasksReaderMock
            ->expects(self::once())
            ->method('get')
            ->with(self::TASK_ID)
            ->willReturn(new Task());

        $this->tasksController->getTask(self::TASK_ID);
    }

    /**
     * @test
     */
    public function getSerializesTask(): void
    {
        $task = new Task();

        $this->tasksReaderWillReturn($task);

        $this
            ->serializerMock
            ->expects(self::once())
            ->method('serialize')
            ->with(
                $task,
                self::FORMAT_JSON
            )
            ->willReturn('{}');

        $this->tasksController->getTask(self::TASK_ID);
    }

    /**
     * @test
     */
    public function getReturnsJsonResponseWithSerializedTask(): void
    {
        $task = new Task();
        $task->setName('name');

        $serialized = '{"name":"name"}';

        $this
            ->serializeWillReturn($serialized)
            ->tasksReaderWillReturn($task);

        self::assertEquals(
            $serialized,
            $this->tasksController->getTask(self::TASK_ID)->getContent(),
            );
    }

    /**
     * @test
     */
    public function getReturnsHttpResponseWithStatusCode404IfTaskDoesNotExist(): void
    {
        $this->tasksReaderWillReturn(null);

        self::assertEquals(
            self::HTTP_NOT_FOUND,
            $this->tasksController->getTask(self::TASK_ID)->getStatusCode(),
            );
    }

    /**
     * @test
     */
    public function saveDeserializeRequest(): void
    {
        $this->serializeWillReturn();

        $request = new Request([], [], [], [], [], [], self::REQUEST_BODY);

        $this
            ->serializerMock
            ->expects(self::once())
            ->method('deserialize')
            ->with(
                self::REQUEST_BODY,
                TaskRequest::class,
                self::FORMAT_JSON
            )
            ->willReturn(new TaskRequest());

        $this->tasksController->saveTask($request);
    }

    /**
     * @test
     */
    public function savePassesTaskRequestAndTaskIdToTheTasksWriter(): void
    {
        $tasksRequest = new TaskRequest();

        $this
            ->deserializeWillReturn($tasksRequest)
            ->serializeWillReturn();

        $this
            ->tasksWriterMock
            ->expects(self::once())
            ->method('save')
            ->with(
                $tasksRequest,
                self::TASK_ID
            );

        $this->tasksController->saveTask(new Request(), self::TASK_ID);
    }

    /**
     * @test
     */
    public function saveSerializesSavedTask(): void
    {
        $task = new Task();

        $this
            ->deserializeWillReturn(new TaskRequest())
            ->tasksWriterWillReturn($task);

        $this
            ->serializerMock
            ->expects(self::once())
            ->method('serialize')
            ->with(
                $task,
                self::FORMAT_JSON
            )
            ->willReturn('{}');

        $this->tasksController->saveTask(new Request());
    }

    /**
     * @test
     */
    public function saveReturnSerializedTaskInJsonResponse(): void
    {
        $serializedTask = '{"name":"Task"}';
        $this
            ->deserializeWillReturn(new TaskRequest())
            ->tasksWriterWillReturn(new Task())
            ->serializeWillReturn($serializedTask);

        $response = $this->tasksController->saveTask(new Request());

        self::assertSame(
            $serializedTask,
            $response->getContent()
        );
    }

    /**
     * @test
     */
    public function saveReturnsResponseWithHttpStatusCode404IfWriterThrowsTaskNotFoundException(): void
    {
        $this->deserializeWillReturn(new TaskRequest());

        $this
            ->tasksWriterMock
            ->method('save')
            ->willThrowException(
                new TaskNotFoundException(self::HTTP_NOT_FOUND)
            );

        self::assertSame(
            Response::HTTP_NOT_FOUND,
            $this->tasksController->saveTask(new Request())->getStatusCode()
        );
    }

    /**
     * @test
     */
    public function saveSerializesValidationErrorsIfTasksWriterThrowsInvalidTaskRequestException(): void
    {
        $this
            ->deserializeWillReturn(new TaskRequest())
            ->serializeWillReturn();

        $validationErrors = $this->createMock(ConstraintViolationListInterface::class);

        $this
            ->tasksWriterMock
            ->method('save')
            ->willThrowException(
                new InvalidTaskRequestException(
                    $validationErrors,
                    new TaskRequest()
                )
            );

        $this
            ->serializerMock
            ->expects(self::once())
            ->method('serialize')
            ->with(
                $validationErrors,
                self::FORMAT_JSON
            );

        $this->tasksController->saveTask(new Request());
    }

    /**
     * @test
     */
    public function saveReturnsSerializedValidationErrorsIfTaskWriterThrowsInvalidTaskRequestException(): void
    {
        $serializedErrors = '{"erorrs": []}';

        $this
            ->deserializeWillReturn(new TaskRequest())
            ->serializeWillReturn($serializedErrors);

        $validationErrors = $this->createMock(ConstraintViolationListInterface::class);

        $this
            ->tasksWriterMock
            ->method('save')
            ->willThrowException(
                new InvalidTaskRequestException(
                    $validationErrors,
                    new TaskRequest()
                )
            );

        self::assertSame(
            $serializedErrors,
            $this->tasksController->saveTask(new Request())->getContent()
        );
    }

    /**
     * @test
     */
    public function saveReturns400AsHttpResponseCodeIfTasksWriterThrowsInvalidTaskRequestException(): void
    {
        $this
            ->deserializeWillReturn(new TaskRequest())
            ->serializeWillReturn();

        $validationErrors = $this->createMock(ConstraintViolationListInterface::class);

        $this
            ->tasksWriterMock
            ->method('save')
            ->willThrowException(
                new InvalidTaskRequestException(
                    $validationErrors,
                    new TaskRequest()
                )
            );

        self::assertSame(
            self::HTTP_BAD_REQUEST,
            $this->tasksController->saveTask(new Request())->getStatusCode()
        );
    }

    private function tasksWriterWillReturn(Task $task): self
    {
        $this
            ->tasksWriterMock
            ->method('save')
            ->willReturn($task);

        return $this;
    }

    private function serializeWillReturn(string $data = '{}'): self
    {
        $this
            ->serializerMock
            ->method('serialize')
            ->willReturn($data);

        return $this;
    }

    private function tasksReaderWillReturn(?Task $task): self
    {
        $this
            ->tasksReaderMock
            ->method('get')
            ->willReturn($task);

        return $this;
    }

    private function deserializeWillReturn(object $object): self
    {
        $this
            ->serializerMock
            ->method('deserialize')
            ->willReturn($object);

        return $this;
    }
}
