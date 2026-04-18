<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">
                Welcome back, {{ Auth::user()->name }}! 👋
            </h2>
            <a href="{{ route('projects.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                + New Project
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- ── Stats Row ──────────────────────────────────────── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                <div class="bg-white rounded-lg shadow p-5 text-center">
                    <p class="text-3xl font-bold text-indigo-600">
                        {{ $stats['total_projects'] }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">Total Projects</p>
                </div>

                <div class="bg-white rounded-lg shadow p-5 text-center">
                    <p class="text-3xl font-bold text-green-600">
                        {{ $stats['active_projects'] }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">Active Projects</p>
                </div>

                <div class="bg-white rounded-lg shadow p-5 text-center">
                    <p class="text-3xl font-bold text-yellow-600">
                        {{ $stats['my_open_tasks'] }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">Open Tasks</p>
                </div>

                <div class="bg-white rounded-lg shadow p-5 text-center">
                    <p class="text-3xl font-bold text-blue-600">
                        {{ $stats['my_done_tasks'] }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">Completed Tasks</p>
                </div>

            </div>

            {{-- ── Overdue Alert ────────────────────────────────────── --}}
            @if ($overdueTasks->isNotEmpty())
                <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-red-600 font-semibold text-sm">
                            ⚠️ {{ $overdueTasks->count() }} Overdue
                            {{ Str::plural('Task', $overdueTasks->count()) }}
                        </span>
                    </div>
                    <div class="space-y-2">
                        @foreach ($overdueTasks as $task)
                            <div class="flex items-center justify-between text-sm">
                                <div>
                                    <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}"
                                       class="font-medium text-red-800 hover:underline">
                                        {{ $task->title }}
                                    </a>
                                    <span class="text-red-500 ml-2 text-xs">
                                        in {{ $task->project->name }}
                                    </span>
                                </div>
                                <span class="text-red-600 text-xs font-medium">
                                    Due {{ $task->due_date->format('M d') }}
                                    ({{ $task->due_date->diffForHumans() }})
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ── Two Column Layout ───────────────────────────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                {{-- My Projects ──────────────────────────────────── --}}
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">My Projects</h3>
                        <a href="{{ route('projects.index') }}"
                           class="text-sm text-indigo-600 hover:underline">View all →</a>
                    </div>

                    @forelse ($projects as $project)
                        <div class="bg-white rounded-lg shadow mb-3 p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <a href="{{ route('projects.show', $project) }}"
                                       class="font-semibold text-gray-900 hover:text-indigo-600">
                                        {{ $project->name }}
                                    </a>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $project->members_count ?? '' }}
                                        Owner: {{ $project->owner->name }}
                                    </p>
                                </div>
                                {{-- Status badge --}}
                                @php
                                    $badgeClass = match($project->status) {
                                        'active'    => 'bg-green-100 text-green-700',
                                        'archived'  => 'bg-gray-100 text-gray-600',
                                        'completed' => 'bg-blue-100 text-blue-700',
                                    };
                                @endphp
                                <span class="text-xs px-2 py-0.5 rounded font-medium {{ $badgeClass }}">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </div>

                            {{-- Task progress bar --}}
                            @if ($project->tasks_count > 0)
                                @php
                                    $pct = round(
                                        ($project->completed_tasks_count / $project->tasks_count) * 100
                                    );
                                @endphp
                                <div class="mt-3">
                                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                                        <span>Progress</span>
                                        <span>
                                            {{ $project->completed_tasks_count }}
                                            / {{ $project->tasks_count }} tasks
                                            ({{ $pct }}%)
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-indigo-500 h-1.5 rounded-full transition-all"
                                             style="width: {{ $pct }}%">
                                        </div>
                                    </div>
                                </div>
                            @else
                                <p class="mt-2 text-xs text-gray-400 italic">No tasks yet.</p>
                            @endif
                        </div>

                    @empty
                        <div class="bg-white rounded-lg shadow p-8 text-center">
                            <p class="text-gray-500 text-sm">No projects yet.</p>
                            <a href="{{ route('projects.create') }}"
                               class="mt-2 inline-block text-sm text-indigo-600 hover:underline">
                                Create your first project
                            </a>
                        </div>
                    @endforelse
                </div>

                {{-- My Tasks ─────────────────────────────────────── --}}
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">My Tasks</h3>
                        <span class="text-xs text-gray-400">Sorted by priority + due date</span>
                    </div>

                    @forelse ($myTasks as $task)
                        <div class="bg-white rounded-lg shadow mb-3 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}"
                                       class="font-medium text-gray-900 hover:text-indigo-600 block truncate">
                                        {{ $task->title }}
                                    </a>
                                    <a href="{{ route('projects.show', $task->project) }}"
                                       class="text-xs text-indigo-500 hover:underline mt-0.5 block">
                                        {{ $task->project->name }}
                                    </a>
                                </div>

                                {{-- Priority + status badges --}}
                                <div class="flex flex-col items-end gap-1 shrink-0">
                                    @php
                                        $priorityClass = match($task->priority) {
                                            'high'   => 'bg-red-100 text-red-700',
                                            'medium' => 'bg-yellow-100 text-yellow-700',
                                            'low'    => 'bg-green-100 text-green-700',
                                        };
                                        $statusClass = match($task->status) {
                                            'todo'        => 'bg-gray-100 text-gray-600',
                                            'in_progress' => 'bg-blue-100 text-blue-700',
                                            'done'        => 'bg-green-100 text-green-700',
                                        };
                                    @endphp
                                    <span class="text-xs px-2 py-0.5 rounded font-medium {{ $priorityClass }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                    <span class="text-xs px-2 py-0.5 rounded {{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Due date --}}
                            @if ($task->due_date)
                                @php
                                    $dueDateClass = $task->due_date->isPast()
                                        ? 'text-red-600 font-semibold'
                                        : 'text-gray-400';
                                @endphp
                                <p class="text-xs mt-2 {{ $dueDateClass }}">
                                    Due: {{ $task->due_date->format('M d, Y') }}
                                    ({{ $task->due_date->diffForHumans() }})
                                </p>
                            @endif
                        </div>

                    @empty
                        <div class="bg-white rounded-lg shadow p-8 text-center">
                            <p class="text-gray-500 text-sm">🎉 No open tasks assigned to you.</p>
                        </div>
                    @endforelse
                </div>

            </div>
            {{-- end two column --}}

        </div>
    </div>
</x-app-layout>