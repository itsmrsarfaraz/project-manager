<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $project->name }}</h2>
                <p class="text-sm text-gray-500">Owner: {{ $project->owner->name }}</p>
            </div>
            <div class="flex gap-2">
                @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}"
                       class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Edit Project
                    </a>
                @endcan

                @can('delete', $project)
                    <form method="POST" action="{{ route('projects.destroy', $project) }}"
                          onsubmit="return confirm('Delete this project? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-md hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <x-shared.alert type="success" :message="session('success')" />
            @endif

            {{-- Project Info --}}
            <div class="bg-white rounded-lg shadow p-6">
                @if ($project->description)
                    <p class="text-gray-700">{{ $project->description }}</p>
                @endif
                <div class="mt-2">
                    @php
                        $badgeClass = match($project->status) {
                            'active'    => 'bg-green-100 text-green-800',
                            'archived'  => 'bg-gray-100 text-gray-600',
                            'completed' => 'bg-blue-100 text-blue-800',
                        };
                    @endphp
                    <span class="px-2 py-1 text-xs font-semibold rounded {{ $badgeClass }}">
                        {{ ucfirst($project->status) }}
                    </span>
                </div>
            </div>

            {{-- Two Column Layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Tasks Column (2/3 width) --}}
                <div class="lg:col-span-2 space-y-4">

                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">Tasks</h3>
                        @can('addTask', $project)
                            <a href="{{ route('projects.tasks.create', $project) }}"
                               class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                + Add Task
                            </a>
                        @endcan
                    </div>

                    {{-- Task search + filter bar --}}
                    <form method="GET" action="{{ route('projects.show', $project) }}"
                          class="flex flex-wrap gap-2">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search tasks..."
                               class="flex-1 min-w-0 border-gray-300 rounded-md shadow-sm text-sm" />

                        <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">All statuses</option>
                            @foreach(['todo' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done'] as $val => $lbl)
                                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </select>

                        <select name="priority" class="border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">All priorities</option>
                            @foreach(['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $val => $lbl)
                                <option value="{{ $val }}" {{ request('priority') === $val ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </select>

                        <x-primary-button type="submit">Filter</x-primary-button>

                        @if (request()->hasAny(['search', 'status', 'priority']))
                            <a href="{{ route('projects.show', $project) }}"
                               class="px-3 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Clear
                            </a>
                        @endif
                    </form>

                    {{-- Task list — uses $tasks from controller (supports search/filter) --}}
                    @forelse ($tasks as $task)
                        <div id="task-{{ $task->id }}"
                             class="bg-white rounded-lg shadow p-4 flex items-start justify-between">
                            <div class="flex-1">
                                @php
                                    $priorityColor = match($task->priority) {
                                        'high'   => 'bg-red-500',
                                        'medium' => 'bg-yellow-500',
                                        'low'    => 'bg-green-500',
                                    };
                                @endphp

                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $priorityColor }}"></span>
                                    <a href="{{ route('projects.tasks.show', [$project, $task]) }}"
                                       class="font-medium text-gray-900 hover:text-indigo-600">
                                        {{ $task->title }}
                                    </a>
                                    {{-- Label color dots --}}
                                    @if ($task->labels->isNotEmpty())
                                        <div class="flex gap-1">
                                            @foreach ($task->labels as $label)
                                                <span class="w-2 h-2 rounded-full inline-block"
                                                      style="background-color: {{ $label->color }}"
                                                      title="{{ $label->name }}">
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-1 flex items-center gap-3 text-xs text-gray-500">
                                    @php
                                        $statusClass = match($task->status) {
                                            'todo'        => 'bg-gray-100 text-gray-600',
                                            'in_progress' => 'bg-blue-100 text-blue-700',
                                            'done'        => 'bg-green-100 text-green-700',
                                        };
                                    @endphp
                                    <span class="task-status px-2 py-0.5 rounded text-xs {{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                    @if ($task->assignee)
                                        <span>→ {{ $task->assignee->name }}</span>
                                    @else
                                        <span class="italic">Unassigned</span>
                                    @endif
                                    @if ($task->due_date)
                                        <span>Due: {{ $task->due_date->format('M d, Y') }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Task Actions --}}
                            <div class="flex gap-2 ml-4">
                                @can('update', $task)
                                    <a href="{{ route('projects.tasks.edit', [$project, $task]) }}"
                                       class="text-xs text-gray-500 hover:text-indigo-600">Edit</a>
                                @endcan

                                @can('delete', $task)
                                    <form method="POST"
                                          action="{{ route('projects.tasks.destroy', [$project, $task]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-red-500 hover:text-red-700"
                                                onclick="return confirm('Delete task?')">
                                            Delete
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>

                    @empty
                        {{-- ✅ No $task variable here — this runs when collection is empty --}}
                        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                            @if (request()->hasAny(['search', 'status', 'priority']))
                                No tasks match your filters.
                            @else
                                No tasks yet.
                                <a href="{{ route('projects.tasks.create', $project) }}"
                                   class="text-indigo-600 hover:underline">Add the first task.</a>
                            @endif
                        </div>
                    @endforelse

                </div>

                {{-- Members Column (1/3 width) --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800">Members</h3>

                    <div class="bg-white rounded-lg shadow divide-y">
                        @foreach ($project->members as $member)
                            <div class="px-4 py-3 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                    <p class="text-xs text-gray-500 capitalize">{{ $member->pivot->role }}</p>
                                </div>
                                @can('removeMember', $project)
                                    @if ($member->pivot->role !== 'owner')
                                        <form method="POST"
                                              action="{{ route('projects.members.destroy', [$project, $member]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-xs text-red-500 hover:text-red-700">Remove</button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        @endforeach
                    </div>

                    {{-- Invite Member Form --}}
                    @can('addMember', $project)
                        <div class="bg-white rounded-lg shadow p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Invite Member</h4>
                            <form method="POST" action="{{ route('projects.members.store', $project) }}">
                                @csrf
                                <div class="mb-3">
                                    <x-input-label for="email" value="Email address" />
                                    <x-text-input id="email" name="email" type="email"
                                                  class="mt-1 block w-full text-sm"
                                                  placeholder="colleague@example.com"
                                                  :value="old('email')" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                                </div>
                                <div class="mb-3">
                                    <x-input-label for="role" value="Role" />
                                    <select id="role" name="role"
                                            class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm">
                                        <option value="member">Member</option>
                                        <option value="manager">Manager</option>
                                    </select>
                                </div>
                                <x-primary-button class="w-full justify-center">Invite</x-primary-button>
                            </form>
                        </div>
                    @endcan

                    {{-- Manage Labels --}}
                    @can('update', $project)
                        <div class="bg-white rounded-lg shadow p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Manage Labels</h4>
                            <div class="flex flex-wrap gap-2 mb-3">
                                @forelse ($project->labels as $label)
                                    <div class="flex items-center gap-1">
                                        <span class="px-2 py-0.5 rounded-full text-xs text-white font-medium"
                                              style="background-color: {{ $label->color }}">
                                            {{ $label->name }}
                                        </span>
                                        <form method="POST"
                                              action="{{ route('projects.labels.destroy', [$project, $label]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-gray-400 hover:text-red-500 text-xs"
                                                    title="Delete label">×</button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-400 italic">No labels yet.</p>
                                @endforelse
                            </div>
                            <form method="POST" action="{{ route('projects.labels.store', $project) }}"
                                  class="flex items-center gap-2">
                                @csrf
                                <input type="text" name="name" placeholder="Label name"
                                       value="{{ old('name') }}"
                                       class="flex-1 text-xs border-gray-300 rounded shadow-sm" />
                                <input type="color" name="color" value="#6366f1"
                                       class="h-8 w-10 rounded border-gray-300 cursor-pointer" />
                                <x-primary-button class="text-xs py-1">Add</x-primary-button>
                            </form>
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                    @endcan
                </div>
            </div>

            {{-- Activity Feed --}}
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Recent Activity</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse ($activities as $activity)
                        <div class="px-6 py-3 flex items-start gap-3">
                            <div class="mt-0.5 shrink-0">
                                @php
                                    $icon = match(true) {
                                        str_contains($activity->type, 'created') => '✅',
                                        str_contains($activity->type, 'updated') => '✏️',
                                        str_contains($activity->type, 'deleted') => '🗑️',
                                        str_contains($activity->type, 'member')  => '👤',
                                        default                                   => '📋',
                                    };
                                @endphp
                                <span class="text-base">{{ $icon }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-700">{{ $activity->description }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $activity->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-400 text-sm">
                            No activity yet.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    window.Echo.private('project.{{ $project->id }}')
        .listen('.task.status.updated', (data) => {
            const taskEl = document.getElementById('task-' + data.task_id);
            if (!taskEl) return;

            const statusBadge = taskEl.querySelector('.task-status');
            if (statusBadge) {
                statusBadge.className = statusBadge.className
                    .replace(/bg-\w+-\d+|text-\w+-\d+/g, '');

                const classes = {
                    'todo':        'bg-gray-100 text-gray-600',
                    'in_progress': 'bg-blue-100 text-blue-700',
                    'done':        'bg-green-100 text-green-700',
                };
                const label = data.status.replace('_', ' ');
                statusBadge.classList.add(
                    ...(classes[data.status] || 'bg-gray-100 text-gray-600').split(' ')
                );
                statusBadge.textContent = label.charAt(0).toUpperCase() + label.slice(1);
            }

            showToast(`${data.updated_by} updated "${data.title}" → ${data.status.replace('_', ' ')}`);
        });

    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-gray-900 text-white text-sm px-4 py-3 rounded-lg shadow-lg z-50 transition-opacity';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
</script>
@endpush