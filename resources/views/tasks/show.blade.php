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

                {{-- Labels --}}
                @if ($task->labels->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach ($task->labels as $label)
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium text-white"
                                style="background-color: {{ $label->color }}">
                                {{ $label->name }}
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- Update labels form --}}
                <div class="pt-4 border-t border-gray-100">
                    <form method="POST"
                        action="{{ route('projects.tasks.labels.sync', [$project, $task]) }}">
                        @csrf
                        <p class="text-sm font-semibold text-gray-500 mb-2">Labels</p>
                        <div class="flex flex-wrap gap-2 mb-3">
                            @foreach ($project->labels as $label)
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox"
                                        name="labels[]"
                                        value="{{ $label->id }}"
                                        {{ $task->labels->contains($label->id) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600" />
                                    <span class="text-sm px-2 py-0.5 rounded-full text-white"
                                        style="background-color: {{ $label->color }}">
                                        {{ $label->name }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @if ($project->labels->isEmpty())
                            <p class="text-xs text-gray-400 italic">No labels defined for this project yet.</p>
                        @else
                            <x-primary-button type="submit">Update Labels</x-primary-button>
                        @endif
                    </form>
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

            {{-- ── Attachments ─────────────────────────────────── --}}
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">
                        Attachments
                        <span class="text-sm text-gray-400 font-normal ml-1">
                            ({{ $task->attachments->count() }})
                        </span>
                    </h3>
                </div>

                {{-- Existing attachments --}}
                @if ($task->attachments->isNotEmpty())
                    <div class="divide-y divide-gray-100">
                        @foreach ($task->attachments as $attachment)
                            <div class="px-6 py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    {{-- File type icon --}}
                                    <div class="text-2xl">
                                        @php
                                            $icon = match(true) {
                                                str_contains($attachment->mime_type, 'image') => '🖼️',
                                                str_contains($attachment->mime_type, 'pdf')   => '📄',
                                                str_contains($attachment->mime_type, 'zip')   => '🗜️',
                                                str_contains($attachment->mime_type, 'word')  => '📝',
                                                str_contains($attachment->mime_type, 'excel') => '📊',
                                                default                                        => '📎',
                                            };
                                        @endphp
                                        {{ $icon }}
                                    </div>
                                    <div>
                                        <a href="{{ route('projects.tasks.attachments.show', [$project, $task, $attachment]) }}"
                                        class="text-sm font-medium text-indigo-600 hover:underline">
                                            {{ $attachment->original_name }}
                                        </a>
                                        <p class="text-xs text-gray-400">
                                            {{ $attachment->formattedSize() }}
                                            · uploaded by {{ $attachment->uploader->name }}
                                            · {{ $attachment->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Delete --}}
                                @if ($attachment->user_id === auth()->id())
                                    <form method="POST"
                                        action="{{ route('projects.tasks.attachments.destroy', [$project, $task, $attachment]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-red-400 hover:text-red-600"
                                                onclick="return confirm('Delete this file?')">
                                            Remove
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Upload form --}}
                <div class="px-6 py-4 bg-gray-50 rounded-b-lg border-t border-gray-100">
                    <form method="POST"
                        action="{{ route('projects.tasks.attachments.store', [$project, $task]) }}"
                        enctype="multipart/form-data">
                        {{-- enctype="multipart/form-data" is REQUIRED for file uploads --}}
                        @csrf
                        <div class="flex items-center gap-3">
                            <input type="file"
                                name="file"
                                class="text-sm text-gray-600 file:mr-4 file:py-1.5 file:px-3
                                        file:rounded file:border-0 file:text-sm file:font-medium
                                        file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100
                                        flex-1" />
                            <x-primary-button type="submit">Upload</x-primary-button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            Max 10MB. Allowed: PDF, Word, Excel, images, ZIP, TXT.
                        </p>
                        <x-input-error :messages="$errors->get('file')" class="mt-1" />
                    </form>
                </div>
            </div>
            
            {{-- ── Comments Section ──────────────────────────────── --}}
            <div class="bg-white rounded-lg shadow">

                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">
                        Comments
                        <span class="text-sm text-gray-400 font-normal ml-1">
                            ({{ $task->comments->count() }})
                        </span>
                    </h3>
                </div>

                {{-- Existing comments --}}
                <div class="divide-y divide-gray-100">
                    @forelse ($task->comments as $comment)
                        <div class="px-6 py-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    {{-- Author + timestamp --}}
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-medium text-sm text-gray-900">
                                            {{ $comment->author->name }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ $comment->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    {{-- Comment body --}}
                                    <p class="text-gray-700 text-sm whitespace-pre-wrap">
                                        {{ $comment->body }}
                                    </p>
                                </div>

                                {{-- Delete button (author or project owner) --}}
                                @if ($comment->user_id === auth()->id())
                                    <form method="POST"
                                        action="{{ route('projects.tasks.comments.destroy', [$project, $task, $comment]) }}"
                                        class="ml-4">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-red-400 hover:text-red-600"
                                                onclick="return confirm('Delete this comment?')">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                    @empty
                        <div class="px-6 py-8 text-center text-gray-400 text-sm">
                            No comments yet. Be the first to comment.
                        </div>
                    @endforelse
                </div>

                {{-- Add comment form --}}
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-lg">
                    @if (session('success'))
                        <x-shared.alert type="success" :message="session('success')" class="mb-3" />
                    @endif

                    <form method="POST"
                        action="{{ route('projects.tasks.comments.store', [$project, $task]) }}">
                        @csrf
                        <div class="mb-3">
                            <textarea
                                name="body"
                                rows="3"
                                placeholder="Write a comment..."
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm
                                    focus:ring-indigo-500 focus:border-indigo-500"
                            >{{ old('body') }}</textarea>
                            <x-input-error :messages="$errors->get('body')" class="mt-1" />
                        </div>
                        <div class="flex justify-end">
                            <x-primary-button>Post Comment</x-primary-button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>