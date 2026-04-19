<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
            ->with(['assignee', 'labels'])
            ->search($request->input('search'))
            ->filterStatus($request->input('status'))
            ->filterPriority($request->input('priority'))
            ->latest()
            ->paginate($request->input('per_page', 20));

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $task = $this->taskService->createTask($project, $request->validated());

        $task->load('assignee', 'labels');

        return response()->json(new TaskResource($task), 201);
    }

    public function show(Project $project, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task->load(['assignee', 'labels', 'comments.author']);

        return response()->json(new TaskResource($task));
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task): JsonResponse
    {
        $task = $this->taskService->updateTask($task, $request->validated());

        $task->load('assignee', 'labels');

        return response()->json(new TaskResource($task));
    }

    public function destroy(Project $project, Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->taskService->deleteTask($task);

        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'status' => ['required', 'in:todo,in_progress,done'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $task = $this->taskService->updateStatus($task, $validated['status'], $user);

        return response()->json(new TaskResource($task));
    }
}
