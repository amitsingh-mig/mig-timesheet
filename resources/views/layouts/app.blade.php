<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-token" content="{{ config('app.api_token') }}">
    @stack('meta')
    <title>{{ config('app.name', 'MIG-HRM') }}</title>
    <link rel="shortcut icon" href="{{ asset('img/favicon.png') }}" type="image/x-icon">   <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

</head>
<body>
    <!-- Fixed Mobile Navbar -->
    <nav class="navbar navbar-fixed-mobile d-md-none">
        <div class="container-fluid">
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                <i class="fas fa-bars text-white fs-4"></i>
            </button>
            <span class="navbar-brand ms-1 text-white fw-bold">MIG-HRM</span>
        </div>
    </nav>

    <!-- Fixed Sidebar (desktop) + Offcanvas (mobile) -->
    <div class="d-flex">
        <!-- Sidebar with Fixed Scrolling -->
        <div class="d-none d-md-flex flex-column flex-shrink-0 text-white position-fixed sidebar" id="sidebar">
            <!-- Toggle Button -->
            {{-- <button type="button" class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar" title="Toggle sidebar">
                <i class="fas fa-chevron-left" id="toggle-icon"></i>
            </button> --}}

            <!-- Fixed Header Section -->
            <div class="sidebar-header flex-shrink-0">
                <div class="sidebar-brand text-white text-decoration-none">
                    <div class="brand-text">MIG-HRM</div>
                </div>
            </div>

            <!-- Scrollable Content Section -->
            <div class="sidebar-content flex-grow-1 d-flex flex-column">
                <div class="sidebar-nav-container flex-grow-1 px-3">
            <ul class="nav nav-pills flex-column">
                {{-- Include Admin Navigation Component --}}
                <x-admin-navigation :mobile="false" />
                {{-- Include User Navigation Component --}}
                <x-user-navigation :mobile="false" />
            </ul>
                </div>
                
            </div>

            <!-- Fixed Footer Section -->
            <div class="sidebar-footer flex-shrink-0">
                <div class="user-info">
                    @if(Auth::check() && !request()->is('profile/*'))
                    <a href="{{ route('profile') }}" class="user-profile-link">
                        <div class="d-flex align-items-center mb-3">
                            <div class="user-avatar me-3">
                                <img src="{{ Auth::user()->getProfilePhotoUrl() }}" 
                                     alt="{{ Auth::user()->name }}" 
                                     class="profile-avatar-img"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="avatar-fallback" style="display: none;">
                                    {{ substr(Auth::user()->name, 0, 2) }}
                                </div>
                            </div>
                            <div class="user-details">
                                <div class="user-name">{{ Auth::user()->name }}</div>
                                <div class="user-email">{{ Auth::user()->email }}</div>
                            </div>
                        </div>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-light w-100 btn-logout">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </button>
                    </form>
                    @elseif(Auth::check() && request()->is('profile/*'))
                    <!-- Simplified logout button when viewing profiles -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-light w-100 btn-logout">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Offcanvas Sidebar (mobile) with Scrolling -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
            <div class="offcanvas-header border-bottom border-white border-opacity-25">
                <h5 class="offcanvas-title text-white fw-bold" id="sidebarOffcanvasLabel">MIG-HRM</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column p-0">
                <!-- Scrollable Navigation Area -->
                <div class="mobile-nav-container flex-grow-1 p-3">
                    <ul class="nav nav-pills flex-column">
                        {{-- Include Admin Navigation Component for Mobile --}}
                        <x-admin-navigation :mobile="true" />
                        {{-- Include User Navigation Component for Mobile --}}
                        <x-user-navigation :mobile="true" />
                    </ul>
                </div>
                
                    
                    @if(Auth::check() && !request()->is('profile/*'))
                    <a href="{{ route('profile') }}" class="user-profile-link">
                        <div class="d-flex align-items-center mb-3">
                            <div class="user-avatar me-3">
                                <img src="{{ Auth::user()->getProfilePhotoUrl() }}" 
                                     alt="{{ Auth::user()->name }}" 
                                     class="profile-avatar-img"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="avatar-fallback" style="display: none;">
                                    {{ substr(Auth::user()->name, 0, 2) }}
                                </div>
                            </div>
                            <div class="user-details">
                                <div class="user-name">{{ Auth::user()->name }}</div>
                                <div class="user-email">{{ Auth::user()->email }}</div>
                            </div>
                        </div>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-light w-100 btn-logout">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </button>
                    </form>
                    @elseif(Auth::check() && request()->is('profile/*'))
                    <!-- Simplified logout button when viewing profiles -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-light w-100 btn-logout">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content flex-grow-1" id="main-content">
            <div class="container-fluid py-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/script.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
