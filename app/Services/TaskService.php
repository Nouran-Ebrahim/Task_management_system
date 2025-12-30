<?php

namespace App\Services;

use App\Http\Resources\TaskResource;
use App\Repositories\TaskRepository;
use App\Enums\TaskStatus;
use App\Traits\ApiResponse;
use Exception;

class TaskService
{
    use ApiResponse;

    protected $taskRepository;
    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }
    public function store($data)
    {
        $data['created_by'] = auth()->user()->id;
        $data['status'] = TaskStatus::PENDING->value;
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
        if (in_array($task->id, $depends_on_task_ids)) {
            throw new Exception('Task can not depend on it self', 422);

        }
        $task = $this->taskRepository->addDependencies($task, $depends_on_task_ids);
        return $task;

    }
    public function statusUpdate($task,$status)
    {
        if ($status == TaskStatus::COMPLETED->value && !$task->canBeCompleted()) {
             throw new Exception('Task can not be completed until all its dependencies are completed', 422);
            
        }
        $task = $this->taskRepository->statusUpdate($task, $status);
        return $task;

    }
    public function index($request = [])
    {
        $tasks = $this->taskRepository->index($request);
        return $tasks;

    }


}
