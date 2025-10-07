{{-- Admin Navigation Component - Restricted to Admin Users Only --}}
@php
    $currentRouteName = Route::currentRouteName();
    $isOnAdminRoute = is_string($currentRouteName) && str_starts_with($currentRouteName, 'admin.');
    $isAdmin = Auth::check() && Auth::user()->role && Auth::user()->role->name === 'admin';
@endphp

@if($isAdmin)
    <!-- Admin Navigation Section -->
    <li class="nav-item-header mt-3 mb-2">
        <span class="nav-header-text">
            <i class="bi bi-shield-check me-1"></i>
            ADMIN PANEL
            <span class="admin-only-badge">ADMIN ONLY</span>
        </span>
    </li>
    
    {{-- Admin Dashboard --}}
    <li>
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-white' }}">
            <i class="bi bi-speedometer2 me-2"></i>
            <span>Dashboard</span>
            @if($showBadges ?? true)
                <span class="admin-badge">ADMIN</span>
            @endif
        </a>
    </li>

    
    {{-- User Management Dropdown --}}
    <li class="nav-item dropdown-nav">
        <a href="#" class="nav-link text-white dropdown-toggle-nav" 
           data-bs-toggle="collapse" 
           data-bs-target="#userManagementCollapse{{ $mobile ?? false ? 'Mobile' : '' }}" 
           aria-expanded="{{ request()->is('admin/users*') ? 'true' : 'false' }}">
            <i class="bi bi-people me-2"></i>
            <span>User Management</span>
            <i class="bi bi-chevron-down ms-auto collapse-icon"></i>
        </a>
        <div class="collapse {{ request()->is('admin/users*') ? 'show' : '' }}" 
             id="userManagementCollapse{{ $mobile ?? false ? 'Mobile' : '' }}">
            <ul class="nav flex-column ms-3">
                <li>
                    <a href="{{ route('admin.users.index') }}" 
                       class="nav-link sub-nav-link {{ request()->routeIs('admin.users.index') ? 'active' : 'text-white' }}">
                        <i class="bi bi-person me-2"></i>
                        <span>All Users</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.employees.time.view') }}" 
                       class="nav-link sub-nav-link {{ request()->routeIs('admin.employees.time.view') ? 'active' : 'text-white' }}">
                        <i class="bi bi-clock-history me-2"></i>
                        <span>Employee Time</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('timesheet.admin.index') }}" 
                       class="nav-link sub-nav-link {{ request()->routeIs('timesheet.admin.index') ? 'active' : 'text-white' }}">
                        <i class="bi bi-list-check me-2"></i>
                        <span>All Timesheets</span>
                    </a>
                </li>

            </ul>
        </div>
    </li>
    
    
    <li>
        <a href="{{ route('admin.users.index', ['open' => 'create']) }}" 
           class="nav-link text-white quick-action-link">
            <i class="bi bi-person-plus me-2"></i>
            <span>Add Employee</span>
        </a>
    </li>

    {{-- Timesheet Management Dropdown --}}
    {{-- <li class="nav-item dropdown-nav">
        <a href="#" class="nav-link text-white dropdown-toggle-nav" 
           data-bs-toggle="collapse" 
           data-bs-target="#timesheetManagementCollapse{{ $mobile ?? false ? 'Mobile' : '' }}" 
           aria-expanded="{{ request()->is('admin/timesheet*') ? 'true' : 'false' }}">
            <i class="bi bi-calendar-check me-2"></i>
            <span>Timesheet Management</span>
            <i class="bi bi-chevron-down ms-auto collapse-icon"></i>
        </a>
        <div class="collapse {{ request()->is('admin/timesheet*') ? 'show' : '' }}" 
             id="timesheetManagementCollapse{{ $mobile ?? false ? 'Mobile' : '' }}">
            <ul class="nav flex-column ms-3">
               
            </ul>
        </div>
    </li> --}}
    
    {{-- Attendance Management --}}
    {{-- <li>
        <a href="{{ route('attendance.index') }}" 
           class="nav-link {{ request()->is('attendance*') && !request()->is('admin/attendance*') ? 'active' : 'text-white' }}">
            <i class="bi bi-person-check me-2"></i>
            <span>Attendance Overview</span>
        </a>
    </li> --}}
    
    {{-- System Status & Quick Reports --}}
    <li>
        <a href="#" class="nav-link text-white" onclick="showSystemStatus()">
            <i class="bi bi-activity me-2"></i>
            <span>System Status</span>
        </a>
    </li>
    <li>
        <a href="#" class="nav-link text-white" onclick="showQuickReports()">
            <i class="bi bi-bar-chart me-2"></i>
            <span>Quick Reports</span>
        </a>
    </li>

    
    
    
    
    
@elseif($isOnAdminRoute)
    <!-- Non-Admin attempting to view Admin routes -->
    <li class="mt-4">
        <div class="access-restricted">
            <div class="mb-3">
                <i class="bi bi-shield-exclamation" style="font-size: 2rem; opacity: 0.7;"></i>
            </div>
            <h6 class="text-white mb-2">Access Restricted</h6>
            <p class="text-white-50 mb-3" style="font-size: 0.85rem;">
                Admin privileges required to access this section.
            </p>
            <div class="alert alert-warning" style="font-size: 0.75rem; padding: 6px 10px; margin-bottom: 0;">
                <i class="bi bi-info-circle me-1"></i>
                Contact your system administrator for access.
            </div>
        </div>
    </li>
@endif

{{-- JavaScript for component functionality --}}
@push('scripts')
<script>
// Admin security validation
function validateAdminAccess(functionName = 'admin function') {
    // Check if current user is admin (this is client-side validation only)
    const userInfo = document.querySelector('.user-info .fw-semibold');
    const isAdminSection = document.querySelector('.nav-header-text');
    
    if (!isAdminSection) {
        console.warn('Unauthorized access attempt to', functionName);
        showToast('Access denied: Admin privileges required', 'danger');
        return false;
    }
    return true;
}

// Open add employee modal with security check
function openAddEmployeeModal() {
    if (!validateAdminAccess('Add Employee Modal')) return;
    
    const modal = document.getElementById('addEmployeeModal');
    if (modal) {
        new bootstrap.Modal(modal).show();
    } else {
        showToast('Add Employee feature is available on the Admin Dashboard', 'info');
    }
}

// Quick action link styles
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.quick-action-link').forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.background = 'linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.1))';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.background = '';
        });
    });
});
</script>
@endpush
