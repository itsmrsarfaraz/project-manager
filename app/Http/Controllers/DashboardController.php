<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // ── 1. Projects the user is a member of ───────────────────────
        // Load with counts so we don't do extra queries in the view
        $projects = $user->projects()
            ->with('owner')
            ->withCount([
                'tasks',                                    // total_tasks_count
                'tasks as completed_tasks_count' => fn($q) // completed_tasks_count
                => $q->where('status', 'done'),
            ])
            ->latest()
            ->take(5)  // show only the 5 most recent on dashboard
            ->get();

        // ── 2. Tasks assigned to the user across ALL projects ─────────
        $myTasks = $user->tasks()
            ->with('project')                   // eager load project name
            ->whereIn('status', ['todo', 'in_progress']) // only unfinished
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')") // high first
            ->orderBy('due_date')               // then by nearest due date
            ->take(8)
            ->get();

        // ── 3. Summary stats ──────────────────────────────────────────
        $stats = [
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

        // ── 4. Overdue tasks ──────────────────────────────────────────
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
