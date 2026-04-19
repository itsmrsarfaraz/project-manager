<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ProjectStatsService
{
    private const TTL = 300; // 5 minutes

    public function getUserStats(User $user): array
    {
        $cacheKey = "user_stats:{$user->id}";

        return Cache::remember($cacheKey, self::TTL, function () use ($user) {
            return [
                'total_projects'  => $user->projects()->count(),
                'active_projects' => $user->projects()
                    ->where('status', 'active')
                    ->count(),
                'my_open_tasks'   => $user->tasks()
                    ->whereIn('status', ['todo', 'in_progress'])
                    ->count(),
                'my_done_tasks'   => $user->tasks()
                    ->where('status', 'done')
                    ->count(),
            ];
        });
    }

    public function getProjectTaskStats(Project $project): array
    {
        $cacheKey = "project_task_stats:{$project->id}";

        return Cache::remember($cacheKey, self::TTL, function () use ($project) {
            $total     = $project->tasks()->count();
            $completed = $project->tasks()->where('status', 'done')->count();

            return [
                'total'      => $total,
                'completed'  => $completed,
                'percentage' => $total > 0
                    ? (int) round(($completed / $total) * 100)
                    : 0,
            ];
        });
    }

    public function invalidateUserStats(int $userId): void
    {
        Cache::forget("user_stats:{$userId}");
    }

    public function invalidateProjectStats(int $projectId): void
    {
        Cache::forget("project_task_stats:{$projectId}");
    }
}
