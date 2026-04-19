<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectMemberRequest;
use App\Models\Project;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ProjectMemberController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService
    ) {}

    public function store(StoreProjectMemberRequest $request, Project $project): RedirectResponse
    {
        /** @var \App\Models\User $invitedBy */
        $invitedBy = Auth::user();

        $invitee = User::where('email', $request->validated()['email'])->first();

        // Service now handles email + activity log via Action
        $this->projectService->addMember(
            $project,
            $invitee,
            $request->validated()['role'],
            $invitedBy   // ← pass the inviter
        );

        return back()->with('success', "{$invitee->name} added to the project.");
    }

    public function destroy(Project $project, User $user): RedirectResponse
    {
        $this->authorize('removeMember', $project);

        if ($project->owner_id === $user->id) {
            return back()->withErrors(['general' => 'Cannot remove the project owner.']);
        }

        $this->projectService->removeMember($project, $user);

        ActivityLogger::log(
            projectId: $project->id,
            type: 'member_removed',
            description: Auth::user()->name . " removed {$user->name} from the project",
            metadata: ['user_id' => $user->id]
        );

        return back()->with('success', 'Member removed from project.');
    }
}
