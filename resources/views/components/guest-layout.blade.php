@props(['title' => config('app.name', 'Employee Timesheet')])

<x-layouts.guest>
    {{ $slot }}
</x-layouts.guest>


