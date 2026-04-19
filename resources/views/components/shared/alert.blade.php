@props([
    'type' => 'success',   // 'success' | 'error' | 'warning'
    'message'
])

@php
$classes = match($type) {
    'success' => 'bg-green-50 border-green-400 text-green-800',
    'error'   => 'bg-red-50 border-red-400 text-red-800',
    'warning' => 'bg-yellow-50 border-yellow-400 text-yellow-800',
    default   => 'bg-gray-50 border-gray-400 text-gray-800',
};
@endphp

<div class="border-l-4 p-4 mb-4 rounded {{ $classes }}" role="alert">
    {{ $message }}
</div>