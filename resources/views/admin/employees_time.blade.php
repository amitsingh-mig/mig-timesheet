@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">Employee Time Overview</h1>
            <p class="text-muted mb-0">View and analyze all employee time logs</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-people me-2"></i>Back to Users
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <select id="employeeFilter" class="form-select">
                        <option value="">All Employees</option>
                        <!-- Populated by JavaScript -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" id="startDateFilter" class="form-control" value="{{ date('Y-m-01') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" id="endDateFilter" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <div class="d-grid">
                        <button class="btn btn-primary" onclick="loadEmployeeTime()">
                            <i class="bi bi-funnel me-1"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Hours Overview</h5>
            <div class="btn-group btn-group-sm" role="group">
                <input type="radio" class="btn-check" name="chartPeriod" id="chartDay" autocomplete="off">
                <label class="btn btn-outline-primary" for="chartDay" onclick="updateChart('day')">Day</label>
                
                <input type="radio" class="btn-check" name="chartPeriod" id="chartWeek" autocomplete="off" checked>
                <label class="btn btn-outline-primary" for="chartWeek" onclick="updateChart('week')">Week</label>
                
                <input type="radio" class="btn-check" name="chartPeriod" id="chartMonth" autocomplete="off">
                <label class="btn btn-outline-primary" for="chartMonth" onclick="updateChart('month')">Month</label>
                
                <input type="radio" class="btn-check" name="chartPeriod" id="chartYear" autocomplete="off">
                <label class="btn btn-outline-primary" for="chartYear" onclick="updateChart('year')">Year</label>
            </div>
        </div>
        <div class="card-body">
            <canvas id="hoursChart" height="80"></canvas>
        </div>
    </div>

    <!-- Time Logs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Time Logs</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary" onclick="exportData()">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Employee Name</th>
                            <th>Date</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Total Hours</th>
                            <th>Tasks Completed</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="timeLogsTableBody">
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light d-flex justify-content-between align-items-center">
            <small class="text-muted">Showing <span id="timeLogCount">0</span> time logs</small>
            <nav aria-label="Time logs pagination">
                <ul class="pagination pagination-sm mb-0" id="timeLogsPagination"></ul>
            </nav>
        </div>
    </div>
</div>

<!-- Employee Time Details Modal -->
<div class="modal fade" id="timeDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-admin text-white">
                <h5 class="modal-title text-white"><i class="bi bi-clock-history me-2"></i>Time Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="timeDetailsContent">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentPage = 1;
let currentPeriod = 'week';
let hoursChart;

function loadEmployees() {
    fetch('/admin/users/data')
        .then(r => r.json())
        .then(data => {
            const select = document.getElementById('employeeFilter');
            select.innerHTML = '<option value="">All Employees</option>';
            
            if (data.success && data.users) {
                data.users.forEach(user => {
                    select.innerHTML += `<option value="${user.id}">${user.name}</option>`;
                });
            }
        })
        .catch(() => {
            // Demo data fallback
            const select = document.getElementById('employeeFilter');
            select.innerHTML = '<option value="">All Employees</option>';
            ['Alice Johnson', 'Bob Smith', 'Carol Lee'].forEach((name, i) => {
                select.innerHTML += `<option value="${i+1}">${name}</option>`;
            });
        });
}

function loadEmployeeTime(page = 1) {
    currentPage = page;
    const employee = document.getElementById('employeeFilter').value;
    const startDate = document.getElementById('startDateFilter').value;
    const endDate = document.getElementById('endDateFilter').value;

    console.log('Loading employee time data:', { page, employee, startDate, endDate });

    const params = new URLSearchParams({
        page,
        employee_id: employee,
        start_date: startDate,
        end_date: endDate
    });

    // Show loading state
    const tbody = document.getElementById('timeLogsTableBody');
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';

    fetch(`/admin/employees/time?${params.toString()}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Employee time response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
        .then(data => {
            console.log('Employee time data received:', data);
            
            const tbody = document.getElementById('timeLogsTableBody');
            const count = document.getElementById('timeLogCount');
            const pagination = document.getElementById('timeLogsPagination');

            if (data.success && data.timeLogs && data.timeLogs.length > 0) {
                tbody.innerHTML = data.timeLogs.map(log => `
                    <tr>
                        <td class="fw-medium">${log.employee_name}</td>
                        <td>${log.date}</td>
                        <td>${log.clock_in || '-'}</td>
                        <td>${log.clock_out || '-'}</td>
                        <td><span class="badge bg-primary">${log.total_hours}h</span></td>
                        <td>${log.tasks_completed || 0}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-info" onclick="viewDetails(${log.id})">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');

                count.textContent = data.total || data.timeLogs.length;
                renderPagination(data.current_page || 1, data.total_pages || 1);
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No time logs found</td></tr>';
                count.textContent = '0';
                pagination.innerHTML = '';
            }
        })
        .catch(() => {
            // Demo data fallback
            const demo = [
                {id:1, employee_name:'Alice Johnson', date:'2024-01-15', clock_in:'09:00', clock_out:'17:30', total_hours:'8.5', tasks_completed:5},
                {id:2, employee_name:'Bob Smith', date:'2024-01-15', clock_in:'08:45', clock_out:'17:00', total_hours:'8.25', tasks_completed:3},
                {id:3, employee_name:'Carol Lee', date:'2024-01-15', clock_in:'09:15', clock_out:'18:00', total_hours:'8.75', tasks_completed:4}
            ];
            
            const tbody = document.getElementById('timeLogsTableBody');
            tbody.innerHTML = demo.map(log => `
                <tr>
                    <td class="fw-medium">${log.employee_name}</td>
                    <td>${log.date}</td>
                    <td>${log.clock_in}</td>
                    <td>${log.clock_out}</td>
                    <td><span class="badge bg-primary">${log.total_hours}h</span></td>
                    <td>${log.tasks_completed}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-info" onclick="viewDetails(${log.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
            
            document.getElementById('timeLogCount').textContent = demo.length;
            renderPagination(1, 1);
        });
}

function updateChart(period) {
    currentPeriod = period;
    document.getElementById(`chart${period.charAt(0).toUpperCase() + period.slice(1)}`).checked = true;
    
    const employee = document.getElementById('employeeFilter').value;
    const startDate = document.getElementById('startDateFilter').value;
    const endDate = document.getElementById('endDateFilter').value;

    const params = new URLSearchParams({
        period,
        employee_id: employee,
        start_date: startDate,
        end_date: endDate
    });

    fetch(`/admin/employees/time/chart?${params.toString()}`)
        .then(r => r.json())
        .then(data => {
            const ctx = document.getElementById('hoursChart');
            if (hoursChart) hoursChart.destroy();
            
            hoursChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: 'Hours Worked',
                        data: data.data || [],
                        backgroundColor: 'rgba(78, 84, 200, 0.8)',
                        borderColor: 'rgba(78, 84, 200, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Hours'
                            }
                        }
                    }
                }
            });
        })
        .catch(() => {
            // Demo data fallback
            const ctx = document.getElementById('hoursChart');
            if (hoursChart) hoursChart.destroy();
            
            const demoData = {
                day: { labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], data: [8, 7.5, 8.5, 8, 7] },
                week: { labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'], data: [39, 38.5, 40, 37] },
                month: { labels: ['Jan', 'Feb', 'Mar'], data: [160, 152, 168] },
                year: { labels: ['2022', '2023', '2024'], data: [1920, 1980, 800] }
            };
            
            hoursChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: demoData[period].labels,
                    datasets: [{
                        label: 'Hours Worked',
                        data: demoData[period].data,
                        backgroundColor: 'rgba(78, 84, 200, 0.8)',
                        borderColor: 'rgba(78, 84, 200, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Hours'
                            }
                        }
                    }
                }
            });
        });
}

function renderPagination(current, total) {
    const el = document.getElementById('timeLogsPagination');
    el.innerHTML = '';
    if (total <= 1) return;
    
    if (current > 1) {
        el.innerHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadEmployeeTime(${current-1})">Prev</a></li>`;
    }
    
    for (let i = 1; i <= total; i++) {
        el.innerHTML += `<li class="page-item ${i===current?'active':''}"><a class="page-link" href="#" onclick="loadEmployeeTime(${i})">${i}</a></li>`;
    }
    
    if (current < total) {
        el.innerHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadEmployeeTime(${current+1})">Next</a></li>`;
    }
}

function viewDetails(id) {
    fetch(`/admin/employees/time/${id}/details`)
        .then(r => r.json())
        .then(data => {
            const content = document.getElementById('timeDetailsContent');
            if (data.success && data.details) {
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Employee Information</h6>
                            <p><strong>Name:</strong> ${data.details.employee_name}</p>
                            <p><strong>Email:</strong> ${data.details.employee_email}</p>
                            <p><strong>Date:</strong> ${data.details.date}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Time Information</h6>
                            <p><strong>Clock In:</strong> ${data.details.clock_in || 'N/A'}</p>
                            <p><strong>Clock Out:</strong> ${data.details.clock_out || 'N/A'}</p>
                            <p><strong>Total Hours:</strong> ${data.details.total_hours || 0} hours</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h6>Tasks Completed</h6>
                        <div class="list-group">
                            ${(data.details.tasks || []).map(task => `
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">${task.title}</h6>
                                        <small>${task.completed_at}</small>
                                    </div>
                                    <p class="mb-1">${task.description}</p>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = '<div class="alert alert-warning">No details available</div>';
            }
            
            new bootstrap.Modal(document.getElementById('timeDetailsModal')).show();
        })
        .catch(() => {
            // Demo details
            const content = document.getElementById('timeDetailsContent');
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Employee Information</h6>
                        <p><strong>Name:</strong> Demo Employee</p>
                        <p><strong>Email:</strong> demo@example.com</p>
                        <p><strong>Date:</strong> 2024-01-15</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Time Information</h6>
                        <p><strong>Clock In:</strong> 09:00 AM</p>
                        <p><strong>Clock Out:</strong> 05:30 PM</p>
                        <p><strong>Total Hours:</strong> 8.5 hours</p>
                    </div>
                </div>
                <div class="mt-3">
                    <h6>Tasks Completed</h6>
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Update user interface</h6>
                                <small>11:30 AM</small>
                            </div>
                            <p class="mb-1">Fixed navigation issues and improved responsive design</p>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Code review</h6>
                                <small>02:15 PM</small>
                            </div>
                            <p class="mb-1">Reviewed pull requests and provided feedback</p>
                        </div>
                    </div>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('timeDetailsModal')).show();
        });
}

function exportData() {
    const employee = document.getElementById('employeeFilter').value;
    const startDate = document.getElementById('startDateFilter').value;
    const endDate = document.getElementById('endDateFilter').value;

    const params = new URLSearchParams({
        employee_id: employee,
        start_date: startDate,
        end_date: endDate,
        export: 'csv'
    });

    window.open(`/admin/employees/time/export?${params.toString()}`, '_blank');
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadEmployees();
    loadEmployeeTime();
    updateChart('week');
});
</script>
@endpush
@endsection
