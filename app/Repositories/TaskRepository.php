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
        return $task->dependencies()->attach($depends_on_task_ids);
    }
    public function statusUpdate($task, $status)
    {
        
        $task->status = $status;
        $task->save();
        return $task;

    }
}
