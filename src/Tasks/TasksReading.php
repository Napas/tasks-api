<?php

namespace App\Tasks;

use App\Entity\Task;

interface TasksReading
{
    function get(int $taskId): ?Task;
}
