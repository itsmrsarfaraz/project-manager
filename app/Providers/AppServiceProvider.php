<?php

namespace App\Providers;

use App\Events\ProjectMemberAdded;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Listeners\InvalidateTaskStatsCache;
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
use App\Services\ProjectStatsService;
use App\Services\TaskService;
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
        $this->app->singleton(ProjectStatsService::class);

        // Telescope only in local — use strings to avoid class-not-found errors
        // when the package is not installed (e.g. on production)
        if ($this->app->environment('local')) {
            $this->app->register('Laravel\Telescope\TelescopeServiceProvider');
            $this->app->register('App\Providers\TelescopeServiceProvider');
        }
    }

    public function boot(): void
    {
        // ── Rate Limiters ──────────────────────────────────────────────
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip())
                ->response(fn() => response()->json([
                    'message' => 'Too many requests. Please slow down.',
                ], 429));
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
                ->response(fn() => response()->json([
                    'message' => 'Rate limit exceeded for heavy operations.',
                ], 429));
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

        Event::listen(TaskStatusChanged::class, InvalidateTaskStatsCache::class);
    }
}
