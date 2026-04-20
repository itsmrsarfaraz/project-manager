<?php

namespace App\Observers;

use App\Models\Project;
use App\Services\ActivityLogger;

class ProjectObserver
{
    public function created(Project $project): void
    {
        ActivityLogger::log(
            projectId: $project->id,
            type: 'project_created',
            description: $this->userName() . " created project \"{$project->name}\"",
            metadata: ['project_name' => $project->name]
        );
    }

    public function updated(Project $project): void
    {
        $changes = $project->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) return;

        $changeParts = [];

        if (isset($changes['status'])) {
            $changeParts[] = "status to \"{$changes['status']}\"";
        }
        if (isset($changes['name'])) {
            $changeParts[] = "name to \"{$changes['name']}\"";
        }

        $summary = empty($changeParts)
            ? 'updated project details'
            : 'changed ' . implode(', ', $changeParts);

        ActivityLogger::log(
            projectId: $project->id,
            type: 'project_updated',
            description: $this->userName() . " {$summary}",
            metadata: ['changes' => $changes]
        );
    }

    private function userName(): string
    {
        return \Illuminate\Support\Facades\Auth::user()?->name ?? 'System';
    }
}
