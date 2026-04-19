<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Page Not Found</h2>
    </x-slot>

    <div class="py-16">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 text-center">
            <div class="bg-white rounded-lg shadow p-12">

                <div class="text-6xl font-bold text-gray-300 mb-4">404</div>

                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                    Page not found.
                </h3>

                <p class="text-gray-500 mb-8">
                    The page you're looking for doesn't exist or has been moved.
                </p>

                <a href="{{ route('projects.index') }}"
                   class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Back to Projects
                </a>

            </div>
        </div>
    </div>
</x-app-layout>