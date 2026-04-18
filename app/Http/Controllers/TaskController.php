<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Show form to create a task inside a specific project.
     * Note BOTH $project and $task are injected — nested route model binding.
     */
    public function create(Project $project): View
    {
        // Pass members so the form can show an "assign to" dropdown
        $members = $project->members;

        return view('tasks.create', compact('project', 'members'));
    }

    /**
     * Store a new task.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
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

    /**
     * Show a single task.
     */
    public function show(Project $project, Task $task): View
    {
        return view('tasks.show', compact('project', 'task'));
    }

    /**
     * Show edit form for a task.
     */
    public function edit(Project $project, Task $task): View
    {
        $members = $project->members;

        return view('tasks.edit', compact('project', 'task', 'members'));
    }

    /**
     * Update a task.
     */
    public function update(Request $request, Project $project, Task $task): RedirectResponse
    {
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

    /**
     * Delete a task.
     */
    public function destroy(Project $project, Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Task deleted.');
    }
}
