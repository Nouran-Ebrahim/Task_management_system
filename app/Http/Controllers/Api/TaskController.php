<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\FillterationRequest;
use App\Http\Requests\TaskDependenciesRequest;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Log;
use Illuminate\Validation\Rule;
class TaskController extends Controller
{
    use ApiResponse;
    private $taskService;
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }
    public function index(FillterationRequest $request)
    {
        $data = $request->validated();
        $tasks = $this->taskService->index($data);
        if ($tasks->count() > 0) {
            return $this->apiResponse(
                TaskResource::collection($tasks),
                [],
                'Data found',
                true,
                200,
                $tasks
            );
        }
        return $this->apiResponse(
            [],
            [],
            'No data yet',
            true,
            200,
        );

    }
    public function statusUpdate(Task $task, Request $request)
    {
        if ($request->user()->cannot('statusUpdate', $task)) {
            return $this->apiResponse([], [], 'Unauthorized', false, 403);

        }
        $request->validate([
            'status' => ['required', 'string', Rule::in(TaskStatus::values())],
        ]);
        try {
            DB::beginTransaction();
            $response = $this->taskService->statusUpdate($task,$request->status);
            DB::commit();
            return $this->apiResponse(
                $response['data'],
                $response['errors'],
                $response['msg'],
                $response['status'],
                $response['code']
            );

        } catch (\Exception $exception) {
            DB::rollback();
            report($exception);
            return $this->apiResponse([], [], $exception->getMessage(), false, 500);

        }

    }


    public function store(TaskRequest $request)
    {
        if ($request->user()->cannot('create', Task::class)) {
            return $this->apiResponse([], [], 'Unauthorized, Managers only can create', false, 403);

        }
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $data['created_by'] = $request->user()->id;
            $data['status'] = TaskStatus::PENDING->value;
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
         if ($request->user()->cannot('addDependencies', $task)) {
            return $this->apiResponse([], [], 'Unauthorized, Managers only can add dependencies', false, 403);

        }

        try {
            DB::beginTransaction();
            $status = $this->taskService->addDependencies($task, $request->depends_on_task_id);
            if ($status == false) {
                return $this->apiResponse([], [], 'Task can not depend on it self', false, 422);

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
        if ($request->user()->cannot('update', $task)) {
            return $this->apiResponse([], [], 'Unauthorized, Managers only can update', false, 403);

        }
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
        if (auth()->user()->cannot('view', $task)) {
            return $this->apiResponse([], [], 'Unauthorized', false, 403);

        }
        return $this->apiResponse(
            [
                'task' => TaskResource::make($task->load(['dependencies', 'createdby', 'assignee'])),
            ],
            [],
            'Data found',
            true,
            201
        );

    }
    public function destroy(Task $task)
    {
        if (auth()->user()->cannot('delete', $task)) {
            return $this->apiResponse([], [], 'Unauthorized, Mangers only can delete', false, 403);

        }
        $task->delete();
        return $this->apiResponse(
            [],
            [],
            'Deleted successfully',
            true,
            200
        );

    }
}
