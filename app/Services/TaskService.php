<?php

namespace App\Services;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Repositories\TaskRepository;
use App\Enums\TaskStatus;
use App\Traits\ApiResponse;
use Exception;

class TaskService
{
    use ApiResponse;

    private $taskRepository;
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
        if ($data['status'] == TaskStatus::COMPLETED->value && !$task->canBeCompleted()) {
            throw new Exception('Task can not be completed until all its dependencies are completed', 422);

        }
        $task = $this->taskRepository->update($task, $data);
        return $task;

    }
    public function addDependencies($task, $depends_on_task_ids)
    {
        if (in_array($task->id, $depends_on_task_ids)) {
            throw new Exception('Task can not depend on it self', 422);
        }
        //Check Existing Dependencies
        $existingDependencies = $task->dependencies()
            ->wherePivotIn('depends_on_task_id', $depends_on_task_ids)
            ->pluck('title')
            ->toArray();
        if (!empty($existingDependencies)) {
            throw new Exception('The following tasks are already dependencies: ' . implode(', ', $existingDependencies), 422);
        }
        //Check Canceld task
        $canceldTasks = Task::whereIn('id', $depends_on_task_ids)
            ->where('status', TaskStatus::CANCELED->value)
            ->pluck('title')
            ->toArray();
        if (!empty($canceldTasks)) {
            throw new Exception('The following tasks are already canceled: ' . implode(', ', $canceldTasks) . ' can not be added', 422);
        }
        //Check Circulation
        foreach ($depends_on_task_ids as $depend_id) {
            if ($task->hasCirculation($depend_id)) {
                throw new Exception('some dependencies will cause circulation', 422);
            }
        }

        $task = $this->taskRepository->addDependencies($task, $depends_on_task_ids);
        return $task;

    }
    public function statusUpdate($task, $status)
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
