<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">{{ $task->title }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('projects.tasks.edit', [$project, $task]) }}"
                   class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Edit Task
                </a>
                <form method="POST"
                      action="{{ route('projects.tasks.destroy', [$project, $task]) }}"
                      onsubmit="return confirm('Delete this task?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-md hover:bg-red-700">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Back link --}}
            <a href="{{ route('projects.show', $project) }}"
               class="text-sm text-indigo-600 hover:underline">
                ← Back to {{ $project->name }}
            </a>

            {{-- Task Details Card --}}
            <div class="bg-white rounded-lg shadow p-6 space-y-4">

                {{-- Status + Priority badges --}}
                <div class="flex items-center gap-3">
                    @php
                        $statusClass = match($task->status) {
                            'todo'        => 'bg-gray-100 text-gray-700',
                            'in_progress' => 'bg-yellow-100 text-yellow-800',
                            'done'        => 'bg-green-100 text-green-800',
                        };
                        $priorityClass = match($task->priority) {
                            'high'   => 'bg-red-100 text-red-800',
                            'medium' => 'bg-orange-100 text-orange-800',
                            'low'    => 'bg-blue-100 text-blue-800',
                        };
                    @endphp

                    <span class="px-2 py-1 text-xs font-semibold rounded {{ $statusClass }}">
                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                    <span class="px-2 py-1 text-xs font-semibold rounded {{ $priorityClass }}">
                        {{ ucfirst($task->priority) }} Priority
                    </span>
                </div>

                {{-- Description --}}
                @if ($task->description)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Description</h3>
                        <p class="mt-1 text-gray-700">{{ $task->description }}</p>
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">No description provided.</p>
                @endif

                {{-- Meta info --}}
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100 text-sm">
                    <div>
                        <span class="font-semibold text-gray-500">Assigned To</span>
                        <p class="mt-1 text-gray-800">
                            {{ $task->assignee?->name ?? 'Unassigned' }}
                        </p>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-500">Due Date</span>
                        <p class="mt-1 text-gray-800">
                            {{ $task->due_date?->format('M d, Y') ?? 'No due date' }}
                        </p>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-500">Project</span>
                        <p class="mt-1">
                            <a href="{{ route('projects.show', $project) }}"
                               class="text-indigo-600 hover:underline">
                                {{ $project->name }}
                            </a>
                        </p>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-500">Created</span>
                        <p class="mt-1 text-gray-800">
                            {{ $task->created_at->format('M d, Y') }}
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>