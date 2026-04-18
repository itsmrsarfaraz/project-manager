<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * List all projects the authenticated user is a member of.
     * This includes projects they own AND were invited to.
     */
    public function index(): View
    {
        $projects = auth()->user()
            ->projects()                    // uses the BelongsToMany relationship
            ->with('owner')                 // eager load owner to avoid N+1 queries
            ->latest()                      // order by created_at DESC
            ->paginate(10);                 // 10 per page (never load all records)

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): View
    {
        return view('projects.create');
    }

    /**
     * Store a newly created project.
     * Validation lives in a Form Request (we'll create that next step).
     * For now, we validate inline to keep things simple.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status'      => ['required', 'in:active,archived,completed'],
        ]);

        // Create the project — owner_id is set from the auth user, NOT from
        // the request. Never trust the client to tell you who the owner is.
        $project = Project::create([
            ...$validated,
            'owner_id' => auth()->id(),
        ]);

        // Add the creator as a member with the 'owner' role in the pivot table
        $project->members()->attach(auth()->id(), ['role' => 'owner']);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display a single project with its tasks and members.
     * Laravel automatically resolves {project} to a Project model instance.
     * If the project doesn't exist, Laravel returns 404 automatically.
     */
    public function show(Project $project): View
    {
        // Eager load relationships we'll need in the view
        $project->load([
            'members',          // all members with pivot role
            'tasks.assignee',   // all tasks AND each task's assigned user
            'owner',
        ]);

        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing a project.
     */
    public function edit(Project $project): View
    {
        return view('projects.edit', compact('project'));
    }

    /**
     * Update the project.
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status'      => ['required', 'in:active,archived,completed'],
        ]);

        $project->update($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Delete a project.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted.');
    }
}
