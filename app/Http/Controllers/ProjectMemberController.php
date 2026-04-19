<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectMemberRequest;
use App\Mail\ProjectInvitationMail;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ProjectMemberController extends Controller
{
    // StoreProjectMemberRequest handles EVERYTHING before this runs
    public function store(StoreProjectMemberRequest $request, Project $project): RedirectResponse
    {
        $invitee = User::where('email', $request->validated()['email'])->first();

        $project->members()->attach($invitee->id, [
            'role' => $request->validated()['role'],
        ]);

        // Send invitation email via queue
        Mail::to($invitee->email)->send(
            new ProjectInvitationMail($project, $invitee, $request->validated()['role'])
        );

        return back()->with('success', "{$invitee->name} added to the project.");
    }

    public function destroy(Project $project, User $user): RedirectResponse
    {
        $this->authorize('removeMember', $project);

        if ($project->owner_id === $user->id) {
            return back()->withErrors(['general' => 'Cannot remove the project owner.']);
        }

        $project->members()->detach($user->id);

        return back()->with('success', 'Member removed from project.');
    }
}
