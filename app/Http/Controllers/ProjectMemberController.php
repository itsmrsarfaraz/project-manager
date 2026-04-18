<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    /**
     * Add a member to a project.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        // Coming in Step 5: Authorization
        return back();
    }

    /**
     * Remove a member from a project.
     */
    public function destroy(Project $project, User $user): RedirectResponse
    {
        // Coming in Step 5: Authorization
        return back();
    }
}
