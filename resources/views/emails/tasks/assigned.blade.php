<x-mail::message>
# You've been assigned a task

Hi **{{ $assignee->name }}**,

You have been assigned a new task in the project **{{ $project->name }}**.

<x-mail::panel>
**{{ $task->title }}**

@if($task->description)
{{ $task->description }}
@endif

**Priority:** {{ ucfirst($task->priority) }}
**Status:** {{ ucfirst(str_replace('_', ' ', $task->status)) }}
@if($task->due_date)
**Due Date:** {{ $task->due_date->format('M d, Y') }}
@endif
</x-mail::panel>

<x-mail::button :url="$url" color="primary">
View Task
</x-mail::button>

You are receiving this because you were assigned to this task.

Thanks,
{{ config('app.name') }}
</x-mail::message>