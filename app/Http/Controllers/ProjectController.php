<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $projects = $user->projects()
            ->with('owner')
            ->latest()
            ->paginate(10);

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('projects.create');
    }

    // StoreProjectRequest replaces Request + inline validate()
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        // No validate() call needed — already done by the Form Request
        // $request->validated() returns ONLY the fields that passed rules
        $project = Project::create([
            ...$request->validated(),
            'owner_id' => Auth::id(),
        ]);

        $project->members()->attach(Auth::id(), ['role' => 'owner']);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        $project->load(['members', 'tasks.assignee', 'tasks.labels', 'owner', 'labels']);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        return view('projects.edit', compact('project'));
    }

    // UpdateProjectRequest handles both authorization AND validation
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        // No authorize() call needed — UpdateProjectRequest::authorize() handles it
        // No validate() call needed — already validated
        $project->update($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted.');
    }
}
