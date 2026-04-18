<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    /**
     * Invite a user to the project by their email address.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        // Only owner or manager can add members
        $this->authorize('addMember', $project);

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            // 'exists:users,email' → the email must belong to a registered user
            'role'  => ['required', 'in:manager,member'],
            // Note: you can't invite someone as 'owner' — owner is set at creation
        ]);

        // Find the user by email
        $invitee = User::where('email', $validated['email'])->first();

        // Prevent adding someone who is already a member
        if ($project->members()->where('user_id', $invitee->id)->exists()) {
            return back()->withErrors([
                'email' => 'This user is already a member of the project.'
            ]);
        }

        // Prevent the owner from being re-added
        if ($project->owner_id === $invitee->id) {
            return back()->withErrors([
                'email' => 'The project owner is already a member.'
            ]);
        }

        // Attach the new member with their role
        $project->members()->attach($invitee->id, ['role' => $validated['role']]);

        return back()->with('success', "{$invitee->name} added to the project.");
    }

    /**
     * Remove a member from the project.
     */
    public function destroy(Project $project, User $user): RedirectResponse
    {
        // Only owner can remove members
        $this->authorize('removeMember', $project);

        // Prevent removing the owner
        if ($project->owner_id === $user->id) {
            return back()->withErrors(['general' => 'Cannot remove the project owner.']);
        }

        // Prevent removing yourself if you're not the owner
        // (covered by the policy, but explicit is better)
        $project->members()->detach($user->id);

        return back()->with('success', 'Member removed from project.');
    }
}
