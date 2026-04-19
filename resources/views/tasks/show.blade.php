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
            {{-- ── Comments Section ──────────────────────────────────── --}}
            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    Comments
                    <span class="text-sm font-normal text-gray-400">
                        ({{ $task->comments->count() }})
                    </span>
                </h3>

                {{-- Flash message --}}
                @if (session('success'))
                    <x-shared.alert type="success" :message="session('success')" />
                @endif

                {{-- Existing comments --}}
                @forelse ($task->comments as $comment)
                    <div class="border border-gray-100 rounded-lg p-4" id="comment-{{ $comment->id }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                {{-- Author + timestamp --}}
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-sm font-semibold text-gray-800">
                                        {{ $comment->author->name }}
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        {{ $comment->created_at->diffForHumans() }}
                                        @if ($comment->updated_at->gt($comment->created_at))
                                            <em>(edited)</em>
                                        @endif
                                    </span>
                                </div>

                                {{-- Comment body --}}
                                <div class="comment-body-{{ $comment->id }}">
                                    <p class="text-sm text-gray-700">{{ $comment->body }}</p>
                                </div>

                                {{-- Inline edit form (hidden by default) --}}
                                @can('update-comment-' . $comment->id)
                                @endcan
                                @if ($comment->isAuthoredBy(Auth::user()))
                                    <div class="edit-form-{{ $comment->id }} hidden mt-2">
                                        <form method="POST"
                                            action="{{ route('projects.tasks.comments.update', [$project, $task, $comment]) }}">
                                            @csrf
                                            @method('PUT')
                                            <textarea
                                                name="body"
                                                rows="3"
                                                class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >{{ $comment->body }}</textarea>
                                            <div class="flex gap-2 mt-2">
                                                <button type="submit"
                                                        class="px-3 py-1 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                                    Save
                                                </button>
                                                <button type="button"
                                                        onclick="toggleEdit({{ $comment->id }})"
                                                        class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            {{-- Action buttons --}}
                            <div class="flex gap-2 shrink-0">
                                @if ($comment->isAuthoredBy(Auth::user()))
                                    <button onclick="toggleEdit({{ $comment->id }})"
                                            class="text-xs text-gray-400 hover:text-indigo-600">
                                        Edit
                                    </button>
                                @endif

                                @if ($comment->isAuthoredBy(Auth::user()) || in_array(Auth::user()->roleOn($project), ['owner', 'manager']))
                                    <form method="POST"
                                        action="{{ route('projects.tasks.comments.destroy', [$project, $task, $comment]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-gray-400 hover:text-red-600"
                                                onclick="return confirm('Delete this comment?')">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">
                        No comments yet. Be the first to comment.
                    </p>
                @endforelse

                {{-- Add comment form --}}
                @if (Auth::user()->isMemberOf($project))
                    <div class="border-t pt-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Add a Comment</h4>
                        <form method="POST"
                            action="{{ route('projects.tasks.comments.store', [$project, $task]) }}">
                            @csrf
                            <textarea
                                name="body"
                                rows="3"
                                placeholder="Write a comment..."
                                class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                            >{{ old('body') }}</textarea>
                            <x-input-error :messages="$errors->get('body')" class="mt-1" />
                            <div class="mt-2 flex justify-end">
                                <x-primary-button>Post Comment</x-primary-button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            {{-- Inline edit toggle script --}}
            <script>
            function toggleEdit(commentId) {
                const body = document.querySelector(`.comment-body-${commentId}`);
                const form = document.querySelector(`.edit-form-${commentId}`);
                body.classList.toggle('hidden');
                form.classList.toggle('hidden');
            }
            </script>
        </div>
    </div>
</x-app-layout>