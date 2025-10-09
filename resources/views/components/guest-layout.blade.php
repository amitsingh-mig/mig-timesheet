@props(['title' => config('app.name', 'MiG-HRM')])

<x-layouts.guest>
    {{ $slot }}
</x-layouts.guest>


