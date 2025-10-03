<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="MIG-HRM Employee Time Management System">
    <meta name="theme-color" content="{{ Auth::check() && Auth::user()->isAdmin() ? '#dc2626' : '#059669' }}">
    
    <title>{{ config('app.name', 'MIG-HRM') }} - @yield('title', 'Dashboard')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Additional styles --
</head>
<body class="font-sans antialiased bg-gray-50" 
      x-data="globalAppState()" 
      @keydown.escape="handleEscapeKey()"
      @toggle-sidebar.window="handleSidebarToggle()">
    
    <!-- Skip Links for Accessibility -->
    {{-- <div class="sr-only" aria-hidden="false">
        <a href="#main-content" class="skip-link focus:not-sr-only">Skip to main content</a>
        <a href="#navigation" class="skip-link focus:not-sr-only">Skip to navigation</a>
        <a href="#footer" class="skip-link focus:not-sr-only">Skip to footer</a>
    </div> --}}
    
    <!-- Live Region for Announcements -->
    <div id="announcements" 
         class="sr-only" 
         aria-live="polite" 
         aria-atomic="true"
         x-text="announcement"></div>
    
    <div class="min-h-screen flex" x-data="{ sidebarOpen: false }" @resize.window="handleResize()">
        
        <!-- Sidebar Navigation -->
        @auth
            <aside id="navigation" role="navigation" aria-label="Main navigation">
                <x-alaga-sidebar :activeRoute="Route::currentRouteName()" />
            </aside>
        @endauth
        
        <!-- Main Content Area -->
        <main 
            id="main-content" 
            class="flex-1 overflow-auto focus:outline-none" 
            tabindex="-1"
            role="main"
            aria-label="Main content"
        >
            
            <!-- Top Navigation Bar -->
            @auth
                <header class="bg-white shadow-sm border-b border-gray-200 lg:hidden" role="banner">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center py-4">
                            <!-- Mobile Menu Button -->
                            <x-mobile-menu-button />
                            
                            <div class="flex items-center">
                                <h1 class="text-lg font-semibold text-gray-900" id="page-title">
                                    @yield('page-title', config('app.name', 'MIG-HRM'))
                                </h1>
                            </div>
                            
                            <!-- User Menu (Mobile) -->
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600 sr-only sm:not-sr-only">
                                    {{ Auth::user()->name }}
                                </span>
                                <div class="h-8 w-8 bg-{{ Auth::user()->getPrimaryColor() }}-100 rounded-full flex items-center justify-center">
                                    <span class="text-{{ Auth::user()->getPrimaryColor() }}-600 font-medium text-sm" 
                                          aria-label="User avatar">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
            @endauth
            
            <!-- Breadcrumb Navigation -->
            @if(isset($breadcrumbs) && !empty($breadcrumbs))
                <nav aria-label="Breadcrumb" class="bg-gray-50 border-b border-gray-200">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <ol class="flex items-center space-x-2 py-3 text-sm">
                            @foreach($breadcrumbs as $index => $crumb)
                                <li class="flex items-center">
                                    @if($index > 0)
                                        <i class="bi bi-chevron-right text-gray-400 mx-2" aria-hidden="true"></i>
                                    @endif
                                    
                                    @if(isset($crumb['url']) && !$loop->last)
                                        <a href="{{ $crumb['url'] }}" 
                                           class="text-gray-600 hover:text-gray-900 transition-colors duration-200 focus:ring-enhanced">
                                            {{ $crumb['label'] }}
                                        </a>
                                    @else
                                        <span class="text-gray-900 font-medium" aria-current="page">
                                            {{ $crumb['label'] }}
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </nav>
            @endif
            
            <!-- Flash Messages with ARIA Live Region -->
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 m-4" 
                     role="alert" 
                     aria-labelledby="success-title"
                     x-data="{ show: true }"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100">
                    <div class="flex items-center justify-between">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-check-circle-fill h-5 w-5 text-green-400" aria-hidden="true"></i>
                            </div>
                            <div class="ml-3">
                                <h3 id="success-title" class="text-sm font-medium text-green-800">Success</h3>
                                <p class="text-sm text-green-700">{{ session('success') }}</p>
                            </div>
                        </div>
                        <button @click="show = false" 
                                class="text-green-400 hover:text-green-600 focus:ring-enhanced p-1"
                                aria-label="Dismiss success message">
                            <i class="bi bi-x text-lg" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 m-4" 
                     role="alert" 
                     aria-labelledby="error-title"
                     x-data="{ show: true }"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100">
                    <div class="flex items-center justify-between">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-exclamation-triangle-fill h-5 w-5 text-red-400" aria-hidden="true"></i>
                            </div>
                            <div class="ml-3">
                                <h3 id="error-title" class="text-sm font-medium text-red-800">Error</h3>
                                <p class="text-sm text-red-700">{{ session('error') }}</p>
                            </div>
                        </div>
                        <button @click="show = false" 
                                class="text-red-400 hover:text-red-600 focus:ring-enhanced p-1"
                                aria-label="Dismiss error message">
                            <i class="bi bi-x text-lg" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            @endif
            
            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-400 p-4 m-4" 
                     role="alert" 
                     aria-labelledby="validation-title"
                     x-data="{ show: true }"
                     x-show="show">
                    <div class="flex items-start justify-between">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-exclamation-triangle-fill h-5 w-5 text-red-400" aria-hidden="true"></i>
                            </div>
                            <div class="ml-3">
                                <h3 id="validation-title" class="text-sm font-medium text-red-800">
                                    There {{ $errors->count() === 1 ? 'was' : 'were' }} {{ $errors->count() }} 
                                    {{ Str::plural('error', $errors->count()) }} with your submission
                                </h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside" role="list">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button @click="show = false" 
                                class="text-red-400 hover:text-red-600 focus:ring-enhanced p-1"
                                aria-label="Dismiss validation errors">
                            <i class="bi bi-x text-lg" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            @endif
            
            <!-- Page Content -->
            <div class="flex-1">
                @yield('content')
            </div>
        </main>
    </div>
    
    <!-- Footer -->
    <footer id="footer" 
            class="bg-white border-t border-gray-200 py-4" 
            role="contentinfo"
            aria-label="Site footer">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center text-sm text-gray-500">
                <div>
                    <p>&copy; {{ date('Y') }} {{ config('app.name', 'MIG-HRM') }}. All rights reserved.</p>
                </div>
                <div class="flex space-x-4">
                    <a href="#" class="hover:text-gray-700 focus:ring-enhanced">Privacy Policy</a>
                    <a href="#" class="hover:text-gray-700 focus:ring-enhanced">Terms of Service</a>
                    <a href="#" class="hover:text-gray-700 focus:ring-enhanced">Support</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    @stack('scripts')
    
    <!-- Global Alpine.js State Management -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('globalAppState', () => ({
                announcement: '',
                sidebarOpen: false,
                
                init() {
                    // Check for reduced motion preference
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        document.documentElement.style.setProperty('--transition-duration', '0ms');
                    }
                    
                    // Set initial focus for screen readers
                    this.announcePageLoad();
                },
                
                announcePageLoad() {
                    const pageTitle = document.getElementById('page-title')?.textContent || 'Page';
                    this.announce(`${pageTitle} loaded`);
                },
                
                announce(message) {
                    this.announcement = message;
                    // Clear announcement after screen readers have processed it
                    setTimeout(() => {
                        this.announcement = '';
                    }, 1000);
                },
                
                handleEscapeKey() {
                    if (this.sidebarOpen) {
                        this.sidebarOpen = false;
                        this.announce('Navigation menu closed');
                    }
                },
                
                handleSidebarToggle() {
                    this.sidebarOpen = !this.sidebarOpen;
                    this.announce(this.sidebarOpen ? 'Navigation menu opened' : 'Navigation menu closed');
                },
                
                handleResize() {
                    // Auto-close sidebar on larger screens
                    if (window.innerWidth >= 1024 && this.sidebarOpen) {
                        this.sidebarOpen = false;
                    }
                }
            }));
        });
        
        // Global keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt + M: Focus main content
            if (e.altKey && e.key === 'm') {
                e.preventDefault();
                document.getElementById('main-content').focus();
            }
            
            // Alt + N: Focus navigation
            if (e.altKey && e.key === 'n') {
                e.preventDefault();
                document.getElementById('navigation')?.focus();
            }
        });
        
        // Announce page changes for SPA-like interactions
        window.announcePageChange = function(message) {
            window.dispatchEvent(new CustomEvent('announce', { detail: message }));
        };
        
        document.addEventListener('announce', function(e) {
            const announcements = document.getElementById('announcements');
            if (announcements) {
                announcements.textContent = e.detail;
                setTimeout(() => {
                    announcements.textContent = '';
                }, 1000);
            }
        });
    </script>
</body>
</html>