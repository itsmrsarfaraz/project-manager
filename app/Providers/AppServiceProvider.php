<?php

namespace App\Providers;

// Services & Singletons
use App\Services\ProjectService;
use App\Services\ProjectStatsService;
use App\Services\TaskService;

// Models & Observers
use App\Models\Project;
use App\Models\Task;
use App\Observers\ProjectObserver;
use App\Observers\TaskObserver;

// Events
use App\Events\ProjectMemberAdded;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;

// Listeners
use App\Listeners\InvalidateTaskStatsCache;
use App\Listeners\LogMemberAddedActivity;
use App\Listeners\LogTaskCreatedActivity;
use App\Listeners\LogTaskStatusChangedActivity;
use App\Listeners\SendProjectInvitationEmail;
use App\Listeners\SendTaskAssignedNotification;

// Laravel
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProjectService::class);
        $this->app->singleton(TaskService::class);
        $this->app->singleton(App\Providers\ProjectStatsService::class);

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(\App\Providers\TelescopeServiceProvider::class);
        }
    }

    public function boot(): void
    {
        // ── Rate Limiters ──────────────────────────────────────────────
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Too many requests. Please slow down.',
                    ], 429);
                });
        });

        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinute(3)->by($request->input('email')),
            ];
        });

        RateLimiter::for('heavy', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Rate limit exceeded for heavy operations.',
                    ], 429);
                });
        });

        // ── Observers ─────────────────────────────────────────────────
        Task::observe(TaskObserver::class);
        Project::observe(ProjectObserver::class);

        // ── Events → Listeners ────────────────────────────────────────
        Event::listen(TaskCreated::class, SendTaskAssignedNotification::class);
        Event::listen(TaskCreated::class, LogTaskCreatedActivity::class);

        Event::listen(TaskStatusChanged::class, LogTaskStatusChangedActivity::class);
        Event::listen(TaskStatusChanged::class, InvalidateTaskStatsCache::class);

        Event::listen(ProjectMemberAdded::class, SendProjectInvitationEmail::class);
        Event::listen(ProjectMemberAdded::class, LogMemberAddedActivity::class);
    }
}
