<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'MIG-HRM') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Additional styles -->
    @stack('styles')
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 flex">
        
        {{-- Alaga Sidebar Component --}}
        <x-alaga-sidebar :activeRoute="Route::currentRouteName()" />
        
        {{-- Main Content Area --}}
        <main class="flex-1 overflow-auto">
            {{-- Top Navigation Bar (optional) --}}
            @if(isset($showTopNav) && $showTopNav)
                <header class="bg-white shadow-sm border-b border-gray-200">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center py-4">
                            <h1 class="text-xl font-semibold text-gray-900">
                                @yield('page-title', 'Dashboard')
                            </h1>
                            
                            {{-- Breadcrumbs or additional nav items --}}
                            <nav aria-label="Breadcrumb" class="flex">
                                @yield('breadcrumbs')
                            </nav>
                        </div>
                    </div>
                </header>
            @endif
            
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 m-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 m-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            {{-- Page Content --}}
            <div class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @yield('content')
                    
                    {{-- Or use slots if you prefer --}}
                    {{ $slot ?? '' }}
                </div>
            </div>
        </main>
    </div>
    
    {{-- Scripts --}}
    @stack('scripts')
</body>
</html>

{{-- 
INTEGRATION INSTRUCTIONS:

1. Include the sidebar in your existing layout:
   Replace your current sidebar/navigation with:
   <x-alaga-sidebar :activeRoute="Route::currentRouteName()" />

2. Update your existing resources/views/layouts/app.blade.php:
   Add the Alaga sidebar component where you want the navigation to appear.

3. Example usage in a controller or view:
   The component automatically detects user roles and shows appropriate menus.

4. To use with custom active route:
   <x-alaga-sidebar :activeRoute="'admin.users.index'" />

5. Route names used in this example:
   Admin routes:
   - admin.dashboard
   - admin.users.index
   - admin.employees.time.view
   
   Employee routes:
   - dashboard
   - profile.edit
   - timesheet.index
   - attendance.index
   - daily-update.index
   
   Authentication routes:
   - logout
   - attendance.clockin
   - attendance.clockout

6. Customize the route names in the component to match your application's routes.

7. Add permission checks by uncommenting and customizing the @can directives in the component.

8. The component includes Tailwind CSS classes. If you're not using Tailwind, 
   replace the classes with equivalent Bootstrap or custom CSS.

--}}