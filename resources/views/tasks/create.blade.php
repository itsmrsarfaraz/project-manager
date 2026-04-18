<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Add Task to: <span class="text-indigo-600">{{ $project->name }}</span>
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">

                <form method="POST" action="{{ route('projects.tasks.store', $project) }}">
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="title" value="Task Title" />
                        <x-text-input id="title" name="title" type="text"
                            class="mt-1 block w-full" :value="old('title')"
                            placeholder="e.g. Design login page" autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="description" value="Description" />
                        <textarea id="description" name="description" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        >{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <x-input-label for="status" value="Status" />
                            <select id="status" name="status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                @foreach(['todo' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('status', 'todo') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="priority" value="Priority" />
                            <select id="priority" name="priority"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('priority', 'medium') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <x-input-label for="assigned_to" value="Assign To" />
                            <select id="assigned_to" name="assigned_to"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="">— Unassigned —</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}"
                                        {{ old('assigned_to') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('assigned_to')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="due_date" value="Due Date" />
                            <x-text-input id="due_date" name="due_date" type="date"
                                class="mt-1 block w-full" :value="old('due_date')" />
                            <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('projects.show', $project) }}"
                           class="text-sm text-gray-600 hover:text-gray-900">← Cancel</a>
                        <x-primary-button>Create Task</x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>