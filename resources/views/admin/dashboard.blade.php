@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Admin Dashboard</h2>
                    <p class="text-muted mb-0">Manage your employee time tracking system</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                        <i class="bi bi-person-plus"></i> Add Employee
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-people"></i> Manage Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 bg-gradient-1 card-gradient text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <h3 class="mb-1" id="total-employees">--</h3>
                        <small class="opacity-90">Total Employees</small>
                    </div>
                    <i class="bi bi-people fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 bg-gradient-2 card-gradient text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <h3 class="mb-1" id="present-today">--</h3>
                        <small class="opacity-90">Present Today</small>
                    </div>
                    <i class="bi bi-person-check fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 bg-gradient-3 card-gradient text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <h3 class="mb-1" id="total-hours">--</h3>
                        <small class="opacity-90">Hours This Month</small>
                    </div>
                    <i class="bi bi-clock fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 bg-gradient-5 card-gradient text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <h3 class="mb-1" id="avg-attendance">--</h3>
                        <small class="opacity-90">Avg Attendance</small>
                    </div>
                    <i class="bi bi-graph-up fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions, Work Time Chart & Recent Activity -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>Team Work Hours (Weekly)</h5>
                    <a href="{{ route('admin.employees.time.view') }}" class="btn btn-sm btn-outline-primary">Open Overview</a>
                </div>
                <div class="card-body" style="height: 340px;">
                    <canvas id="adminWorkChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Employee Activity</h5>
                    <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div id="recent-activity">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-people me-2"></i>Manage Users
                        </a>
                        <a href="{{ route('attendance.index') }}" class="btn btn-outline-success text-start">
                            <i class="bi bi-calendar-check me-2"></i>View Attendance
                        </a>
                        <a href="{{ route('timesheet.admin.index') }}" class="btn btn-outline-info text-start">
                            <i class="bi bi-clock-history me-2"></i>View Timesheets
                        </a>
                        <button class="btn btn-outline-warning text-start" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                            <i class="bi bi-person-plus me-2"></i>Add Employee
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-1 text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add New Employee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addEmployeeForm">
                @csrf
                <div class="modal-body">
                    <div id="errorAlert" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Enter full name" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email address" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="">Select Role</option>
                            <option value="employee">Employee</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-select">
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
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Create a password" required minlength="8" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" required />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <i class="bi bi-person-plus"></i> Create Employee
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
    
    fetch('/attendance/data?limit=10', {
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


