<?php

namespace App\Services;

use App\Http\Resources\TaskResource;
use App\Repositories\TaskRepository;

class TaskService
{
    protected $taskRepository;
    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }
    public function store($data)
    {
        $task = $this->taskRepository->store($data);
        return $task;

    }
    public function update($task, $data)
    {
        $task = $this->taskRepository->update($task, $data);
        return $task;

    }
    public function addDependencies($task, $depends_on_task_ids)
    {
        $task = $this->taskRepository->addDependencies($task, $depends_on_task_ids);
        return $task;

    }
    public function statusUpdate($task,$status)
    {
        $task = $this->taskRepository->statusUpdate($task, $status);
        return $task;

    }
    public function index($request = [])
    {
        $tasks = $this->taskRepository->index($request);
        return $tasks;

    }


}
