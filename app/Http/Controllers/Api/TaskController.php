<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskDependenciesRequest;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Log;
class TaskController extends Controller
{
    use ApiResponse;
    private $taskService;
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function store(TaskRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $data['created_by'] = $request->user()->id;
            $task = $this->taskService->store($data);
            DB::commit();
            return $this->apiResponse(
                [
                    'task' => TaskResource::make($task)
                ],
                [],
                'Created successfully',
                true,
                201
            );

        } catch (\Exception $exception) {
            DB::rollback();
            report($exception);
            return $this->apiResponse([], [], $exception->getMessage(), false, 500);

        }

    }
    public function addDependencies(Task $task, TaskDependenciesRequest $request)
    {

        try {
            DB::beginTransaction();
            $status = $this->taskService->addDependencies($task, $request->depends_on_task_id);
            if ($status == false) {
                return $this->apiResponse([], [], 'Can not add main task as dependency', false, 422);

            }
            DB::commit();
            return $this->apiResponse(
                [],
                [],
                'Dependencies added successfully',
                true,
                201
            );

        } catch (\Exception $exception) {
            DB::rollback();
            report($exception);
            return $this->apiResponse([], [], $exception->getMessage(), false, 500);

        }

    }

    public function update(Task $task, TaskRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $this->taskService->update($task, $data);
            DB::commit();
            return $this->apiResponse(
                [],
                [],
                'Updated successfully',
                true,
                200
            );

        } catch (\Exception $exception) {
            DB::rollback();
            report($exception);
            return $this->apiResponse([], [], $exception->getMessage(), false, 500);

        }

    }
    public function show(Task $task)
    {

        return $this->apiResponse(
            [
                'task' => TaskResource::make($task->load(['dependencies','createdby','assignee'])),
            ],
            [],
            'Data found',
            true,
            201
        );

    }
}
