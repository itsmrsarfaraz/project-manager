<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    /**
     * Create a new label for a project.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project); // managers/owners only

        $validated = $request->validate([
            'name'  => [
                'required',
                'string',
                'max:50',
                // Unique within this project
                \Illuminate\Validation\Rule::unique('labels')
                    ->where('project_id', $project->id),
            ],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            // ↑ validates hex color format like #6366f1
        ]);

        $project->labels()->create($validated);

        return back()->with('success', 'Label created.');
    }

    /**
     * Sync labels on a task (attach/detach in one operation).
     */
    public function sync(Request $request, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('addTask', $project); // any member can label tasks

        $validated = $request->validate([
            'labels'   => ['nullable', 'array'],
            'labels.*' => [
                'integer',
                // Label must belong to this project (prevent cross-project label injection)
                \Illuminate\Validation\Rule::exists('labels', 'id')
                    ->where('project_id', $project->id),
            ],
        ]);

        // sync() replaces ALL current labels with the new selection
        $task->labels()->sync($validated['labels'] ?? []);

        return back()->with('success', 'Labels updated.');
    }

    /**
     * Delete a label from the project.
     */
    public function destroy(Project $project, Label $label): RedirectResponse
    {
        $this->authorize('update', $project);

        abort_if($label->project_id !== $project->id, 404);

        $label->delete(); // cascade removes label_task pivot rows too

        return back()->with('success', 'Label deleted.');
    }
}
