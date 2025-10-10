@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Admin Header -->
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-title-section">
                <h1 class="admin-title">Admin Dashboard</h1>
                <p class="admin-subtitle">Manage your employee time tracking system</p>
            </div>
            <div class="admin-actions">
                <button class="btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                    <i class="bi bi-person-plus"></i>
                    Add Employee
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn-admin-secondary">
                    <i class="bi bi-people"></i>
                    Manage Users
                </a>
            </div>
        </div>
    </div>

    <!-- Modern Stats Cards -->
    <div class="admin-stats-grid">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number" id="total-employees">--</h3>
                <p class="stat-label">Total Employees</p>
            </div>
        </div>
        
        <div class="stat-card stat-card-success">
            <div class="stat-icon">
                <i class="bi bi-person-check"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number" id="present-today">--</h3>
                <p class="stat-label">Present Today</p>
            </div>
        </div>
        
        <div class="stat-card stat-card-info">
            <div class="stat-icon">
                <i class="bi bi-clock"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number" id="total-hours">--</h3>
                <p class="stat-label">Hours This Month</p>
            </div>
        </div>
        
        <div class="stat-card stat-card-warning">
            <div class="stat-icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number" id="avg-attendance">--</h3>
                <p class="stat-label">Avg Attendance</p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="admin-content-grid">
        <!-- Chart Section -->
        <div class="admin-chart-section">
            <div class="admin-card">
                <div class="admin-card-header">
                    <div class="admin-card-title">
                        <i class="bi bi-bar-chart-line"></i>
                        Team Work Hours (Weekly)
                    </div>
                    <a href="{{ route('admin.employees.time.view') }}" class="btn-admin-link">
                        Open Overview
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="admin-card-body">
                    <canvas id="adminWorkChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="admin-activity-section">
            <div class="admin-card">
                <div class="admin-card-header">
                    <div class="admin-card-title">
                        <i class="bi bi-activity"></i>
                        Recent Employee Activity
                    </div>
                    <a href="{{ route('attendance.index') }}" class="btn-admin-link">
                        View All
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="admin-card-body">
                    <div id="recent-activity">
                        <div class="admin-loading">
                            <div class="admin-spinner"></div>
                            <p>Loading activity...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="admin-actions-section">
            <div class="admin-card">
                <div class="admin-card-header">
                    <div class="admin-card-title">
                        <i class="bi bi-lightning"></i>
                        Quick Actions
                    </div>
                </div>
                <div class="admin-card-body">
                    <div class="admin-actions-grid">
                        <a href="{{ route('admin.users.index') }}" class="admin-action-btn admin-action-primary">
                            <i class="bi bi-people"></i>
                            <span>Manage Users</span>
                        </a>
                        <a href="{{ route('attendance.index') }}" class="admin-action-btn admin-action-success">
                            <i class="bi bi-calendar-check"></i>
                            <span>View Attendance</span>
                        </a>
                        <a href="{{ route('timesheet.admin.index') }}" class="admin-action-btn admin-action-info">
                            <i class="bi bi-clock-history"></i>
                            <span>View Timesheets</span>
                        </a>
                        <button class="admin-action-btn admin-action-warning" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                            <i class="bi bi-person-plus"></i>
                            <span>Add Employee</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content admin-modal">
            <div class="admin-modal-header">
                <div class="admin-modal-title">
                    <i class="bi bi-person-plus"></i>
                    Add New Employee
                </div>
                <button type="button" class="admin-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <form id="addEmployeeForm">
                @csrf
                <div class="admin-modal-body">
                    <div id="errorAlert" class="admin-alert admin-alert-danger d-none"></div>
                    
                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label class="admin-form-label">Full Name <span class="required">*</span></label>
                            <input type="text" name="name" class="admin-form-input" placeholder="Enter full name" required />
                        </div>
                        
                        <div class="admin-form-group">
                            <label class="admin-form-label">Email Address <span class="required">*</span></label>
                            <input type="email" name="email" class="admin-form-input" placeholder="Enter email address" required />
                        </div>
                        
                        <div class="admin-form-group">
                            <label class="admin-form-label">Role <span class="required">*</span></label>
                            <select name="role" class="admin-form-select" required>
                                <option value="">Select Role</option>
                                <option value="employee">Employee</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        
                        <div class="admin-form-group">
                            <label class="admin-form-label">Department</label>
                            <select name="department" class="admin-form-select">
                                <option value="">Select Department</option>
                                <option value="Web">Web Development</option>
                                <option value="Graphic">Graphic Design</option>
                                <option value="Editorial">Editorial</option>
                                <option value="Multimedia">Multimedia</option>
                                <option value="Sales">Sales</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Intern">Internship</option>
                                <option value="General">General</option>
                            </select>
                        </div>
                        
                        <div class="admin-form-group">
                            <label class="admin-form-label">Password <span class="required">*</span></label>
                            <input type="password" name="password" class="admin-form-input" placeholder="Create a password" required minlength="8" />
                        </div>
                        
                        <div class="admin-form-group">
                            <label class="admin-form-label">Confirm Password <span class="required">*</span></label>
                            <input type="password" name="password_confirmation" class="admin-form-input" placeholder="Confirm password" required />
                        </div>
                    </div>
                </div>
                
                <div class="admin-modal-footer">
                    <button type="button" class="btn-admin-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-admin-submit">
                        <span class="admin-spinner d-none"></span>
                        <i class="bi bi-person-plus"></i>
                        Create Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load dashboard statistics
    loadDashboardStats();
    
    // Load recent activity
    loadRecentActivity();
    
    // Load admin work chart
    loadAdminWorkChart();
    
    // Handle add employee form
    document.getElementById('addEmployeeForm').addEventListener('submit', handleAddEmployee);
});

function loadDashboardStats() {
    console.log('Loading dashboard statistics...');
    
    fetch('/admin/users/data?stats_only=true', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        console.log('Stats API response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Stats API response data:', data);
        
        if (data.success && data.stats) {
            // Update dashboard cards with actual data
            document.getElementById('total-employees').textContent = data.stats.total_employees || 0;
            document.getElementById('present-today').textContent = data.stats.present_today || 0;
            document.getElementById('total-hours').textContent = (data.stats.total_hours || 0) + 'h';
            document.getElementById('avg-attendance').textContent = (data.stats.avg_attendance || 0) + '%';
            
            console.log('Dashboard statistics updated successfully:', {
                employees: data.stats.total_employees,
                present: data.stats.present_today,
                hours: data.stats.total_hours,
                attendance: data.stats.avg_attendance
            });
        } else {
            console.warn('Stats API returned unsuccessful response:', data);
            setFallbackValues();
        }
    })
    .catch(error => {
        console.error('Error loading stats:', error);
        setFallbackValues();
    });
}

function loadAdminWorkChart() {
    const ctx = document.getElementById('adminWorkChart');
    if (!ctx) return;
    const chart = new Chart(ctx, {
        type: 'bar',
        data: { labels: [], datasets: [{ label: 'Hours', data: [], backgroundColor: 'rgba(254,119,67,0.85)', borderRadius: 8, borderSkipped: false }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
    
    fetch('{{ route('admin.employees.time.chart') }}', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(r => r.json())
    .then(data => {
        // Expecting { labels: [...], data: [...] }
        if (data && (data.labels || data.dates) && (data.data || data.hours)) {
            chart.data.labels = data.labels || data.dates;
            chart.data.datasets[0].data = data.data || data.hours;
            chart.update('active');
        } else {
            setDemoChart(chart);
        }
    })
    .catch(() => setDemoChart(chart));
    
    function setDemoChart(c) {
        c.data.labels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        c.data.datasets[0].data = [32, 28, 35, 30, 34, 0, 0];
        c.update('active');
    }
}

function setFallbackValues() {
    document.getElementById('total-employees').textContent = '0';
    document.getElementById('present-today').textContent = '0';
    document.getElementById('total-hours').textContent = '0h';
    document.getElementById('avg-attendance').textContent = '0%';
    console.log('Fallback values set for dashboard statistics');
}

function loadRecentActivity() {
    console.log('Loading recent activity...');
    
    fetch('/attendance/data?limit=5', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        console.log('Activity API response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Activity API response data:', data);
        
        const container = document.getElementById('recent-activity');
        
        if (data.success && data.attendance && data.attendance.length > 0) {
            container.innerHTML = data.attendance.map(record => {
                const userName = record.user_name || 'Unknown User';
                const status = record.status || (record.clock_in ? 'present' : 'absent');
                const statusColor = status === 'present' ? 'success' : 'danger';
                const clockInTime = record.clock_in || 'Not clocked in';
                
                return `
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-${statusColor} me-3" style="width: 10px; height: 10px;"></div>
                            <div>
                                <div class="fw-medium">${userName}</div>
                                <small class="text-muted">${record.date}</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-${statusColor}">${status}</span>
                            ${record.clock_in ? `<br><small class="text-muted">In: ${clockInTime}</small>` : ''}
                        </div>
                    </div>
                `;
            }).join('');
            
            console.log(`Recent activity loaded: ${data.attendance.length} records`);
        } else {
            container.innerHTML = '<div class="text-center py-4 text-muted"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No recent activity</div>';
            console.log('No recent activity data available');
        }
    })
    .catch(error => {
        console.error('Error loading recent activity:', error);
        document.getElementById('recent-activity').innerHTML = '<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle fs-3 d-block mb-2"></i>Failed to load recent activity</div>';
    });
}

function handleAddEmployee(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const spinner = submitBtn.querySelector('.spinner-border');
    const errorAlert = document.getElementById('errorAlert');
    
    // Show loading state
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    errorAlert.classList.add('d-none');
    
    fetch('/admin/users', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal and refresh stats
            bootstrap.Modal.getInstance(document.getElementById('addEmployeeModal')).hide();
            form.reset();
            loadDashboardStats();
            
            // Show success message (optional)
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">Employee created successfully!</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.body.appendChild(toast);
            new bootstrap.Toast(toast).show();
            setTimeout(() => toast.remove(), 5000);
        } else {
            errorAlert.textContent = data.message || 'Failed to create employee';
            errorAlert.classList.remove('d-none');
        }
    })
    .catch(error => {
        console.error('Error creating employee:', error);
        errorAlert.textContent = 'An error occurred while creating the employee';
        errorAlert.classList.remove('d-none');
    })
    .finally(() => {
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
    });
}
</script>
@endsection


