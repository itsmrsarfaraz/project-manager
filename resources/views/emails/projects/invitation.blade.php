<x-mail::message>
# You've been invited to a project

Hi **{{ $invitee->name }}**,

You have been invited to join the project **{{ $project->name }}** as a **{{ $role }}**.

<x-mail::panel>
**Project:** {{ $project->name }}

@if($project->description)
{{ $project->description }}
@endif

**Your Role:** {{ ucfirst($role) }}
</x-mail::panel>

<x-mail::button :url="$url" color="primary">
View Project
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>