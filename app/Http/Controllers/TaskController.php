<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function create(Project $project): View
    {
        $this->authorize('addTask', $project);

        return view('tasks.create', [
            'project' => $project,
            'members' => $project->members,
        ]);
    }

    public function store(StoreTaskRequest $request, Project $project): RedirectResponse
    {
        $this->taskService->createTask($project, $request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Task created.');
    }

    public function show(Project $project, Task $task): View
    {
        $this->authorize('view', $task);

        $task->load(['comments.author', 'attachments.uploader', 'labels']);
        $project->load('labels');

        return view('tasks.show', compact('project', 'task'));
    }

    public function edit(Project $project, Task $task): View
    {
        $this->authorize('update', $task);

        return view('tasks.edit', [
            'project' => $project,
            'task'    => $task,
            'members' => $project->members,
        ]);
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task): RedirectResponse
    {
        $this->taskService->updateTask($task, $request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Task updated.');
    }

    public function destroy(Project $project, Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $this->taskService->deleteTask($task);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Task deleted.');
    }
}
