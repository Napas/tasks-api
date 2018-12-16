<?php

namespace App\Controller;

use App\Tasks\Exceptions\InvalidTaskRequestException;
use App\Tasks\Exceptions\TaskNotFoundException;
use App\Tasks\TaskRequest;
use App\Tasks\TasksReading;
use App\Tasks\TasksWriting;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/tasks")
 */
class TasksController
{
    private const FORMAT_JSON = 'json';

    /**
     * @var TasksReading
     */
    private $tasksReader;

    /**
     * @var TasksWriting
     */
    private $tasksWriter;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        TasksReading $tasksReader,
        TasksWriting $tasksWriter,
        SerializerInterface $serializer
    ) {
        $this->tasksReader = $tasksReader;
        $this->tasksWriter = $tasksWriter;
        $this->serializer  = $serializer;
    }

    /**
     * @Route(
     *     "/{taskId}",
     *     methods={"GET"},
     *     requirements={
     *          "taskId"="\d+"
     *     }
     * )
     */
    public function getTask(int $taskId): Response
    {
        $task = $this->tasksReader->get($taskId);

        if (!$task) {
            return new JsonResponse(
                null,
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse(
            $this->serializer->serialize($task, self::FORMAT_JSON),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route(
     *     "/{taskId}",
     *     methods={"POST"},
     *     requirements={
     *          "taskId"="\d+"
     *     },
     *     defaults={
     *          "taskId"=null
     *     }
     * )
     */
    public function saveTask(Request $request, ?int $taskId = null): Response
    {
        /** @var TaskRequest $taskRequest */
        $taskRequest = $this->serializer->deserialize(
            $request->getContent(),
            TaskRequest::class,
            self::FORMAT_JSON
        );

        try {
            $task = $this->tasksWriter->save($taskRequest, $taskId);
        } catch (TaskNotFoundException $exception) {
            return new JsonResponse(
                null,
                JsonResponse::HTTP_NOT_FOUND
            );
        } catch (InvalidTaskRequestException $exception) {
            return new JsonResponse(
                $this->serializer->serialize(
                    $exception->getValidationErrors(),
                    self::FORMAT_JSON
                ),
                JsonResponse::HTTP_BAD_REQUEST,
                [],
                true
            );
        }

        return new JsonResponse(
            $this->serializer->serialize($task, self::FORMAT_JSON),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }
}
