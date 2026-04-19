<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Create Project</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">

                <form method="POST" action="{{ route('projects.store') }}">
                    @csrf
                    {{-- @csrf generates a hidden token field.
                         Without it, Laravel rejects the POST with a 419 error.
                         This prevents Cross-Site Request Forgery attacks. --}}

                    {{-- Name --}}
                    <div class="mb-4">
                        <x-input-label for="name" :value="__('Project Name')" />
                        <x-text-input
                            id="name"
                            name="name"
                            type="text"
                            class="mt-1 block w-full"
                            :value="old('name')"
                            placeholder="e.g. Website Redesign"
                            autofocus
                        />
                        {{-- old('name') repopulates the field after validation failure --}}
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    {{-- Description --}}
                    <div class="mb-4">
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            placeholder="What is this project about?"
                        >{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    {{-- Status --}}
                    <div class="mb-6">
                        <x-input-label for="status" :value="__('Status')" />
                        <select
                            id="status"
                            name="status"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        >
                            @foreach (['active' => 'Active', 'archived' => 'Archived', 'completed' => 'Completed'] as $value => $label)
                                <option value="{{ $value }}" {{ old('status', 'active') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between">
                        <a href="{{ route('projects.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-900">
                            ← Cancel
                        </a>
                        <x-primary-button>
                            Create Project
                        </x-primary-button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>