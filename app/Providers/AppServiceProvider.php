<?php

namespace App\Providers;

use App\Events\ProjectMemberAdded;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Listeners\LogMemberAddedActivity;
use App\Listeners\LogTaskCreatedActivity;
use App\Listeners\LogTaskStatusChangedActivity;
use App\Listeners\SendProjectInvitationEmail;
use App\Listeners\SendTaskAssignedNotification;
use App\Models\Project;
use App\Models\Task;
use App\Observers\ProjectObserver;
use App\Observers\TaskObserver;
use App\Services\ProjectService;
use App\Services\TaskService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProjectService::class);
        $this->app->singleton(TaskService::class);
    }

    public function boot(): void
    {
        // Observers
        Task::observe(TaskObserver::class);
        Project::observe(ProjectObserver::class);

        // Events → Listeners mapping
        // One event can have MULTIPLE listeners
        Event::listen(TaskCreated::class, SendTaskAssignedNotification::class);
        Event::listen(TaskCreated::class, LogTaskCreatedActivity::class);

        Event::listen(TaskStatusChanged::class, LogTaskStatusChangedActivity::class);

        Event::listen(ProjectMemberAdded::class, SendProjectInvitationEmail::class);
        Event::listen(ProjectMemberAdded::class, LogMemberAddedActivity::class);
    }
}
