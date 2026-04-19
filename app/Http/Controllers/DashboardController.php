<?php

namespace App\Http\Controllers;

use App\Services\ProjectStatsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ProjectStatsService $statsService
    ) {}

    public function __invoke(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // ── Cached stats (no DB queries if cache is warm) ────────────
        $stats = $this->statsService->getUserStats($user);

        // ── Projects with progress ────────────────────────────────────
        $projects = $user->projects()
            ->with('owner')
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn($q) => $q->where('status', 'done'),
            ])
            ->latest()
            ->take(5)
            ->get();

        // ── My open tasks ─────────────────────────────────────────────
        $myTasks = $user->tasks()
            ->with('project')
            ->whereIn('status', ['todo', 'in_progress'])
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderBy('due_date')
            ->take(8)
            ->get();

        // ── Overdue tasks ─────────────────────────────────────────────
        $overdueTasks = $user->tasks()
            ->with('project')
            ->whereIn('status', ['todo', 'in_progress'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->orderBy('due_date')
            ->get();

        return view('dashboard', compact(
            'projects',
            'myTasks',
            'stats',
            'overdueTasks'
        ));
    }
}
