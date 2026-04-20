<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Access Denied</h2>
    </x-slot>

    <div class="py-16">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 text-center">
            <div class="bg-white rounded-lg shadow p-12">

                <div class="text-6xl font-bold text-red-400 mb-4">403</div>

                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                    You don't have permission to do this.
                </h3>

                <p class="text-gray-500 mb-8">
                    {{ $exception->getMessage() ?: "You don't have access to this resource." }}
                </p>

                <div class="flex justify-center gap-4">
                    <a href="{{ route('projects.index') }}"
                       class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Back to Projects
                    </a>
                    <a href="{{ url()->previous() }}"
                       class="px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                        Go Back
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>