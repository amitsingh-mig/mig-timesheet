@props(['title' => config('app.name', 'MIG-TimeSheet')])

<x-layouts.guest>
    {{ $slot }}
</x-layouts.guest>


