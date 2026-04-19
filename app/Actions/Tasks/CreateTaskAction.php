<?php

namespace App\Actions\Tasks;

use App\Mail\TaskAssignedMail;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CreateTaskAction
{
    public function execute(Project $project, array $data): Task
    {
        // Step 1: Create the task
        $task = $project->tasks()->create($data);

        // Step 2: Notify assignee if assigned and not self-assigned
        if ($task->assigned_to) {
            $assignee = User::find($task->assigned_to);

            if ($assignee && $assignee->id !== Auth::id()) {
                Mail::to($assignee->email)->send(
                    new TaskAssignedMail($task->load('project'), $assignee)
                );
            }
        }

        return $task;
    }
}
