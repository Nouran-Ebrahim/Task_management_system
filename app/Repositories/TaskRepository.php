<?php

namespace App\Repositories;

use App\Models\Task;
use App\Traits\ApiResponse;

class TaskRepository
{
    use ApiResponse;
    public function store($data)
    {
        return Task::create($data);
    }
    public function update($task, $data)
    {
        return $task->update($data);
    }
    public function addDependencies($task, $depends_on_task_ids)
    {
        if (in_array($task->id, $depends_on_task_ids)) {
            return false;

        }
        return $task->dependencies()->sync($depends_on_task_ids);

    }
}
