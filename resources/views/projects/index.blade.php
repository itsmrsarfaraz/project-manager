<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">
                My Projects
            </h2>
            <a href="{{ route('projects.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                + New Project
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- Search + Filter Bar --}}
                    <form method="GET" action="{{ route('projects.index') }}" class="mb-6">
                        <div class="flex flex-col sm:flex-row gap-3">

                            {{-- Search input --}}
                            <div class="flex-1">
                                <input type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Search projects..."
                                    class="w-full border-gray-300 rounded-md shadow-sm text-sm
                                            focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>

                            {{-- Status filter --}}
                            <select name="status"
                                    class="border-gray-300 rounded-md shadow-sm text-sm
                                        focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All statuses</option>
                                @foreach (['active' => 'Active', 'archived' => 'Archived', 'completed' => 'Completed'] as $val => $label)
                                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            <x-primary-button type="submit">Search</x-primary-button>

                            {{-- Clear filters --}}
                            @if (request()->hasAny(['search', 'status']))
                                <a href="{{ route('projects.index') }}"
                                class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300
                                        rounded-md hover:bg-gray-50">
                                    Clear
                                </a>
                            @endif

                        </div>

                        {{-- Show active filter summary --}}
                        @if (request()->hasAny(['search', 'status']))
                            <p class="text-xs text-gray-500 mt-2">
                                Showing results for
                                @if(request('search'))
                                    "<strong>{{ request('search') }}</strong>"
                                @endif
                                @if(request('status'))
                                    · Status: <strong>{{ request('status') }}</strong>
                                @endif
                                · {{ $projects->total() }} project(s) found
                            </p>
                        @endif
                    </form>
                </div>
            </div>
    </div>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash message --}}
            @if (session('success'))
                <x-shared.alert type="success" :message="session('success')" />
            @endif

            {{-- Empty state --}}
            @if ($projects->isEmpty())
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <p class="text-gray-500 text-lg">You have no projects yet.</p>
                    <a href="{{ route('projects.create') }}"
                       class="mt-4 inline-block px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Create your first project
                    </a>
                </div>

            @else
                {{-- Projects Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($projects as $project)
                        <div class="bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                            <div class="p-6">

                                {{-- Status Badge --}}
                                @php
                                    $badgeClass = match($project->status) {
                                        'active'    => 'bg-green-100 text-green-800',
                                        'archived'  => 'bg-gray-100 text-gray-600',
                                        'completed' => 'bg-blue-100 text-blue-800',
                                    };
                                @endphp
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded {{ $badgeClass }}">
                                    {{ ucfirst($project->status) }}
                                </span>

                                {{-- Project Name --}}
                                <h3 class="mt-2 text-lg font-semibold text-gray-900">
                                    <a href="{{ route('projects.show', $project) }}"
                                       class="hover:text-indigo-600">
                                        {{ $project->name }}
                                    </a>
                                </h3>

                                {{-- Description --}}
                                @if ($project->description)
                                    <p class="mt-1 text-sm text-gray-500 line-clamp-2">
                                        {{ $project->description }}
                                    </p>
                                @endif

                                {{-- Footer: owner + member count --}}
                                <div class="mt-4 flex items-center justify-between text-xs text-gray-500">
                                    <span>Owner: {{ $project->owner->name }}</span>
                                    <span>
                                        {{ $project->members->count() }}
                                        {{ Str::plural('member', $project->members->count()) }}
                                    </span>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $projects->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>