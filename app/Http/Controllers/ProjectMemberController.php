<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectMemberRequest;
use App\Mail\ProjectInvitationMail;
use App\Models\Project;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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

        ActivityLogger::log(
            projectId: $project->id,
            type: 'member_added',
            description: Auth::user()->name . " added {$invitee->name} as {$request->validated()['role']}",
            metadata: ['user_id' => $invitee->id, 'role' => $request->validated()['role']]
        );

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

        ActivityLogger::log(
            projectId: $project->id,
            type: 'member_removed',
            description: Auth::user()->name . " removed {$user->name} from the project",
            metadata: ['user_id' => $user->id]
        );

        return back()->with('success', 'Member removed from project.');
    }
}
