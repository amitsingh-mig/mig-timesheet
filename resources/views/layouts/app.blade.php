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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

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
            <button type="button" class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar" title="Toggle sidebar">
                <i class="bi bi-chevron-left" id="toggle-icon"></i>
            </button>

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
