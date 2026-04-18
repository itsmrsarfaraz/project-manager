<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function create(Project $project): View
    {
        $this->authorize('addTask', $project);

        $members = $project->members;

        return view('tasks.create', compact('project', 'members'));
    }

    // StoreTaskRequest handles authorization + validation + business rules
    public function store(StoreTaskRequest $request, Project $project): RedirectResponse
    {
        $project->tasks()->create($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Task created.');
    }

    public function show(Project $project, Task $task): View
    {
        $this->authorize('view', $task);

        return view('tasks.show', compact('project', 'task'));
    }

    public function edit(Project $project, Task $task): View
    {
        $this->authorize('update', $task);

        $members = $project->members;

        return view('tasks.edit', compact('project', 'task', 'members'));
    }

    // UpdateTaskRequest handles authorization + validation + business rules
    public function update(UpdateTaskRequest $request, Project $project, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Task updated.');
    }

    public function destroy(Project $project, Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Task deleted.');
    }
}
