<?php

namespace App\Actions\Tasks;

use App\Events\TaskCreated;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CreateTaskAction
{
    public function execute(Project $project, array $data): Task
    {
        $task = $project->tasks()->create($data);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Fire event — listeners handle email + activity log independently
        TaskCreated::dispatch($task->load('assignee'), $user);

        return $task;
    }
}
