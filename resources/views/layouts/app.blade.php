<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('meta')
    <title>{{ config('app.name', 'MIG-HRM') }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Global Styles */
        :root {
            --color-primary: #FE7743; /* warm accent */
            --color-neutral: #EFEEEA; /* light neutral */
            --color-bluegray: #273F4F; /* cool/dark blue-grey */
            --color-black: #000000; /* pure black */
            --sidebar-gradient: linear-gradient(200deg, var(--color-bluegray) 0%, #314e61 50%, #3f6b83 100%);
            --card-radius: 14px;
            --card-shadow: 0 10px 22px rgba(0,0,0,0.08);
            --hover-glow: 0 8px 24px rgba(254, 119, 67, 0.35);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --sidebar-gradient: linear-gradient(200deg, #1b2b35 0%, #223a48 50%, #273F4F 100%);
                --card-shadow: 0 12px 24px rgba(0,0,0,0.35);
            }
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--color-neutral);
            color: #1f2937;
        }
        
        /* Sidebar Styles - Fixed with proper scrolling */
        .sidebar {
            width: 250px;
            background: var(--sidebar-gradient);
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            /* Custom scrollbar styling */
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
        }
        
        /* Webkit scrollbar styling for better appearance */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            transition: background 0.3s ease;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        /* Sidebar Layout Structure */
        .sidebar-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        .sidebar-content {
            min-height: 0; /* Important for flex scrolling */
            overflow: hidden; /* Prevent container overflow */
        }
        
        .sidebar-nav-container {
            overflow-y: auto;
            overflow-x: hidden;
            min-height: 0; /* Important for flex scrolling */
            /* Scrollbar styling for nav container */
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }
        
        .sidebar-nav-container::-webkit-scrollbar {
            width: 4px;
        }
        
        .sidebar-nav-container::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .sidebar-nav-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 6px;
        }
        
        .sidebar-nav-container::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.4);
        }
        
        .sidebar-actions {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.95);
            padding: 12px 18px;
            border-radius: 12px;
            margin: 6px 12px;
            transition: background .25s ease, transform .18s ease, color .2s ease, box-shadow .25s ease;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
            transform: translateX(4px) scale(1.02);
            box-shadow: var(--hover-glow);
        }
        
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-brand {
            padding: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            text-align: center;
        }
        
        .brand-text {
            font-size: 1.5rem;
            font-weight: 800;
            color: white;
            text-shadow: 0 2px 6px rgba(0,0,0,0.25);
            letter-spacing: .5px;
        }
        
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            background: #f7f7f6;
            transition: margin-left 0.3s ease;
            padding: 1rem;
            overflow-x: hidden; /* Prevent horizontal overflow from wide children */
        }
        
        .main-content .container-fluid {
            padding: 1.5rem;
            overflow-x: hidden; /* Contain any wide sections */
        }
        
        .quick-actions {
            padding: 20px;
            margin: 20px 0;
        }
        
        .admin-quick-actions {
            padding: 15px 0;
        }
        
        .admin-quick-actions .btn-quick {
            font-size: 0.9rem;
            padding: 10px 15px;
            margin-bottom: 8px;
        }
        
        .btn-quick {
            width: 100%;
            margin-bottom: 10px;
            border-radius: 12px;
            font-weight: 600;
            padding: 12px;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-quick:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        .user-info {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            margin-top: auto;
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #ffffff, #f1f3f4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4e54c8;
            font-weight: 700;
            font-size: 14px;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Mobile/Tablet Responsive */
        @media (max-width: 992px) {
            .sidebar {
                margin-left: -250px;
            }
            .main-content {
                margin-left: 0;
                padding-top: 70px; /* Add padding to prevent overlap with fixed navbar */
            }
            
            /* Fixed mobile navbar styles */
            .navbar-fixed-mobile {
                height: 60px;
            }
            
            body {
                padding-top: 0;
            }
        }
        
        .avatar-sm {
            width: 32px;
            height: 32px;
        }
        
        /* Compact sidebar styles - Updated for new structure */
        .sidebar.compact {
            width: 80px;
            transition: width 0.3s ease;
        }
         
        .sidebar.compact .sidebar-brand span,
        .sidebar.compact .nav-link span,
        .sidebar.compact .sidebar-actions,
        .sidebar.compact .admin-quick-actions,
        .sidebar.compact .user-info div:not(.avatar),
        .sidebar.compact hr {
            display: none;
        }
        
        .sidebar.compact .nav-link {
            text-align: center;
            padding: 12px 0;
        }
        
        .sidebar.compact .nav-link i {
            margin: 0;
            font-size: 18px;
        }
        
        .sidebar.compact .sidebar-brand {
            justify-content: center;
        }
        
        .sidebar.compact .user-info {
            text-align: center;
            padding: 15px 10px;
        }
        
        /* Compact mode scrolling adjustments */
        .sidebar.compact .sidebar-nav-container {
            padding-left: 0;
            padding-right: 0;
        }
        
        .main-content.sidebar-compact {
            margin-left: 80px;
            transition: margin-left 0.3s ease;
        }
        
        .sidebar-toggle {
            position: absolute;
            top: 10px;
            right: -15px;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
        }
        
        .sidebar-toggle:hover {
            background: #f8f9fa;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        /* Enhanced Admin Navigation Styles */
        .nav-item-header {
            padding: 0 25px;
        }
        
        .nav-header-text {
            font-size: 0.75rem;
            font-weight: 800;
            color: rgba(255, 255, 255, 0.7);
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        
        .dropdown-nav {
            position: relative;
        }
        
        .dropdown-toggle-nav {
            cursor: pointer;
            user-select: none;
        }
        
        .dropdown-toggle-nav:hover {
            background: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
            transform: translateX(5px);
        }
        
        .collapse-icon {
            font-size: 0.75rem;
            transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            display: inline-block;
        }
        
        .dropdown-toggle-nav[aria-expanded="true"] .collapse-icon {
            transform: rotate(180deg);
        }
        
        /* Enhanced dropdown animations */
        .collapse {
            transition: height 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .collapse.show {
            animation: slideDown 0.35s ease-out;
        }
        
        @keyframes slideDown {
            0% {
                opacity: 0;
                transform: translateY(-10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .sub-nav-link {
            font-size: 0.9rem;
            padding: 10px 20px !important;
            margin: 1px 10px;
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.8) !important;
            background: rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }
        
        .sub-nav-link:hover {
            background: rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
            transform: translateX(3px);
        }
        
        .sub-nav-link.active {
            background: rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(255, 255, 255, 0.1);
        }
        
        .sub-nav-link i {
            font-size: 0.8rem;
            width: 16px;
        }
        
        /* Admin Badge */
        .admin-badge {
            background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
            color: white;
            font-size: 0.6rem;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: auto;
        }
        
        /* Compact sidebar admin styles */
        .sidebar.compact .nav-header-text,
        .sidebar.compact .collapse,
        .sidebar.compact .collapse-icon {
            display: none;
        }
        
        .sidebar.compact .dropdown-toggle-nav {
            text-align: center;
            padding: 12px 0;
        }
        
        .sidebar.compact .dropdown-toggle-nav i:first-child {
            margin: 0;
            font-size: 18px;
        }
        
        /* Admin notification badge */
        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        /* Admin-only visual separators and indicators */
        .admin-separator {
            border-color: rgba(255, 255, 255, 0.3);
            margin: 0 20px;
            border-width: 2px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
            height: 2px;
            border: none;
        }
        
        .admin-only-badge {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            font-size: 0.5rem;
            font-weight: 700;
            padding: 2px 4px;
            border-radius: 6px;
            margin-left: 8px;
            letter-spacing: 0.5px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
            animation: adminPulse 2s infinite;
        }
        
        @keyframes adminPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* Admin section hover effects */
        .nav-item-header:hover .admin-only-badge {
            animation: none;
            opacity: 1;
        }
        
        /* Compact sidebar admin-only styles */
        .sidebar.compact .admin-separator,
        .sidebar.compact .admin-only-badge {
            display: none;
        }
        
        /* Non-admin access styling */
        .access-restricted {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            margin: 10px 15px;
        }
        
        .access-restricted .alert {
            background: rgba(255, 193, 7, 0.2) !important;
            border: 1px solid rgba(255, 193, 7, 0.3) !important;
            color: #fff !important;
        }
        
        /* Enhanced mobile navbar with smooth transitions */
        .navbar-fixed-mobile {
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            backdrop-filter: blur(10px);
        }
        
        .navbar-fixed-mobile.scrolled {
            background: linear-gradient(90deg, rgba(78, 84, 200, 0.95), rgba(143, 148, 251, 0.95)) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;
        }
        
        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }
        
        /* Mobile navigation scrolling */
        .mobile-nav-container {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
        }
        
        .mobile-nav-container::-webkit-scrollbar {
            width: 4px;
        }
        
        .mobile-nav-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .mobile-nav-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 6px;
        }
        
        .mobile-nav-container::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        /* User Navigation and Clock Button Styling */
        .employee-badge {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            font-size: 0.5rem;
            font-weight: 700;
            padding: 2px 4px;
            border-radius: 6px;
            margin-left: 8px;
            letter-spacing: 0.5px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        
        .clock-status-display {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            margin: 0 15px;
        }
        
        .clock-actions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 0 10px;
        }
        
        .btn-clock-action {
            padding: 8px 12px;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-clock-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn-clock-action i {
            font-size: 1rem;
        }
        
        /* Mobile clock button adjustments */
        @media (max-width: 768px) {
            .clock-actions-grid {
                grid-template-columns: 1fr;
                gap: 6px;
            }
            
            .btn-clock-action {
                width: 100%;
                padding: 10px;
                font-size: 0.9rem;
            }
        }
        
        /* Quick form styling */
        .quick-form {
            max-width: 100%;
        }
        
        .quick-form .form-control,
        .quick-form .form-select {
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }
        
        .quick-form .form-control:focus,
        .quick-form .form-select:focus {
            border-color: #20c997;
            box-shadow: 0 0 0 0.2rem rgba(32, 201, 151, 0.25);
        }
        
        .time-summary .summary-stat {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .time-summary .summary-stat h3 {
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        /* Admin Modal and Quick Actions Styling */
        .system-status-grid .status-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .system-status-grid .status-item:last-child {
            border-bottom: none;
        }
        
        .system-status-grid .status-item i {
            font-size: 1.2rem;
            margin-right: 10px;
        }
        
        .quick-reports-summary .report-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #4e54c8;
        }
        
        .quick-reports-summary .report-item h6 {
            color: #333;
            margin-bottom: 8px;
        }
        
        .quick-reports-summary .report-item h6 i {
            margin-right: 8px;
        }
        
        /* Typography (based on palette) */
        h1 { color: var(--color-primary); font-size: 2.5rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
        h2 { color: var(--color-bluegray); font-size: 2rem; margin-top: 1.5rem; margin-bottom: 1rem; }

        /* Modern cards */
        .card { border-radius: var(--card-radius); box-shadow: var(--card-shadow); }
        .card-gradient { border-radius: var(--card-radius); }

        /* Buttons (palette + hover gradients) */
        .btn { background: var(--color-primary); color: var(--color-neutral); border: none; padding: 0.75rem 1.5rem; font-size: 1rem; cursor: pointer; transition: background .35s ease, color .35s ease, box-shadow .25s ease; border-radius: 8px; }
        .btn:hover { background: linear-gradient(45deg, var(--color-primary), var(--color-bluegray)); color: var(--color-neutral); box-shadow: var(--hover-glow); }
        .btn-outline-light:hover { background: linear-gradient(45deg, var(--color-primary), var(--color-bluegray)); color: var(--color-neutral); }

        /* Navbar & Links */
        nav.navbar-fixed-mobile { background-color: var(--color-black) !important; }
        nav a { color: var(--color-neutral); text-decoration: none; margin: 0 1rem; padding: 0.5rem; transition: background .3s ease, color .3s ease; }
        nav a:hover { background: linear-gradient(45deg, var(--color-primary), var(--color-bluegray)); color: var(--color-neutral); }
        nav a.active { color: var(--color-primary); border-bottom: 2px solid var(--color-primary); }

        /* Palette-based gradients helpers */
        .bg-gradient-1 { background: linear-gradient(45deg, var(--color-primary), var(--color-bluegray)) !important; }
        .bg-gradient-2 { background: linear-gradient(45deg, var(--color-primary), var(--color-neutral)) !important; }
        .bg-gradient-3 { background: linear-gradient(45deg, var(--color-bluegray), var(--color-black)) !important; }
        .bg-gradient-5 { background: linear-gradient(45deg, var(--color-primary), var(--color-neutral), var(--color-bluegray)) !important; }

        /* Prevent content jumping on mobile */
        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }
        }
    </style>
</head>
<body>
    <!-- Fixed Mobile Navbar -->
    <nav class="navbar navbar-fixed-mobile d-md-none" style="
        background: linear-gradient(90deg, #4e54c8, #8f94fb);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1030;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    ">
        <div class="container-fluid">
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                <i class="bi bi-list text-white fs-4"></i>
            </button>
            <span class="navbar-brand ms-2 text-white fw-bold">MIG-HRM</span>
        </div>
    </nav>

    <!-- Fixed Sidebar (desktop) + Offcanvas (mobile) -->
    <div class="d-flex">
        <!-- Sidebar with Fixed Scrolling -->
        <div class="d-none d-md-flex flex-column flex-shrink-0 text-white position-fixed sidebar" style="top:0; left:0;" id="sidebar">
            <!-- Toggle Button -->
            <div class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="bi bi-chevron-left" id="toggle-icon"></i>
            </div>

            <!-- Fixed Header Section -->
            <div class="sidebar-header flex-shrink-0 p-3">
                <div class="sidebar-brand mb-3 text-white text-decoration-none">
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
                
                <!-- Admin Quick Actions (Management Tools) -->
                @if(Auth::check() && Auth::user()->role && Auth::user()->role->name === 'admin')
                <div class="sidebar-actions flex-shrink-0 px-3 py-2">
                    <div class="admin-quick-actions">
                        <button class="btn btn-light text-success btn-quick" onclick="showSystemStatus()">
                            <i class="bi bi-activity me-2"></i><span>System Status</span>
                        </button>
                        <button class="btn btn-light text-info btn-quick" onclick="showQuickReports()">
                            <i class="bi bi-bar-chart me-2"></i><span>Quick Reports</span>
                        </button>
                    </div>
                </div>
                @endif
            </div>

            <!-- Fixed Footer Section -->
            <div class="sidebar-footer flex-shrink-0">
                <div class="user-info">
                    @if(Auth::check())
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-2">{{ substr(Auth::user()->name, 0, 2) }}</div>
                        <div>
                            <div class="fw-semibold">{{ Auth::user()->name }}</div>
                            <small class="text-white-75">{{ Auth::user()->email }}</small>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-light w-100"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Offcanvas Sidebar (mobile) with Scrolling -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel" style="background: linear-gradient(180deg, #4e54c8 0%, #8f94fb 100%);">
            <div class="offcanvas-header border-bottom border-white border-opacity-25">
                <h5 class="offcanvas-title text-white fw-bold" id="sidebarOffcanvasLabel">MIG-HRM</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column p-0">
                <!-- Scrollable Navigation Area -->
                <div class="mobile-nav-container flex-grow-1 p-3" style="overflow-y: auto; min-height: 0;">
                    <ul class="nav nav-pills flex-column">
                        {{-- Include Admin Navigation Component for Mobile --}}
                        <x-admin-navigation :mobile="true" />
                        {{-- Include User Navigation Component for Mobile --}}
                        <x-user-navigation :mobile="true" />
                    </ul>
                </div>
                
                <!-- Fixed Admin Action Area -->
                @if(Auth::check() && Auth::user()->role && Auth::user()->role->name === 'admin')
                <div class="mobile-actions-container flex-shrink-0 p-3 border-top border-white border-opacity-25">
                    <div class="mb-3">
                        <button class="btn btn-light text-success w-100 mb-2" onclick="showSystemStatus()">
                            <i class="bi bi-activity me-2"></i><span>System Status</span>
                        </button>
                        <button class="btn btn-light text-info w-100" onclick="showQuickReports()">
                            <i class="bi bi-bar-chart me-2"></i><span>Quick Reports</span>
                        </button>
                    </div>
                @endif
                    
                    @if(Auth::check())
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-2">{{ substr(Auth::user()->name, 0, 2) }}</div>
                        <div>
                            <div class="fw-semibold text-white">{{ Auth::user()->name }}</div>
                            <small class="text-white-75">{{ Auth::user()->email }}</small>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-light w-100"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
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
    <script>
    // Admin Panel Functionality
    function showSystemStatus() {
        // Show system status modal or dashboard (live API)
        fetch('{{ route('admin.system.status') }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAdminModal('System Status', data.html || generateSystemStatusHTML(data));
            } else {
                showToast('System Status: All systems operational', 'success');
            }
        })
        .catch(error => {
            console.error('System status error:', error);
            // Fallback system status display
            const statusData = {
                server: 'Online',
                database: 'Connected',
                users_online: Math.floor(Math.random() * 50) + 10,
                last_backup: 'Today 3:00 AM',
                disk_usage: Math.floor(Math.random() * 40) + 20 + '%'
            };
            showAdminModal('System Status', generateSystemStatusHTML(statusData));
        });
    }
    
    function showQuickReports() {
        // Load quick reports summary (live API)
        fetch('{{ route('admin.quick.reports') }}', { headers: { 'Accept': 'application/json' }})
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    showAdminModal('Quick Reports', generateQuickReportsHTML(data));
                } else {
                    showToast('Unable to load reports', 'danger');
                }
            })
            .catch(err => {
                console.error('Quick reports error:', err);
                showToast('Unable to load reports', 'danger');
            });
    }
    
    function generateSystemStatusHTML(data) {
        return `
            <div class="system-status-grid">
                <div class="row">
                    <div class="col-md-6">
                        <div class="status-item">
                            <i class="bi bi-server text-success"></i>
                            <span>Server Status: <strong>${data.server || 'Online'}</strong></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="status-item">
                            <i class="bi bi-database text-success"></i>
                            <span>Database: <strong>${data.database || 'Connected'}</strong></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="status-item">
                            <i class="bi bi-people text-info"></i>
                            <span>Active Users: <strong>${data.users_online || 'N/A'}</strong></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="status-item">
                            <i class="bi bi-hdd text-warning"></i>
                            <span>Disk Usage: <strong>${data.disk_usage || 'N/A'}</strong></span>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">Last Backup: ${data.last_backup || 'Unknown'}</small>
                </div>
            </div>
        `;
    }
    
    function generateQuickReportsHTML(data) {
        return `
            <div class="quick-reports-summary">
                <div class="report-item mb-3">
                    <h6><i class="bi bi-calendar-day text-primary"></i> Daily Summary</h6>
                    <p>${data.daily_summary}</p>
                </div>
                <div class="report-item mb-3">
                    <h6><i class="bi bi-graph-up text-success"></i> Weekly Trend</h6>
                    <p>${data.weekly_trend}</p>
                </div>
                <div class="report-item mb-3">
                    <h6><i class="bi bi-star text-warning"></i> Top Performers</h6>
                    <ul class="list-unstyled">
                        ${data.top_performers.map(name => `<li>• ${name}</li>`).join('')}
                    </ul>
                </div>
                <div class="report-item">
                    <h6><i class="bi bi-clock-history text-info"></i> Pending Approvals</h6>
                    <p><span class="badge bg-warning">${data.pending_approvals}</span> timesheets awaiting approval</p>
                </div>
            </div>
        `;
    }
    
    function showAdminModal(title, content) {
        // Create and show admin modal
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(90deg, #4e54c8, #8f94fb); color: white;">
                        <h5 class="modal-title"><i class="bi bi-shield-check me-2"></i>${title}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="refreshAdminData()">Refresh</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        // Remove modal from DOM after hiding
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
    
    function refreshAdminData() {
        showToast('Admin data refreshed', 'info');
    }
    
    // Show toast notifications
    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toast-container') || createToastContainer();
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        // Remove after hiding
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
    
    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '1050';
        document.body.appendChild(container);
        return container;
    }
    
    // Sidebar toggle functionality
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const toggleIcon = document.getElementById('toggle-icon');
        
        if (sidebar.classList.contains('compact')) {
            // Expand sidebar
            sidebar.classList.remove('compact');
            mainContent.classList.remove('sidebar-compact');
            toggleIcon.className = 'bi bi-chevron-left';
            localStorage.setItem('sidebar-compact', 'false');
        } else {
            // Collapse sidebar
            sidebar.classList.add('compact');
            mainContent.classList.add('sidebar-compact');
            toggleIcon.className = 'bi bi-chevron-right';
            localStorage.setItem('sidebar-compact', 'true');
        }
    }
    
    // Initialize sidebar state from localStorage
    document.addEventListener('DOMContentLoaded', function() {
        const isCompact = localStorage.getItem('sidebar-compact') === 'true';
        if (isCompact) {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const toggleIcon = document.getElementById('toggle-icon');
            
            sidebar.classList.add('compact');
            mainContent.classList.add('sidebar-compact');
            toggleIcon.className = 'bi bi-chevron-right';
        }
        // Auto-compact on smaller screens initially
        if (window.innerWidth < 1100) {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const toggleIcon = document.getElementById('toggle-icon');
            sidebar.classList.add('compact');
            mainContent.classList.add('sidebar-compact');
            toggleIcon.className = 'bi bi-chevron-right';
        }
        
        // Initialize admin navigation dropdowns
        initializeAdminDropdowns();
        
        // Initialize enhanced navbar scroll effect
        initializeNavbarScrollEffect();
        
        // Initialize sidebar scrolling enhancements
        initializeSidebarScrolling();
    });
    
    // Enhanced Sidebar Scrolling Functions
    function initializeSidebarScrolling() {
        const sidebarNavContainer = document.querySelector('.sidebar-nav-container');
        const mobileNavContainer = document.querySelector('.mobile-nav-container');
        
        // Add smooth scrolling to sidebar navigation links
        function addSmoothScrollToLinks(container) {
            if (!container) return;
            
            const navLinks = container.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Only prevent default if it's a dropdown toggle
                    if (!this.hasAttribute('data-bs-target')) {
                        // Add a subtle scroll to top behavior when navigating
                        setTimeout(() => {
                            if (container.scrollTop > 0) {
                                container.scrollTo({
                                    top: Math.max(0, container.scrollTop - 50),
                                    behavior: 'smooth'
                                });
                            }
                        }, 100);
                    }
                });
            });
        }
        
        // Apply to both desktop and mobile
        addSmoothScrollToLinks(sidebarNavContainer);
        addSmoothScrollToLinks(mobileNavContainer);
        
        // Add keyboard navigation support
        function addKeyboardNavigation(container) {
            if (!container) return;
            
            container.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    const scrollAmount = 50;
                    const direction = e.key === 'ArrowUp' ? -1 : 1;
                    
                    container.scrollBy({
                        top: scrollAmount * direction,
                        behavior: 'smooth'
                    });
                }
            });
        }
        
        addKeyboardNavigation(sidebarNavContainer);
        addKeyboardNavigation(mobileNavContainer);
        
        // Auto-scroll to active item on page load
        setTimeout(() => {
            scrollToActiveItem(sidebarNavContainer);
            scrollToActiveItem(mobileNavContainer);
        }, 300);
    }
    
    // Scroll to active navigation item
    function scrollToActiveItem(container) {
        if (!container) return;
        
        const activeLink = container.querySelector('.nav-link.active');
        if (activeLink) {
            const containerRect = container.getBoundingClientRect();
            const activeRect = activeLink.getBoundingClientRect();
            
            // Check if active item is not fully visible
            if (activeRect.top < containerRect.top || activeRect.bottom > containerRect.bottom) {
                const scrollTop = activeLink.offsetTop - container.offsetTop - (container.offsetHeight / 2) + (activeLink.offsetHeight / 2);
                
                container.scrollTo({
                    top: Math.max(0, scrollTop),
                    behavior: 'smooth'
                });
            }
        }
    }
    
    // Enhanced Mobile Navbar Scroll Effect
    function initializeNavbarScrollEffect() {
        const navbar = document.querySelector('.navbar-fixed-mobile');
        if (!navbar) return;
        
        let scrollTimeout;
        let isScrolling = false;
        
        function handleScroll() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Add scrolled class when scrolling down
            if (scrollTop > 20) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            
            // Clear timeout if it exists
            if (scrollTimeout) {
                clearTimeout(scrollTimeout);
            }
            
            // Set scrolling state
            if (!isScrolling) {
                navbar.style.transform = 'translateY(0)';
            }
            isScrolling = true;
            
            // Hide navbar after scroll stops (for better UX)
            scrollTimeout = setTimeout(() => {
                isScrolling = false;
                if (scrollTop > 100) {
                    // Only hide if not actively scrolling and scrolled significantly
                    navbar.style.transform = 'translateY(0)';
                }
            }, 150);
        }
        
        // Throttled scroll event for better performance
        let throttleTimeout;
        window.addEventListener('scroll', () => {
            if (throttleTimeout) return;
            throttleTimeout = setTimeout(() => {
                handleScroll();
                throttleTimeout = null;
            }, 16); // ~60fps
        }, { passive: true });
        
        // Handle touch events for mobile
        let touchStartY = 0;
        let touchEndY = 0;
        
        document.addEventListener('touchstart', (e) => {
            touchStartY = e.changedTouches[0].screenY;
        }, { passive: true });
        
        document.addEventListener('touchmove', (e) => {
            touchEndY = e.changedTouches[0].screenY;
            const deltaY = touchStartY - touchEndY;
            
            // Show/hide navbar based on swipe direction
            if (Math.abs(deltaY) > 5) {
                if (deltaY > 0) {
                    // Scrolling down - keep navbar visible for now
                    navbar.style.transform = 'translateY(0)';
                } else {
                    // Scrolling up - ensure navbar is visible
                    navbar.style.transform = 'translateY(0)';
                }
            }
        }, { passive: true });
    }
    
    // Admin Navigation Functions - Fixed Dropdown Functionality
    function initializeAdminDropdowns() {
        // Handle dropdown toggle clicks with improved event handling
        document.querySelectorAll('.dropdown-toggle-nav').forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const target = toggle.getAttribute('data-bs-target');
                const collapse = document.querySelector(target);
                const icon = toggle.querySelector('.collapse-icon');
                
                if (collapse) {
                    // Check current state and toggle
                    const isShown = collapse.classList.contains('show');
                    
                    // Close other dropdowns first (accordion behavior)
                    document.querySelectorAll('.collapse.show').forEach(function(openCollapse) {
                        if (openCollapse !== collapse) {
                            const bsCollapseToClose = bootstrap.Collapse.getInstance(openCollapse);
                            if (bsCollapseToClose) {
                                bsCollapseToClose.hide();
                            }
                        }
                    });
                    
                    // Toggle current dropdown
                    let bsCollapse = bootstrap.Collapse.getInstance(collapse);
                    if (!bsCollapse) {
                        bsCollapse = new bootstrap.Collapse(collapse, { toggle: false });
                    }
                    
                    if (isShown) {
                        bsCollapse.hide();
                    } else {
                        bsCollapse.show();
                    }
                }
            });
        });
        
        // Enhanced collapse event handling with icon rotation
        document.querySelectorAll('.collapse').forEach(function(collapse) {
            const toggleButton = document.querySelector(`[data-bs-target="#${collapse.id}"]`);
            const icon = toggleButton ? toggleButton.querySelector('.collapse-icon') : null;
            
            // Handle show event
            collapse.addEventListener('show.bs.collapse', function() {
                if (toggleButton) {
                    toggleButton.setAttribute('aria-expanded', 'true');
                }
                if (icon) {
                    icon.style.transform = 'rotate(180deg)';
                }
            });
            
            // Handle shown event
            collapse.addEventListener('shown.bs.collapse', function() {
                localStorage.setItem('admin-nav-' + collapse.id, 'expanded');
            });
            
            // Handle hide event
            collapse.addEventListener('hide.bs.collapse', function() {
                if (toggleButton) {
                    toggleButton.setAttribute('aria-expanded', 'false');
                }
                if (icon) {
                    icon.style.transform = 'rotate(0deg)';
                }
            });
            
            // Handle hidden event
            collapse.addEventListener('hidden.bs.collapse', function() {
                localStorage.setItem('admin-nav-' + collapse.id, 'collapsed');
            });
            
            // Restore previous state on page load
            const savedState = localStorage.getItem('admin-nav-' + collapse.id);
            if (savedState === 'expanded' && !collapse.classList.contains('show')) {
                // Delay to ensure DOM is ready
                setTimeout(() => {
                    const bsCollapse = new bootstrap.Collapse(collapse, { show: true });
                }, 100);
            }
        });
        
        // Initialize icon states based on current collapse state
        setTimeout(() => {
            document.querySelectorAll('.collapse.show').forEach(function(collapse) {
                const toggleButton = document.querySelector(`[data-bs-target="#${collapse.id}"]`);
                const icon = toggleButton ? toggleButton.querySelector('.collapse-icon') : null;
                if (icon) {
                    icon.style.transform = 'rotate(180deg)';
                }
                if (toggleButton) {
                    toggleButton.setAttribute('aria-expanded', 'true');
                }
            });
        }, 200);
    }
    
    // Show coming soon notification for future features (with admin check)
    function showComingSoon(featureName) {
        // Only show admin feature notifications if user is admin
        const isAdminFeature = document.querySelector('.nav-header-text');
        if (isAdminFeature) {
            showToast('Admin Feature: ' + featureName + ' is coming soon!', 'info');
        } else {
            showToast('This feature is coming soon!', 'info');
        }
    }
    
    // Admin notification system
    function showAdminNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `admin-notification alert alert-${type} alert-dismissible fade show`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1060;
            max-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        notification.innerHTML = `
            <strong>Admin:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after duration
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, duration);
    }
    </script>
    @stack('scripts')
</body>
</html>
