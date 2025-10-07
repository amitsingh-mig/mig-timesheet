{{-- 
    Alaga Role-Aware Sidebar Component
    
    Usage: <x-alaga-sidebar :activeRoute="$activeRoute ?? Route::currentRouteName()" />
    
    Features:
    - Role-based menu display (admin, employee, or both)
    - Active route highlighting with accessibility
    - Permission checking with @can directives
    - Keyboard navigation support
    - Screen reader friendly
    - Responsive design
--}}

@php
    // Get the active route for highlighting
    $activeRoute = $activeRoute ?? Route::currentRouteName();
    
    // Get authenticated user
    $user = Auth::user();
    
    // Use reliable role detection methods from enhanced User model
    $hasAdminRole = $user ? $user->isAdmin() : false;
    $hasEmployeeRole = $user ? $user->isEmployee() : false;
    $userTheme = $user ? $user->getThemePreference() : 'default-theme';
    $primaryColor = $user ? $user->getPrimaryColor() : 'gray';
    $isOnAdminRoute = isset($activeRoute) && is_string($activeRoute) ? str_starts_with($activeRoute, 'admin.') : false;
    
    // Define menu items with route checking
    $adminMenuItems = [
        [
            'route' => 'admin.dashboard',
            'label' => 'Admin Dashboard',
            'icon' => 'speedometer2',
            'permission' => null, // Add permission check if needed, e.g., 'admin.dashboard.view'
        ],
        [
            'route' => 'admin.users.index',
            'label' => 'Users',
            'icon' => 'people',
            'permission' => null, // e.g., 'admin.users.manage'
        ],
        [
            'route' => 'admin.employees.time.view',
            'label' => 'Employee Time',
            'icon' => 'clock-history',
            'permission' => null, // e.g., 'admin.employees.view'
        ],
    ];
    
    $employeeMenuItems = [
        [
            'route' => 'dashboard',
            'label' => 'My Dashboard',
            'icon' => 'house',
            'permission' => null,
        ],
        [
            'route' => 'profile.edit',
            'label' => 'My Profile',
            'icon' => 'person-gear',
            'permission' => null,
        ],
        [
            'route' => 'timesheet.index',
            'label' => 'My Timesheets',
            'icon' => 'card-checklist',
            'permission' => null, // e.g., 'employee.timesheet.manage'
        ],
        [
            'route' => 'attendance.index',
            'label' => 'My Attendance',
            'icon' => 'calendar-check',
            'permission' => null, // e.g., 'employee.attendance.view'
        ],
        [
            'route' => 'daily-update.index',
            'label' => 'Daily Updates',
            'icon' => 'journal-text',
            'permission' => null, // e.g., 'employee.updates.manage'
        ],
    ];
    
    // Helper function to check if route is active
    $isActiveRoute = function($route) use ($activeRoute) {
        if (!$route || !Route::has($route)) return false;
        
        // Check for exact match
        if ($activeRoute === $route) return true;
        
        // Check for prefix match (e.g., admin.users.* routes)
        $routePrefix = explode('.', $route)[0] ?? '';
        $activePrefix = explode('.', $activeRoute)[0] ?? '';
        
        if ($routePrefix === 'admin' && $activePrefix === 'admin') {
            return str_starts_with($activeRoute, rtrim($route, '.index') . '.');
        }
        
        return str_starts_with($activeRoute, $route);
    };
    
    // Helper function to check if user can access menu item
    $canAccess = function($item) use ($user) {
        // Check if route exists
        if (!Route::has($item['route'])) return false;
        
        // Check permission if specified
        if (!empty($item['permission']) && !$user->can($item['permission'])) {
            return false;
        }
        
        return true;
    };
@endphp

{{-- Apply different styling based on user role --}}
@php
    $baseSidebarClasses = 'alaga-sidebar fixed inset-y-0 left-0 z-50 w-64 transform transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0';
    $themeSidebarClasses = $hasAdminRole 
        ? 'alaga-sidebar-admin bg-gradient-to-b from-blue-600 to-purple-700 text-white shadow-xl'
        : 'alaga-sidebar-employee bg-gradient-to-b from-green-500 to-blue-600 text-white shadow-xl';
    $sidebarClasses = $baseSidebarClasses . ' ' . $themeSidebarClasses;
@endphp

{{-- Mobile Overlay --}}
<div 
    x-show="sidebarOpen" 
    @click="sidebarOpen = false"
    class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden transition-opacity duration-300"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
></div>

<nav 
    x-data="{ sidebarOpen: false }"
    x-show="sidebarOpen || window.innerWidth >= 1024"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    aria-label="Alaga Sidebar Navigation" 
    class="{{ $sidebarClasses }}"
    role="navigation"
    x-transition:enter="transform transition ease-in-out duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transform transition ease-in-out duration-300"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
>
    <!-- Brand/Logo Section -->
    <div class="alaga-brand p-6 {{ $hasAdminRole ? 'border-b border-red-300/30' : 'border-b border-white/20' }}">
        <h1 class="text-xl font-bold tracking-wide">
            <i class="bi bi-building text-2xl mr-2" aria-hidden="true"></i>
            MIG-HRM
        </h1>
        <p class="{{ $hasAdminRole ? 'text-blue-100' : 'text-green-100' }} text-sm mt-1">
            {{ $hasAdminRole ? 'Admin Control Panel' : 'Employee Portal' }}
        </p>
    </div>

    {{-- Admin Privilege Indicator Section (Only when a non-admin enters an admin route) --}}
    @if(!$hasAdminRole && $isOnAdminRoute)
        <div class="alaga-admin-privileges p-6 bg-white/5 border-2 border-red-300/20 rounded-xl m-4 backdrop-blur-sm">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-white/10 rounded-full flex items-center justify-center">
                    <i class="bi bi-shield-exclamation text-4xl text-yellow-300" aria-hidden="true"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Access Restricted</h3>
                <p class="text-sm text-gray-300 mb-6 leading-relaxed px-2">
                    Admin privileges required to access this section.
                </p>
                <button 
                    type="button"
                    class="w-full px-4 py-3 bg-gray-500/30 hover:bg-gray-500/40 text-gray-200 text-sm rounded-lg transition-all duration-200 flex items-center justify-center border border-gray-400/20 hover:border-gray-400/30"
                    onclick="contactAdmin()"
                >
                    <i class="bi bi-info-circle mr-2" aria-hidden="true"></i>
                    Contact your system administrator for access.
                </button>
            </div>
        </div>
    @endif

    <!-- Navigation Menu -->
    <div class="alaga-nav-content p-4 space-y-6 overflow-y-auto">
        
        {{-- Admin Menu Group --}}
        @if($hasAdminRole)
            <div class="alaga-menu-group">
                <div class="alaga-menu-header mb-3">
                    <h2 class="text-xs font-semibold text-blue-200 uppercase tracking-wider flex items-center">
                        <i class="bi bi-shield-check mr-2" aria-hidden="true"></i>
                        Admin Panel
                        <span class="ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">Admin</span>
                    </h2>
                </div>
                
                <ul class="alaga-menu-list space-y-1" role="list">
                    @foreach($adminMenuItems as $item)
                        @if($canAccess($item))
                            @php $isActive = $isActiveRoute($item['route']); @endphp
                            
                            <li role="listitem">
                                {{-- Use @can if you have specific permissions --}}
                                {{-- @can($item['permission'] ?? 'admin.access') --}}
                                
                                <a 
                                    href="{{ route($item['route']) }}"
                                    class="alaga-nav-link flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 group
                                           {{ $isActive 
                                              ? 'bg-white/20 text-white shadow-md border-l-4 border-yellow-400' 
                                              : 'text-blue-100 hover:bg-white/10 hover:text-white focus:bg-white/10 focus:text-white' }}"
                                    {{ $isActive ? 'aria-current="page"' : '' }}
                                >
                                    <i class="bi bi-{{ $item['icon'] }} w-5 h-5 mr-3 transition-transform group-hover:scale-110" 
                                       aria-hidden="true"></i>
                                    <span class="font-medium">{{ $item['label'] }}</span>
                                    
                                    @if($isActive)
                                        <span class="sr-only">(current page)</span>
                                        <i class="bi bi-chevron-right ml-auto opacity-75" aria-hidden="true"></i>
                                    @endif
                                </a>
                                
                                {{-- @endcan --}}
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            
            {{-- Admin Quick Actions --}}
            <div class="alaga-admin-actions space-y-3">
                <button 
                    type="button"
                    onclick="checkSystemStatus()"
                    class="w-full px-4 py-3 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-all duration-200 flex items-center justify-between border border-white/20 hover:border-white/40"
                >
                    <div class="flex items-center">
                        <i class="bi bi-activity mr-3 text-green-400" aria-hidden="true"></i>
                        <span class="font-medium text-left">
                            <div class="text-sm font-semibold">System Status</div>
                        </span>
                    </div>
                    <i class="bi bi-arrow-right opacity-60" aria-hidden="true"></i>
                </button>
                
                <button 
                    type="button"
                    onclick="generateQuickReports()"
                    class="w-full px-4 py-3 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-all duration-200 flex items-center justify-between border border-white/20 hover:border-white/40"
                >
                    <div class="flex items-center">
                        <i class="bi bi-graph-up mr-3 text-cyan-400" aria-hidden="true"></i>
                        <span class="font-medium text-left">
                            <div class="text-sm font-semibold">Quick Reports</div>
                        </span>
                    </div>
                    <i class="bi bi-arrow-right opacity-60" aria-hidden="true"></i>
                </button>
            </div>
        @endif

        {{-- Employee Menu Group --}}
        @if($hasEmployeeRole)
            <div class="alaga-menu-group">
                <div class="alaga-menu-header mb-3">
                    <h2 class="text-xs font-semibold {{ $hasAdminRole ? 'text-blue-200' : 'text-green-200' }} uppercase tracking-wider flex items-center">
                        <i class="bi bi-person-workspace mr-2" aria-hidden="true"></i>
                        {{ $hasAdminRole ? 'Employee Portal' : 'My Workspace' }}
                        <span class="ml-auto bg-green-500 text-white text-xs px-2 py-0.5 rounded-full">Employee</span>
                    </h2>
                </div>
                
                <ul class="alaga-menu-list space-y-1" role="list">
                    @foreach($employeeMenuItems as $item)
                        @if($canAccess($item))
                            @php $isActive = $isActiveRoute($item['route']); @endphp
                            
                            <li role="listitem">
                                {{-- Use @can if you have specific permissions --}}
                                {{-- @can($item['permission'] ?? 'employee.access') --}}
                                
                                <a 
                                    href="{{ route($item['route']) }}"
                                    class="alaga-nav-link flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 group
                                           {{ $isActive 
                                              ? 'bg-white/20 text-white shadow-md border-l-4 border-green-400' 
                                              : ($hasAdminRole ? 'text-blue-100 hover:bg-white/10 hover:text-white focus:bg-white/10 focus:text-white' : 'text-green-100 hover:bg-white/10 hover:text-white focus:bg-white/10 focus:text-white') }}"
                                    {{ $isActive ? 'aria-current="page"' : '' }}
                                >
                                    <i class="bi bi-{{ $item['icon'] }} w-5 h-5 mr-3 transition-transform group-hover:scale-110" 
                                       aria-hidden="true"></i>
                                    <span class="font-medium">{{ $item['label'] }}</span>
                                    
                                    @if($isActive)
                                        <span class="sr-only">(current page)</span>
                                        <i class="bi bi-chevron-right ml-auto opacity-75" aria-hidden="true"></i>
                                    @endif
                                </a>
                                
                                {{-- @endcan --}}
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Employee Quick Actions Section (Clock In/Out) --}}
        @if($hasEmployeeRole && !$hasAdminRole)
            <div class="alaga-quick-actions mt-8 p-4 bg-white/10 rounded-lg border border-white/20">
                <h3 class="text-sm font-semibold text-green-200 mb-3 flex items-center">
                    <i class="bi bi-lightning mr-2" aria-hidden="true"></i>
                    Quick Actions
                </h3>
                <div class="space-y-3">
                    <button 
                        type="button"
                        onclick="clockIn()"
                        class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-md transition-all duration-200 flex items-center justify-center shadow-lg hover:shadow-xl"
                        aria-describedby="clock-in-desc"
                    >
                        <i class="bi bi-play-circle mr-2 text-lg" aria-hidden="true"></i>
                        <span class="font-medium">Clock In</span>
                    </button>
                    <div id="clock-in-desc" class="sr-only">Start your work day by clocking in</div>
                    
                    <button 
                        type="button"
                        onclick="clockOut()"
                        class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-md transition-all duration-200 flex items-center justify-center shadow-lg hover:shadow-xl"
                        aria-describedby="clock-out-desc"
                    >
                        <i class="bi bi-stop-circle mr-2 text-lg" aria-hidden="true"></i>
                        <span class="font-medium">Clock Out</span>
                    </button>
                    <div id="clock-out-desc" class="sr-only">End your work day by clocking out</div>
                </div>
            </div>
        @endif
    </div>

    <!-- User Profile Section -->
    @if(Auth::check())
        <div class="alaga-user-profile p-4 {{ $hasAdminRole ? 'border-t border-red-300/30' : 'border-t border-white/20' }} mt-auto">
            <div class="flex items-center space-x-3">
                <div class="alaga-avatar w-10 h-10 {{ $hasAdminRole ? 'bg-red-500/30 border-2 border-red-400/50' : 'bg-green-500/30 border-2 border-green-400/50' }} rounded-full flex items-center justify-center text-lg font-bold">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">
                        {{ Auth::user()->name ?? 'User' }}
                    </p>
                    <p class="text-xs {{ $hasAdminRole ? 'text-red-200' : 'text-green-200' }} truncate">
                        {{ Auth::user()->email ?? '' }}
                    </p>
                    <p class="text-xs {{ $hasAdminRole ? 'text-blue-200' : 'text-green-300' }} truncate font-medium mt-1">
                        {{ $hasAdminRole && $hasEmployeeRole ? 'Admin & Employee' : ($hasAdminRole ? 'Administrator' : 'Employee') }}
                    </p>
                </div>
            </div>
            
            {{-- Logout Button --}}
            <form action="{{ route('logout') }}" method="POST" class="mt-3">
                @csrf
                <button 
                    type="submit" 
                    class="w-full px-3 py-2 {{ $hasAdminRole ? 'bg-red-600/20 hover:bg-red-600/30 border-red-400/30' : 'bg-green-600/20 hover:bg-green-600/30 border-green-400/30' }} border rounded-md text-white transition-colors duration-200 flex items-center justify-center text-sm font-medium"
                    title="Logout"
                    aria-label="Logout from application"
                >
                    <i class="bi bi-box-arrow-right mr-2" aria-hidden="true"></i>
                    Logout
                </button>
            </form>
        </div>
    @endif
</nav>

{{-- Inline Styles for demonstration - move to your CSS file --}}
<style>
/* Focus styles for accessibility */
.alaga-nav-link:focus {
    outline: 2px solid #fbbf24;
    outline-offset: 2px;
}

/* Active state animations */
.alaga-nav-link.active {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(-5px);
        opacity: 0.8;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Responsive behavior */
@media (max-width: 768px) {
    .alaga-sidebar {
        width: 100%;
        min-height: auto;
    }
}

/* Print styles */
@media print {
    .alaga-sidebar {
        display: none;
    }
}
</style>

{{-- JavaScript for quick actions (if not already defined elsewhere) --}}
@push('scripts')
<script>
// Admin contact functionality
window.contactAdmin = window.contactAdmin || function() {
    alert('Please contact your system administrator at admin@mig-hrm.com or call ext. 100 for access.');
};

// Admin quick actions
window.checkSystemStatus = window.checkSystemStatus || function() {
    // Add your system status check logic here
    fetch('{{ route("admin.system.status", [], false) ?: "/admin/system/status" }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('System Status: ' + (data.status || 'All systems operational'));
        } else {
            alert(data.message || 'Unable to fetch system status');
        }
    })
    .catch(error => {
        console.error('System status error:', error);
        alert('System status check failed. Please try again.');
    });
};

window.generateQuickReports = window.generateQuickReports || function() {
    // Add your quick reports logic here
    fetch('{{ route("admin.reports.quick", [], false) ?: "/admin/reports/quick" }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.open(data.report_url || '/admin/reports', '_blank');
        } else {
            alert(data.message || 'Unable to generate reports');
        }
    })
    .catch(error => {
        console.error('Quick reports error:', error);
        alert('Report generation failed. Please try again.');
    });
};

// Clock In/Out functionality - adapt these to match your existing functions
window.clockIn = window.clockIn || function() {
    // Add your clock in logic here
    fetch('{{ route("attendance.clockin") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Clocked in successfully!');
            // Optionally refresh the page or update UI
            window.location.reload();
        } else {
            alert(data.message || 'Clock in failed');
        }
    })
    .catch(error => {
        console.error('Clock in error:', error);
        alert('Clock in failed. Please try again.');
    });
};

window.clockOut = window.clockOut || function() {
    // Add your clock out logic here
    fetch('{{ route("attendance.clockout") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Clocked out successfully!');
            // Optionally refresh the page or update UI
            window.location.reload();
        } else {
            alert(data.message || 'Clock out failed');
        }
    })
    .catch(error => {
        console.error('Clock out error:', error);
        alert('Clock out failed. Please try again.');
    });
};
</script>
@endpush
