<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Any project member can view a task.
     * We check project membership, not task-specific membership.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->isMemberOf($task->project);
    }

    /**
     * Any project member can create tasks.
     */
    public function create(User $user, Task $task): bool
    {
        return $user->isMemberOf($task->project);
    }

    /**
     * Any project member can edit tasks.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->isMemberOf($task->project);
    }

    /**
     * Owner, manager, OR the assigned user can delete a task.
     * The person assigned to a task has ownership of it.
     */
    public function delete(User $user, Task $task): bool
    {
        // Is the user assigned to this task?
        if ($task->assigned_to === $user->id) {
            return true;
        }

        // Is the user an owner or manager of the project?
        $role = $user->roleOn($task->project);

        return in_array($role, ['owner', 'manager']);
    }
}
