<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $project->name }}</h2>
                <p class="text-sm text-gray-500">Owner: {{ $project->owner->name }}</p>
            </div>
            <div class="flex gap-2">

                {{-- Only owner or manager sees Edit button --}}
                @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}"
                    class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Edit Project
                    </a>
                @endcan

                {{-- Only owner sees Delete button --}}
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
                        <a href="{{ route('projects.tasks.create', $project) }}"
                           class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            + Add Task
                        </a>
                    </div>

                    @forelse ($project->tasks as $task)
                        <div class="bg-white rounded-lg shadow p-4 flex items-start justify-between">
                            <div class="flex-1">
                                {{-- Priority dot --}}
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
                                </div>

                                <div class="mt-1 flex items-center gap-3 text-xs text-gray-500">
                                    <span class="capitalize">{{ str_replace('_', ' ', $task->status) }}</span>
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
                                <a href="{{ route('projects.tasks.edit', [$project, $task]) }}"
                                   class="text-xs text-gray-500 hover:text-indigo-600">Edit</a>
                                <form method="POST"
                                      action="{{ route('projects.tasks.destroy', [$project, $task]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700"
                                            onclick="return confirm('Delete task?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>

                    @empty
                        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                            No tasks yet.
                            <a href="{{ route('projects.tasks.create', $project) }}"
                               class="text-indigo-600 hover:underline">Add the first task.</a>
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

                                {{-- Only owner can remove non-owner members --}}
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

                    {{-- Invite Member Form — only visible to owner/manager --}}
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

                </div>
            </div>

        </div>
    </div>
</x-app-layout>