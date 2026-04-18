<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function create(Project $project): View
    {
        // Authorize against the PROJECT (any member can create tasks)
        $this->authorize('addTask', $project); // reuse project's update policy
        // Alternative: create a custom 'addTask' policy method

        $members = $project->members;

        return view('tasks.create', compact('project', 'members'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('addTask', $project);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status'      => ['required', 'in:todo,in_progress,done'],
            'priority'    => ['required', 'in:low,medium,high'],
            'due_date'    => ['nullable', 'date', 'after_or_equal:today'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $project->tasks()->create($validated);

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

    public function update(Request $request, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status'      => ['required', 'in:todo,in_progress,done'],
            'priority'    => ['required', 'in:low,medium,high'],
            'due_date'    => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $task->update($validated);

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
