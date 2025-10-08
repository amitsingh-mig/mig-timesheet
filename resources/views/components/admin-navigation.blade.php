{{-- Modern Admin Navigation Component --}}
@php
    $currentRouteName = Route::currentRouteName();
    $isOnAdminRoute = is_string($currentRouteName) && str_starts_with($currentRouteName, 'admin.');
    $isAdmin = Auth::check() && Auth::user()->role && Auth::user()->role->name === 'admin';
@endphp

@if($isAdmin)
    <!-- Admin Navigation Section -->
    <li class="nav-item-header-modern mt-3 mb-3">
        <div class="nav-header-modern">
            <div class="nav-header-icon">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="nav-header-content">
                <span class="nav-header-title">Admin Panel</span>
                <span class="nav-header-subtitle">System Management</span>
            </div>
        </div>
    </li>
    
    {{-- Admin Dashboard --}}
    <li class="nav-item-modern">
        <a href="{{ route('admin.dashboard') }}" class="nav-link-modern {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <div class="nav-link-icon">
                <i class="bi bi-speedometer2"></i>
            </div>
            <div class="nav-link-content">
                <span class="nav-link-title">Dashboard</span>
                <span class="nav-link-subtitle">Overview & Analytics</span>
            </div>
            <div class="nav-link-badge admin-badge">ADMIN</div>
        </a>
    </li>

    {{-- User Management Section --}}
    <li class="nav-item-modern">
        <div class="nav-section-header">
            <i class="bi bi-people-fill me-2"></i>
            <span>User Management</span>
        </div>
    </li>
    
    {{-- User Management Dropdown --}}
    <li class="nav-item-modern dropdown-nav">
        <a href="#" class="nav-link-modern dropdown-toggle-nav" 
           data-bs-toggle="collapse" 
           data-bs-target="#userManagementCollapse{{ $mobile ?? false ? 'Mobile' : '' }}" 
           aria-expanded="{{ request()->is('admin/users*') ? 'true' : 'false' }}">
            <div class="nav-link-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="nav-link-content">
                <span class="nav-link-title">Users & Employees</span>
                <span class="nav-link-subtitle">Manage all users</span>
            </div>
            <div class="nav-link-arrow">
                <i class="bi bi-chevron-down collapse-icon"></i>
            </div>
        </a>
        <div class="collapse {{ request()->is('admin/users*') ? 'show' : '' }}" 
             id="userManagementCollapse{{ $mobile ?? false ? 'Mobile' : '' }}">
            <div class="nav-submenu">
                <a href="{{ route('admin.users.index') }}" 
                   class="nav-submenu-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                    <div class="nav-submenu-icon">
                        <i class="bi bi-person-lines-fill"></i>
                    </div>
                    <div class="nav-submenu-content">
                        <span class="nav-submenu-title">All Users</span>
                        <span class="nav-submenu-subtitle">View & manage users</span>
                    </div>
                </a>
                
                <a href="{{ route('admin.employees.time.view') }}" 
                   class="nav-submenu-link {{ request()->routeIs('admin.employees.time.view') ? 'active' : '' }}">
                    <div class="nav-submenu-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="nav-submenu-content">
                        <span class="nav-submenu-title">Employee Time</span>
                        <span class="nav-submenu-subtitle">Time tracking overview</span>
                    </div>
                </a>

                <a href="{{ route('timesheet.admin.index') }}" 
                   class="nav-submenu-link {{ request()->routeIs('timesheet.admin.index') ? 'active' : '' }}">
                    <div class="nav-submenu-icon">
                        <i class="bi bi-list-check"></i>
                    </div>
                    <div class="nav-submenu-content">
                        <span class="nav-submenu-title">All Timesheets</span>
                        <span class="nav-submenu-subtitle">Review timesheets</span>
                    </div>
                </a>
            </div>
        </div>
    </li>

    {{-- Quick Actions Section --}}
    <li class="nav-item-modern">
        <div class="nav-section-header">
            <i class="bi bi-lightning-fill me-2"></i>
            <span>Quick Actions</span>
        </div>
    </li>
    
    <li class="nav-item-modern">
        <a href="{{ route('admin.users.index', ['open' => 'create']) }}" class="nav-link-modern quick-action-link">
            <div class="nav-link-icon">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <div class="nav-link-content">
                <span class="nav-link-title">Add Employee</span>
                <span class="nav-link-subtitle">Create new user account</span>
            </div>
            <div class="nav-link-badge new-badge">NEW</div>
        </a>
    </li>

    {{-- System Tools Section --}}
    <li class="nav-item-modern">
        <div class="nav-section-header">
            <i class="bi bi-tools me-2"></i>
            <span>System Tools</span>
        </div>
    </li>
    
    <li class="nav-item-modern">
        <a href="#" class="nav-link-modern" onclick="showSystemStatus()">
            <div class="nav-link-icon">
                <i class="bi bi-activity"></i>
            </div>
            <div class="nav-link-content">
                <span class="nav-link-title">System Status</span>
                <span class="nav-link-subtitle">Monitor system health</span>
            </div>
        </a>
    </li>
    
    <li class="nav-item-modern">
        <a href="#" class="nav-link-modern" onclick="showQuickReports()">
            <div class="nav-link-icon">
                <i class="bi bi-bar-chart-fill"></i>
            </div>
            <div class="nav-link-content">
                <span class="nav-link-title">Quick Reports</span>
                <span class="nav-link-subtitle">Generate reports</span>
            </div>
        </a>
    </li>

@elseif($isOnAdminRoute)
    <!-- Non-Admin attempting to view Admin routes -->
    <li class="mt-4">
        <div class="access-restricted-modern">
            <div class="access-restricted-icon">
                <i class="bi bi-shield-exclamation"></i>
            </div>
            <div class="access-restricted-content">
                <h6 class="access-restricted-title">Access Restricted</h6>
                <p class="access-restricted-message">
                    Admin privileges required to access this section.
                </p>
                <div class="access-restricted-alert">
                    <i class="bi bi-info-circle me-1"></i>
                    Contact your system administrator for access.
                </div>
            </div>
        </div>
    </li>
@endif

{{-- Modern Navigation Styles --}}
@push('styles')
<style>
/* Modern Navigation Styles */
.nav-header-modern {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    margin-bottom: 8px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.nav-header-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.nav-header-icon i {
    font-size: 18px;
    color: white;
}

.nav-header-content {
    flex: 1;
}

.nav-header-title {
    display: block;
    font-weight: 600;
    font-size: 14px;
    color: white;
    line-height: 1.2;
}

.nav-header-subtitle {
    display: block;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.7);
    line-height: 1.2;
}

.nav-section-header {
    display: flex;
    align-items: center;
    padding: 8px 16px;
    margin: 16px 0 8px 0;
    font-size: 11px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-left: 3px solid rgba(255, 255, 255, 0.3);
}

.nav-item-modern {
    margin-bottom: 4px;
}

.nav-link-modern {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.nav-link-modern:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    transform: translateX(4px);
}

.nav-link-modern.active {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
    color: white;
    border-left: 4px solid #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.nav-link-icon {
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    transition: all 0.3s ease;
}

.nav-link-modern:hover .nav-link-icon {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.05);
}

.nav-link-modern.active .nav-link-icon {
    background: linear-gradient(135deg, #667eea, #764ba2);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.nav-link-icon i {
    font-size: 16px;
    color: white;
}

.nav-link-content {
    flex: 1;
}

.nav-link-title {
    display: block;
    font-weight: 500;
    font-size: 14px;
    line-height: 1.2;
    margin-bottom: 2px;
}

.nav-link-subtitle {
    display: block;
    font-size: 11px;
    opacity: 0.7;
    line-height: 1.2;
}

.nav-link-badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.admin-badge {
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    color: white;
    box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
}

.new-badge {
    background: linear-gradient(135deg, #4ecdc4, #44a08d);
    color: white;
    box-shadow: 0 2px 8px rgba(78, 205, 196, 0.3);
}

.nav-link-arrow {
    transition: transform 0.3s ease;
}

.nav-link-modern[aria-expanded="true"] .nav-link-arrow {
    transform: rotate(180deg);
}

.nav-submenu {
    margin-left: 20px;
    margin-top: 8px;
    border-left: 2px solid rgba(255, 255, 255, 0.1);
    padding-left: 16px;
}

.nav-submenu-link {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    margin-bottom: 4px;
}

.nav-submenu-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    transform: translateX(4px);
}

.nav-submenu-link.active {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border-left: 3px solid #667eea;
}

.nav-submenu-icon {
    width: 28px;
    height: 28px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
}

.nav-submenu-icon i {
    font-size: 14px;
    color: white;
}

.nav-submenu-content {
    flex: 1;
}

.nav-submenu-title {
    display: block;
    font-weight: 500;
    font-size: 13px;
    line-height: 1.2;
    margin-bottom: 2px;
}

.nav-submenu-subtitle {
    display: block;
    font-size: 10px;
    opacity: 0.7;
    line-height: 1.2;
}

.access-restricted-modern {
    text-align: center;
    padding: 24px 16px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.access-restricted-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
}

.access-restricted-icon i {
    font-size: 24px;
    color: white;
}

.access-restricted-title {
    color: white;
    font-weight: 600;
    margin-bottom: 8px;
}

.access-restricted-message {
    color: rgba(255, 255, 255, 0.7);
    font-size: 14px;
    margin-bottom: 16px;
}

.access-restricted-alert {
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    border: 1px solid rgba(255, 193, 7, 0.3);
}

/* Quick Action Link Special Styling */
.quick-action-link {
    background: linear-gradient(135deg, rgba(78, 205, 196, 0.2), rgba(68, 160, 141, 0.2));
    border: 1px solid rgba(78, 205, 196, 0.3);
}

.quick-action-link:hover {
    background: linear-gradient(135deg, rgba(78, 205, 196, 0.3), rgba(68, 160, 141, 0.3));
    border-color: rgba(78, 205, 196, 0.5);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .nav-header-modern {
        padding: 10px 12px;
    }
    
    .nav-link-modern {
        padding: 10px 12px;
    }
    
    .nav-submenu {
        margin-left: 16px;
        padding-left: 12px;
    }
}
</style>
@endpush

{{-- JavaScript for component functionality --}}
@push('scripts')
<script>
// Modern navigation interactions
document.addEventListener('DOMContentLoaded', function() {
    // Smooth collapse animations
    document.querySelectorAll('.dropdown-toggle-nav').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('data-bs-target'));
            const icon = this.querySelector('.collapse-icon');
            
            if (target) {
                if (target.classList.contains('show')) {
                    target.classList.remove('show');
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    target.classList.add('show');
                    icon.style.transform = 'rotate(180deg)';
                }
            }
        });
    });
    
    // Add ripple effect to nav links
    document.querySelectorAll('.nav-link-modern').forEach(link => {
        link.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});

// Admin security validation
function validateAdminAccess(functionName = 'admin function') {
    const isAdminSection = document.querySelector('.nav-header-modern');
    
    if (!isAdminSection) {
        console.warn('Unauthorized access attempt to', functionName);
        showToast('Access denied: Admin privileges required', 'danger');
        return false;
    }
    return true;
}

// System status and reports functions
function showSystemStatus() {
    if (!validateAdminAccess('System Status')) return;
    showToast('System Status feature coming soon!', 'info');
}

function showQuickReports() {
    if (!validateAdminAccess('Quick Reports')) return;
    showToast('Quick Reports feature coming soon!', 'info');
}

// Toast notification function
function showToast(message, type = 'info') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    const toast = createToast(message, type);
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

function createToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    return toast;
}
</script>
@endpush