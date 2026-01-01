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
    public function statusUpdate($id, Request $request)
    {
        $task = $this->taskService->find($id);
        if (!$task) {
            return $this->apiResponse([], [], 'Task not found', false, 404);
        }
        if ($request->user()->cannot('statusUpdate', $task)) {
            return $this->apiResponse([], [], 'Unauthorized, it is not your task', false, 403);

        }
        $request->validate([
            'status' => ['required', 'string', Rule::in(TaskStatus::values())],
        ]);
        try {
            DB::beginTransaction();
            if ($request->user()->isUser() && $request->status == TaskStatus::CANCELED->value) {
                return $this->apiResponse(
                    [],
                    [],
                    'Only managers can cancel tasks',
                    false,
                    403
                );
            }
            $this->taskService->statusUpdate($task, $request->status);
            DB::commit();
            return $this->apiResponse(
                [],
                [],
                'Status Updated successfully',
                true,
                200
            );

        } catch (\Exception $exception) {
            DB::rollback();
            report($exception);
            $code = $exception->getCode();
            if (!is_int($code) || $code < 100 || $code > 599) {
                $code = 500;
            }
            return $this->apiResponse([], [], $exception->getMessage(), false, $code);

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
            $task = $this->taskService->store($data);
            DB::commit();
            return $this->apiResponse(
                TaskResource::make($task),
                [],
                'Created successfully',
                true,
                201
            );

        } catch (\Exception $exception) {
            DB::rollback();
            $code = $exception->getCode();
            if (!is_int($code) || $code < 100 || $code > 599) {
                $code = 500;
            }
            return $this->apiResponse([], [], $exception->getMessage(), false, $code);

        }

    }
    public function addDependencies($id, TaskDependenciesRequest $request)
    {
        $task = $this->taskService->find($id);
        if (!$task) {
            return $this->apiResponse([], [], 'Task not found', false, 404);
        }
        if ($request->user()->cannot('addDependencies', $task)) {
            return $this->apiResponse([], [], 'Unauthorized, Managers only can add dependencies', false, 403);

        }

        try {
            DB::beginTransaction();
            $this->taskService->addDependencies($task, $request->depends_on_task_id);
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
            $code = $exception->getCode();
            if (!is_int($code) || $code < 100 || $code > 599) {
                $code = 500;
            }
            return $this->apiResponse([], [], $exception->getMessage(), false, $code);

        }

    }

    public function update($id, TaskRequest $request)
    {
        $task = $this->taskService->find($id);
        if (!$task) {
            return $this->apiResponse([], [], 'Task not found', false, 404);
        }
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
            $code = $exception->getCode();
            if (!is_int($code) || $code < 100 || $code > 599) {
                $code = 500;
            }
            return $this->apiResponse([], [], $exception->getMessage(), false, $code);

        }

    }
    public function show($id)
    {
        $task = $this->taskService->find($id);
        if (!$task) {
            return $this->apiResponse([], [], 'Task not found', false, 404);
        }
        if (auth()->user()->cannot('view', $task)) {
            return $this->apiResponse([], [], 'Unauthorized', false, 403);

        }
        return $this->apiResponse(
            TaskResource::make($task->load(['dependencies', 'createdby', 'assignee'])),
            [],
            'Data found',
            true,
            201
        );

    }
    public function destroy($id)
    {
        $task = $this->taskService->find($id);
        if (!$task) {
            return $this->apiResponse([], [], 'Task not found', false, 404);
        }
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
    public function removeDependency($id, Request $request)
    {
        $task = $this->taskService->find($id);
        if (!$task) {
            return $this->apiResponse([], [], 'Task not found', false, 404);
        }
        if (auth()->user()->cannot('removeDependency', $task)) {
            return $this->apiResponse([], [], 'Unauthorized, Mangers only can remove dependency', false, 403);

        }
        $request->validate([
            'depends_on_task_id' => ['required', 'exists:tasks,id'],
        ]);

        try {
            DB::beginTransaction();
            $this->taskService->removeDependency($task, $request->depends_on_task_id);
            DB::commit();
            return $this->apiResponse(
                [],
                [],
                'Dependencies removed successfully',
                true,
                200
            );

        } catch (\Exception $exception) {
            DB::rollback();
            report($exception);
            $code = $exception->getCode();
            if (!is_int($code) || $code < 100 || $code > 599) {
                $code = 500;
            }
            return $this->apiResponse([], [], $exception->getMessage(), false, $code);

        }
    }
    public function assign($id, Request $request)
    {
        $task = $this->taskService->find($id);
        if (!$task) {
            return $this->apiResponse([], [], 'Task not found', false, 404);
        }
        if (auth()->user()->cannot('assign', $task)) {
            return $this->apiResponse([], [], 'Unauthorized, Mangers only can assign', false, 403);

        }
        $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $task->assigned_to = $request->assigned_to;
        $task->save();
        return $this->apiResponse(
            [],
            [],
            'Assigned successfully',
            true,
            200
        );

    }

}
