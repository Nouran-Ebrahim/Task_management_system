<?php

namespace App\Repositories;

use App\Enums\TaskStatus;
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
    public function index($request = [])
    {
        $tasks = Task::available()->with(['assignee', 'createdby', 'dependencies'])
            ->when($request['status'] ?? null, function ($q) use ($request) {
                $q->where('status', $request['status']);
            })->when($request['assigned_to'] ?? null, function ($q) use ($request) {
                $q->where('assigned_to', $request['assigned_to']);
            })->when($request['due_date_from'] ?? null, function ($q) use ($request) {
                $q->where('due_date', '>=', $request['due_date_from']);
            })->when($request['due_date_to'] ?? null, function ($q) use ($request) {
                $q->where('due_date', '<=', $request['due_date_to']);
            })->latest()->paginate($request['per_page'] ?? 10);
        return $tasks;
    }

    public function addDependencies($task, $depends_on_task_ids)
    {
        if (in_array($task->id, $depends_on_task_ids)) {
            return false;

        }
        return $task->dependencies()->sync($depends_on_task_ids);

    }
    public function statusUpdate($task, $status)
    {
        if (auth()->user()->isUser() && (auth()->user()->id != $task->assigned_to)) {
            return [
                'data' => [],
                'errors' => [],
                'msg' => 'This not your task',
                'status' => false,
                'code' => 403
            ];
        }
        if ($status == TaskStatus::COMPLETED->value && !$task->canBeCompleted()) {
            return [
                'data' => [],
                'errors' => [],
                'msg' => 'Task can not be completed until all its dependencies are completed',
                'status' => false,
                'code' => 422
            ];

        }
        $task->status = $status;
        $task->save();
        return [
            'data' => [],
            'errors' => [],
            'msg' => 'Status Updated successfully',
            'status' => true,
            'code' => 200
        ];

    }
}
