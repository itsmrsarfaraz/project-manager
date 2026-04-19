<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ProjectStatsService;
use Illuminate\Console\Command;

class WarmCache extends Command
{
    protected $signature   = 'cache:warm';
    protected $description = 'Pre-warm application caches for all users';

    public function handle(ProjectStatsService $statsService): int
    {
        $users = User::all();

        $this->withProgressBar($users, function (User $user) use ($statsService) {
            $statsService->getUserStats($user);
        });

        $this->newLine();
        $this->info("Cache warmed for {$users->count()} users.");

        return Command::SUCCESS;
    }
}
