@extends('layouts.app')

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea, #764ba2) !important;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #42a5f5, #1e88e5) !important;
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffa726, #ff9800) !important;
}

.bg-gradient-success {
    background: linear-gradient(135deg, #4ecdc4, #44a08d) !important;
}

.bg-info-modern {
    background: linear-gradient(135deg, #42a5f5, #1e88e5) !important;
    color: white !important;
}

.bg-primary-modern {
    background: linear-gradient(135deg, #667eea, #764ba2) !important;
    color: white !important;
}

.bg-success-modern {
    background: linear-gradient(135deg, #4ecdc4, #44a08d) !important;
    color: white !important;
}

.bg-warning-modern {
    background: linear-gradient(135deg, #ffa726, #ff9800) !important;
    color: white !important;
}

.bg-danger-modern {
    background: linear-gradient(135deg, #ff6b6b, #ee5a24) !important;
    color: white !important;
}

.btn-info-modern {
    background: linear-gradient(135deg, #42a5f5, #1e88e5) !important;
    color: white !important;
    border: none !important;
}

.btn-info-modern:hover {
    background: linear-gradient(135deg, #64b5f6, #42a5f5) !important;
    color: white !important;
}

.avatar-fallback {
    background: linear-gradient(135deg, #667eea, #764ba2) !important;
    color: white !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-weight: 600 !important;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3) !important;
}

.table tbody tr:hover {
    background-color: rgba(102, 126, 234, 0.05) !important;
    transform: translateY(-1px) !important;
    transition: all 0.3s ease !important;
}

.badge {
    font-weight: 500 !important;
    border-radius: 8px !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
}

.form-select.rounded-input, .form-control.rounded-input {
    border-radius: 12px !important;
    border: 2px solid #e5e7eb !important;
    transition: all 0.3s ease !important;
}

.form-select.rounded-input:focus, .form-control.rounded-input:focus {
    border-color: #667eea !important;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15) !important;
}

.btn-modern {
    border-radius: 12px !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

.btn-modern:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2) !important;
}

.card.hover-lift {
    transition: all 0.3s ease !important;
}

.card.hover-lift:hover {
    transform: translateY(-4px) !important;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15) !important;
}

.pagination .page-link {
    border-radius: 8px !important;
    margin: 0 2px !important;
    border: 1px solid #e5e7eb !important;
    color: #667eea !important;
    font-weight: 500 !important;
}

.pagination .page-link:hover {
    background-color: #667eea !important;
    color: white !important;
    transform: translateY(-1px) !important;
}

.pagination .page-item.active .page-link {
    background-color: #667eea !important;
    border-color: #667eea !important;
    color: white !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">
                <i class="bi bi-people-fill me-2 text-primary"></i>Employee Records Management
            </h1>
            <p class="text-muted mb-0">Advanced filtering and working hours analysis for all employees</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-modern">
                <i class="bi bi-arrow-left me-2"></i>Back to Users
            </a>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="card border-0 shadow-sm mb-4 hover-lift">
        <div class="card-header bg-gradient-primary text-white">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-funnel-fill me-2"></i>Advanced Employee Records Filter
            </h6>
        </div>
        <div class="card-body bg-light">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold text-dark">
                        <i class="bi bi-person me-1"></i>Employee Name
                    </label>
                    <select id="employeeFilter" class="form-select rounded-input">
                        <option value="">All Employees</option>
                        <!-- Populated by JavaScript -->
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold text-dark">
                        <i class="bi bi-building me-1"></i>Department
                    </label>
                    <select id="departmentFilter" class="form-select rounded-input">
                        <option value="">All Departments</option>
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
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold text-dark">
                        <i class="bi bi-calendar-range me-1"></i>Time Period
                    </label>
                    <select id="timePeriodFilter" class="form-select rounded-input">
                        <option value="days">Days</option>
                        <option value="weeks">Weeks</option>
                        <option value="months">Months</option>
                        <option value="years">Years</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary-modern btn-modern" onclick="loadEmployeeTime()">
                            <i class="bi bi-funnel-fill me-2"></i>Apply Filters
                        </button>
                        <button class="btn btn-success-modern btn-modern" onclick="exportData()">
                            <i class="bi bi-download me-2"></i>Export CSV
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Date Range Filters (Hidden by default, shown when needed) -->
            <div class="row g-3 align-items-end mt-4" id="dateRangeFilters" style="display: none;">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark">
                        <i class="bi bi-calendar-event me-1"></i>Start Date
                    </label>
                    <input type="date" id="startDateFilter" class="form-control rounded-input" value="{{ date('Y-m-01') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark">
                        <i class="bi bi-calendar-check me-1"></i>End Date
                    </label>
                    <input type="date" id="endDateFilter" class="form-control rounded-input" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="useCustomDateRange">
                        <label class="form-check-label fw-semibold text-dark" for="useCustomDateRange">
                            <i class="bi bi-calendar-range me-1"></i>Use Custom Date Range
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Working Hours Summary -->
    <div class="card border-0 shadow-sm mb-4 hover-lift">
        <div class="card-header bg-gradient-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-graph-up me-2"></i>Working Hours Summary
            </h5>
            <div class="btn-group btn-group-sm" role="group">
                <input type="radio" class="btn-check" name="summaryPeriod" id="summaryDay" autocomplete="off" checked>
                <label class="btn btn-outline-light" for="summaryDay" onclick="updateSummary('day')">
                    <i class="bi bi-calendar-day me-1"></i>Day
                </label>
                
                <input type="radio" class="btn-check" name="summaryPeriod" id="summaryWeek" autocomplete="off">
                <label class="btn btn-outline-light" for="summaryWeek" onclick="updateSummary('week')">
                    <i class="bi bi-calendar-week me-1"></i>Week
                </label>
                
                <input type="radio" class="btn-check" name="summaryPeriod" id="summaryMonth" autocomplete="off">
                <label class="btn btn-outline-light" for="summaryMonth" onclick="updateSummary('month')">
                    <i class="bi bi-calendar-month me-1"></i>Month
                </label>
                
                <input type="radio" class="btn-check" name="summaryPeriod" id="summaryYear" autocomplete="off">
                <label class="btn btn-outline-light" for="summaryYear" onclick="updateSummary('year')">
                    <i class="bi bi-calendar-year me-1"></i>Year
                </label>
            </div>
        </div>
        <div class="card-body bg-light">
            <div class="row" id="hoursSummary">
                <!-- Populated by JavaScript -->
                <div class="col-12 text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 text-muted fw-medium">Loading hours summary...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="card border-0 shadow-sm mb-4 hover-lift">
        <div class="card-header bg-gradient-warning text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-bar-chart-fill me-2"></i>Hours Overview Chart
            </h5>
            <div class="btn-group btn-group-sm" role="group">
                <input type="radio" class="btn-check" name="chartPeriod" id="chartDay" autocomplete="off">
                <label class="btn btn-outline-light" for="chartDay" onclick="updateChart('day')">
                    <i class="bi bi-calendar-day me-1"></i>Day
                </label>
                
                <input type="radio" class="btn-check" name="chartPeriod" id="chartWeek" autocomplete="off" checked>
                <label class="btn btn-outline-light" for="chartWeek" onclick="updateChart('week')">
                    <i class="bi bi-calendar-week me-1"></i>Week
                </label>
                
                <input type="radio" class="btn-check" name="chartPeriod" id="chartMonth" autocomplete="off">
                <label class="btn btn-outline-light" for="chartMonth" onclick="updateChart('month')">
                    <i class="bi bi-calendar-month me-1"></i>Month
                </label>
                
                <input type="radio" class="btn-check" name="chartPeriod" id="chartYear" autocomplete="off">
                <label class="btn btn-outline-light" for="chartYear" onclick="updateChart('year')">
                    <i class="bi bi-calendar-year me-1"></i>Year
                </label>
            </div>
        </div>
        <div class="card-body bg-light">
            <canvas id="hoursChart" height="100"></canvas>
        </div>
    </div>

    <!-- Employee Records Table -->
    <div class="card border-0 shadow-sm hover-lift">
        <div class="card-header bg-gradient-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-table me-2"></i>Employee Records
            </h5>
            <div>
                <button class="btn btn-light btn-modern" onclick="exportData()">
                    <i class="bi bi-download me-2"></i>Export CSV
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="fw-semibold">
                                <i class="bi bi-person me-1"></i>Employee Name
                            </th>
                            <th class="fw-semibold">
                                <i class="bi bi-building me-1"></i>Department
                            </th>
                            <th class="fw-semibold">
                                <i class="bi bi-envelope me-1"></i>Email
                            </th>
                            <th class="fw-semibold text-center">
                                <i class="bi bi-calendar-day me-1"></i>Days (hrs)
                            </th>
                            <th class="fw-semibold text-center">
                                <i class="bi bi-calendar-week me-1"></i>Weeks (hrs)
                            </th>
                            <th class="fw-semibold text-center">
                                <i class="bi bi-calendar-month me-1"></i>Months (hrs)
                            </th>
                            <th class="fw-semibold text-center">
                                <i class="bi bi-calendar-year me-1"></i>Years (hrs)
                            </th>
                            <th class="fw-semibold text-center">
                                <i class="bi bi-gear me-1"></i>Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody id="employeeRecordsTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-3 text-muted fw-medium">Loading employee records...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light d-flex justify-content-between align-items-center">
            <small class="text-muted fw-medium">
                <i class="bi bi-info-circle me-1"></i>Showing <span id="employeeCount" class="fw-bold text-primary">0</span> employee records
            </small>
            <nav aria-label="Employee records pagination">
                <ul class="pagination pagination-sm mb-0" id="employeeRecordsPagination"></ul>
            </nav>
        </div>
    </div>
</div>

<!-- Employee Time Details Modal -->
<div class="modal fade" id="timeDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title text-white fw-semibold">
                    <i class="bi bi-person-lines-fill me-2"></i>Employee Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
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
let currentSummaryPeriod = 'day';
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

function loadEmployeeRecords(page = 1) {
    currentPage = page;
    const employee = document.getElementById('employeeFilter').value;
    const department = document.getElementById('departmentFilter').value;
    const timePeriod = document.getElementById('timePeriodFilter').value;
    const useCustomRange = document.getElementById('useCustomDateRange').checked;
    
    let startDate = null;
    let endDate = null;
    
    if (useCustomRange) {
        startDate = document.getElementById('startDateFilter').value;
        endDate = document.getElementById('endDateFilter').value;
    }

    console.log('Loading employee records:', { page, employee, department, timePeriod, startDate, endDate });

    const params = new URLSearchParams({
        page,
        employee_id: employee,
        department: department,
        time_period: timePeriod,
        start_date: startDate,
        end_date: endDate
    });

    // Show loading state
    const tbody = document.getElementById('employeeRecordsTableBody');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';

    fetch(`/admin/employees/time/records?${params.toString()}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Employee records response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Employee records data received:', data);
        
        const tbody = document.getElementById('employeeRecordsTableBody');
        const count = document.getElementById('employeeCount');
        const pagination = document.getElementById('employeeRecordsPagination');

        if (data.success && data.employees && data.employees.length > 0) {
            tbody.innerHTML = data.employees.map(emp => `
                <tr class="hover-scale">
                    <td class="fw-semibold">
                        <div class="d-flex align-items-center">
                            <div class="avatar-fallback me-2" style="width: 32px; height: 32px; font-size: 12px;">
                                ${emp.name.split(' ').map(n => n[0]).join('').toUpperCase()}
                            </div>
                            ${emp.name}
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-info-modern px-3 py-2">
                            <i class="bi bi-building me-1"></i>${emp.department || 'General'}
                        </span>
                    </td>
                    <td class="text-muted">${emp.email}</td>
                    <td class="text-center">
                        <span class="badge bg-primary-modern px-3 py-2">
                            <i class="bi bi-calendar-day me-1"></i>${emp.days_hours || '0'} hrs
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success-modern px-3 py-2">
                            <i class="bi bi-calendar-week me-1"></i>${emp.weeks_hours || '0'} hrs
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-warning-modern px-3 py-2">
                            <i class="bi bi-calendar-month me-1"></i>${emp.months_hours || '0'} hrs
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-danger-modern px-3 py-2">
                            <i class="bi bi-calendar-year me-1"></i>${emp.years_hours || '0'} hrs
                        </span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-info-modern btn-modern btn-sm" onclick="viewEmployeeDetails(${emp.id})" title="View Details">
                            <i class="bi bi-eye-fill me-1"></i>View
                        </button>
                    </td>
                </tr>
            `).join('');

            count.textContent = data.total || data.employees.length;
            renderEmployeePagination(data.current_page || 1, data.total_pages || 1);
        } else {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No employee records found</td></tr>';
            count.textContent = '0';
            pagination.innerHTML = '';
        }
    })
    .catch(() => {
        // Demo data fallback
        const demo = [
            {id:1, name:'Alice Johnson', department:'Web', email:'alice@example.com', days_hours:'8', weeks_hours:'42', months_hours:'168', years_hours:'2016'},
            {id:2, name:'Bob Smith', department:'Graphic', email:'bob@example.com', days_hours:'7.5', weeks_hours:'37.5', months_hours:'150', years_hours:'1800'},
            {id:3, name:'Carol Lee', department:'Editorial', email:'carol@example.com', days_hours:'8.5', weeks_hours:'42.5', months_hours:'170', years_hours:'2040'}
        ];
        
        const tbody = document.getElementById('employeeRecordsTableBody');
        tbody.innerHTML = demo.map(emp => `
            <tr class="hover-scale">
                <td class="fw-semibold">
                    <div class="d-flex align-items-center">
                        <div class="avatar-fallback me-2" style="width: 32px; height: 32px; font-size: 12px;">
                            ${emp.name.split(' ').map(n => n[0]).join('').toUpperCase()}
                        </div>
                        ${emp.name}
                    </div>
                </td>
                <td>
                    <span class="badge bg-info-modern px-3 py-2">
                        <i class="bi bi-building me-1"></i>${emp.department}
                    </span>
                </td>
                <td class="text-muted">${emp.email}</td>
                <td class="text-center">
                    <span class="badge bg-primary-modern px-3 py-2">
                        <i class="bi bi-calendar-day me-1"></i>${emp.days_hours} hrs
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge bg-success-modern px-3 py-2">
                        <i class="bi bi-calendar-week me-1"></i>${emp.weeks_hours} hrs
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge bg-warning-modern px-3 py-2">
                        <i class="bi bi-calendar-month me-1"></i>${emp.months_hours} hrs
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge bg-danger-modern px-3 py-2">
                        <i class="bi bi-calendar-year me-1"></i>${emp.years_hours} hrs
                    </span>
                </td>
                <td class="text-center">
                    <button class="btn btn-info-modern btn-modern btn-sm" onclick="viewEmployeeDetails(${emp.id})" title="View Details">
                        <i class="bi bi-eye-fill me-1"></i>View
                    </button>
                </td>
            </tr>
        `).join('');
        
        document.getElementById('employeeCount').textContent = demo.length;
        renderEmployeePagination(1, 1);
    });
}

function updateSummary(period) {
    currentSummaryPeriod = period;
    document.getElementById(`summary${period.charAt(0).toUpperCase() + period.slice(1)}`).checked = true;
    
    const employee = document.getElementById('employeeFilter').value;
    const department = document.getElementById('departmentFilter').value;
    const timePeriod = document.getElementById('timePeriodFilter').value;
    const useCustomRange = document.getElementById('useCustomDateRange').checked;
    
    let startDate = null;
    let endDate = null;
    
    if (useCustomRange) {
        startDate = document.getElementById('startDateFilter').value;
        endDate = document.getElementById('endDateFilter').value;
    }

    const params = new URLSearchParams({
        period,
        employee_id: employee,
        department: department,
        time_period: timePeriod,
        start_date: startDate,
        end_date: endDate
    });

    fetch(`/admin/employees/time/summary?${params.toString()}`)
        .then(r => r.json())
        .then(data => {
            const summaryContainer = document.getElementById('hoursSummary');
            
            if (data.success && data.summary) {
                const summary = data.summary;
                summaryContainer.innerHTML = `
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1">${summary.total_employees || 0}</h3>
                                <p class="mb-0">Total Employees</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1">${summary.total_hours || 0}</h3>
                                <p class="mb-0">Total Hours (${period})</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1">${summary.average_hours || 0}</h3>
                                <p class="mb-0">Average Hours</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1">${summary.active_employees || 0}</h3>
                                <p class="mb-0">Active Employees</p>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                summaryContainer.innerHTML = '<div class="col-12 text-center text-muted">No summary data available</div>';
            }
        })
        .catch(() => {
            // Demo data fallback
            const summaryContainer = document.getElementById('hoursSummary');
            summaryContainer.innerHTML = `
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-1">15</h3>
                            <p class="mb-0">Total Employees</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-1">120</h3>
                            <p class="mb-0">Total Hours (${period})</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-1">8.0</h3>
                            <p class="mb-0">Average Hours</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-1">12</h3>
                            <p class="mb-0">Active Employees</p>
                        </div>
                    </div>
                </div>
            `;
        });
}

function loadEmployeeTime(page = 1) {
    // This function now calls the new employee records function
    loadEmployeeRecords(page);
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

function renderEmployeePagination(current, total) {
    const el = document.getElementById('employeeRecordsPagination');
    el.innerHTML = '';
    if (total <= 1) return;
    
    if (current > 1) {
        el.innerHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadEmployeeRecords(${current-1})">Prev</a></li>`;
    }
    
    for (let i = 1; i <= total; i++) {
        el.innerHTML += `<li class="page-item ${i===current?'active':''}"><a class="page-link" href="#" onclick="loadEmployeeRecords(${i})">${i}</a></li>`;
    }
    
    if (current < total) {
        el.innerHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadEmployeeRecords(${current+1})">Next</a></li>`;
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

function viewEmployeeDetails(id) {
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
                            <p><strong>Department:</strong> ${data.details.department || 'General'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Working Hours Summary</h6>
                            <p><strong>Today:</strong> ${data.details.today_hours || 0} hours</p>
                            <p><strong>This Week:</strong> ${data.details.week_hours || 0} hours</p>
                            <p><strong>This Month:</strong> ${data.details.month_hours || 0} hours</p>
                            <p><strong>This Year:</strong> ${data.details.year_hours || 0} hours</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h6>Recent Activity</h6>
                        <div class="list-group">
                            ${(data.details.recent_activity || []).map(activity => `
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">${activity.date}</h6>
                                        <small>${activity.hours} hours</small>
                                    </div>
                                    <p class="mb-1">${activity.description || 'No description'}</p>
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
                        <p><strong>Department:</strong> Web Development</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Working Hours Summary</h6>
                        <p><strong>Today:</strong> 8.5 hours</p>
                        <p><strong>This Week:</strong> 42 hours</p>
                        <p><strong>This Month:</strong> 168 hours</p>
                        <p><strong>This Year:</strong> 2016 hours</p>
                    </div>
                </div>
                <div class="mt-3">
                    <h6>Recent Activity</h6>
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">2024-01-15</h6>
                                <small>8.5 hours</small>
                            </div>
                            <p class="mb-1">Completed user interface updates and code review</p>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">2024-01-14</h6>
                                <small>8.0 hours</small>
                            </div>
                            <p class="mb-1">Database optimization and testing</p>
                        </div>
                    </div>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('timeDetailsModal')).show();
        });
}

function exportData() {
    const employee = document.getElementById('employeeFilter').value;
    const department = document.getElementById('departmentFilter').value;
    const timePeriod = document.getElementById('timePeriodFilter').value;
    const useCustomRange = document.getElementById('useCustomDateRange').checked;
    
    let startDate = null;
    let endDate = null;
    
    if (useCustomRange) {
        startDate = document.getElementById('startDateFilter').value;
        endDate = document.getElementById('endDateFilter').value;
    }

    const params = new URLSearchParams({
        employee_id: employee,
        department: department,
        time_period: timePeriod,
        start_date: startDate,
        end_date: endDate,
        export: 'csv'
    });

    window.open(`/admin/employees/time/export?${params.toString()}`, '_blank');
}

// Toggle custom date range visibility
function toggleCustomDateRange() {
    const checkbox = document.getElementById('useCustomDateRange');
    const dateRangeFilters = document.getElementById('dateRangeFilters');
    
    if (checkbox.checked) {
        dateRangeFilters.style.display = 'block';
    } else {
        dateRangeFilters.style.display = 'none';
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadEmployees();
    loadEmployeeRecords();
    updateSummary('day');
    updateChart('week');
    
    // Add event listener for custom date range checkbox
    document.getElementById('useCustomDateRange').addEventListener('change', toggleCustomDateRange);
});
</script>
@endpush
@endsection
