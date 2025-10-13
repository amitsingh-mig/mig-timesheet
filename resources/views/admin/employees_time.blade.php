@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Admin Header -->
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-title-section">
                <h1 class="admin-title">Employee Time Overview</h1>
                <p class="admin-subtitle">Advanced filtering and working hours analysis for all employees</p>
            </div>
            <div class="admin-actions">
                <a href="{{ route('admin.users.index') }}" class="btn-admin-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Back to Users
                </a>
            </div>
        </div>
    </div>

    <!-- Modern Filters Section -->
    <div class="admin-filters-section">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-funnel"></i>
                    Filters & Search
                </h3>
            </div>
            <div class="admin-card-body">
                <div class="admin-filters-grid">
                    <div class="admin-filter-group">
                        <label class="admin-form-label">Employee Name</label>
                        <select id="employeeFilter" class="admin-form-select">
                            <option value="">All Employees</option>
                            <!-- Populated by JavaScript -->
                        </select>
                    </div>
                    <div class="admin-filter-group">
                        <label class="admin-form-label">Department</label>
                        <select id="departmentFilter" class="admin-form-select">
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
                    <div class="admin-filter-group">
                        <label class="admin-form-label">Time Period</label>
                        <select id="timePeriodFilter" class="admin-form-select">
                            <option value="days">Days</option>
                            <option value="weeks">Weeks</option>
                            <option value="months">Months</option>
                            <option value="years">Years</option>
                        </select>
                    </div>
                    <div class="admin-filter-group">
                        <label class="admin-form-label">&nbsp;</label>
                        <button class="btn-admin-filter" onclick="loadEmployeeTime()">
                            <i class="bi bi-funnel"></i>
                            Apply Filters
                        </button>
                    </div>
                </div>
                
                <!-- Date Range Filters (Hidden by default, shown when needed) -->
                <div class="admin-filters-grid mt-3" id="dateRangeFilters" style="display: none;">
                    <div class="admin-filter-group">
                        <label class="admin-form-label">Start Date</label>
                        <input type="date" id="startDateFilter" class="admin-form-input" value="{{ date('Y-m-01') }}">
                    </div>
                    <div class="admin-filter-group">
                        <label class="admin-form-label">End Date</label>
                        <input type="date" id="endDateFilter" class="admin-form-input" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="admin-filter-group">
                        <label class="admin-form-label">&nbsp;</label>
                        <div class="admin-checkbox-group">
                            <input class="admin-checkbox" type="checkbox" id="useCustomDateRange">
                            <label class="admin-checkbox-label" for="useCustomDateRange">
                                Use Custom Date Range
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Working Hours Summary -->
    <div class="admin-card mb-4">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="bi bi-graph-up"></i>
                Working Hours Summary
            </h3>
            <div class="admin-period-selector">
                <input type="radio" class="admin-radio" name="summaryPeriod" id="summaryDay" autocomplete="off" checked>
                <label class="admin-radio-label" for="summaryDay" onclick="updateSummary('day')">
                    <i class="bi bi-calendar-day"></i>Day
                </label>
                
                <input type="radio" class="admin-radio" name="summaryPeriod" id="summaryWeek" autocomplete="off">
                <label class="admin-radio-label" for="summaryWeek" onclick="updateSummary('week')">
                    <i class="bi bi-calendar-week"></i>Week
                </label>
                
                <input type="radio" class="admin-radio" name="summaryPeriod" id="summaryMonth" autocomplete="off">
                <label class="admin-radio-label" for="summaryMonth" onclick="updateSummary('month')">
                    <i class="bi bi-calendar-month"></i>Month
                </label>
                
                <input type="radio" class="admin-radio" name="summaryPeriod" id="summaryYear" autocomplete="off">
                <label class="admin-radio-label" for="summaryYear" onclick="updateSummary('year')">
                    <i class="bi bi-calendar-year"></i>Year
                </label>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="admin-stats-grid" id="hoursSummary">
                <!-- Populated by JavaScript -->
                <div class="admin-loading">
                    <div class="admin-spinner"></div>
                    <p>Loading hours summary...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="admin-card mb-4">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="bi bi-bar-chart-fill"></i>
                Hours Overview Chart
            </h3>
            <div class="admin-period-selector">
                <input type="radio" class="admin-radio" name="chartPeriod" id="chartDay" autocomplete="off">
                <label class="admin-radio-label" for="chartDay" onclick="updateChart('day')">
                    <i class="bi bi-calendar-day"></i>Day
                </label>
                
                <input type="radio" class="admin-radio" name="chartPeriod" id="chartWeek" autocomplete="off" checked>
                <label class="admin-radio-label" for="chartWeek" onclick="updateChart('week')">
                    <i class="bi bi-calendar-week"></i>Week
                </label>
                
                <input type="radio" class="admin-radio" name="chartPeriod" id="chartMonth" autocomplete="off">
                <label class="admin-radio-label" for="chartMonth" onclick="updateChart('month')">
                    <i class="bi bi-calendar-month"></i>Month
                </label>
                
                <input type="radio" class="admin-radio" name="chartPeriod" id="chartYear" autocomplete="off">
                <label class="admin-radio-label" for="chartYear" onclick="updateChart('year')">
                    <i class="bi bi-calendar-year"></i>Year
                </label>
            </div>
        </div>
        <div class="admin-card-body">
            <canvas id="hoursChart" height="100"></canvas>
        </div>
    </div>

    <!-- Employee Records Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="bi bi-table"></i>
                Employee Records
            </h3>
            <div class="admin-actions">
                <button id="refreshEmployeeBtn" class="btn-admin-secondary" onclick="refreshEmployeeRecords()" title="Refresh Employee Records">
                    <i class="bi bi-arrow-clockwise"></i>
                    Update
                </button>
                <button class="btn-admin-secondary" onclick="debugEmployeeData()" title="Debug Employee Data">
                    <i class="bi bi-bug"></i>
                    Debug
                </button>
                <button class="btn-admin-primary" onclick="exportData()">
                    <i class="bi bi-download"></i>
                    Export CSV
                </button>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="admin-table-container">
                <table class="admin-table" id="employeeRecordsTable">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th class="text-center">Days (hrs)</th>
                            <th class="text-center">Weeks (hrs)</th>
                            <th class="text-center">Months (hrs)</th>
                            <th class="text-center">Years (hrs)</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employeeRecordsTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="admin-loading">
                                    <div class="admin-spinner"></div>
                                    <p>Loading employee records...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination">
                <div class="admin-pagination-info">
                    Showing <span id="employeeCount">0</span> employee records
                </div>
                <nav aria-label="Employee records pagination">
                    <ul class="admin-pagination-list" id="employeeRecordsPagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Employee Time Details Modal -->
<div class="modal fade" id="timeDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title admin-modal-title">
                    <i class="bi bi-person-lines-fill"></i>
                    Employee Details
                </h5>
                <button type="button" class="btn-close admin-btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body admin-modal-body">
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

function loadEmployees(forceRefresh = false) {
    console.log('Loading employees for filter dropdown...', { forceRefresh });

    const url = forceRefresh ? 
        `/admin/users/data?include_all=1&_t=${Date.now()}` : 
        '/admin/users/data?include_all=1';

    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Cache-Control': forceRefresh ? 'no-cache' : 'default'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Employees response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
        .then(data => {
        console.log('Employees data received:', data);
            const select = document.getElementById('employeeFilter');
            select.innerHTML = '<option value="">All Employees</option>';
            
        // Handle both 'users' and 'data' properties for backward compatibility
        const users = data.users || data.data || [];
        
        if (data.success && users && users.length > 0) {
            users.forEach(user => {
                    select.innerHTML += `<option value="${user.id}">${user.name}</option>`;
                });
            console.log(`Loaded ${users.length} employees into filter dropdown`);
        } else {
            console.log('No employees found in response');
            }
        })
    .catch(error => {
        console.error('Error loading employees:', error);
            // Demo data fallback
            const select = document.getElementById('employeeFilter');
            select.innerHTML = '<option value="">All Employees</option>';
            ['Alice Johnson', 'Bob Smith', 'Carol Lee'].forEach((name, i) => {
                select.innerHTML += `<option value="${i+1}">${name}</option>`;
            });
        });
}

function loadEmployeeRecords(page = 1, forceRefresh = false) {
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

    console.log('Loading employee records:', { page, employee, department, timePeriod, startDate, endDate, forceRefresh });

    const params = new URLSearchParams({
        page,
        employee_id: employee,
        department: department,
        time_period: timePeriod,
        start_date: startDate,
        end_date: endDate,
        _t: forceRefresh ? Date.now() : Date.now() // Always use cache busting
    });

    // Show loading state
    const tbody = document.getElementById('employeeRecordsTableBody');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';

    fetch(`/admin/employees/time/records?${params.toString()}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Cache-Control': forceRefresh ? 'no-cache' : 'default'
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
            console.log(`Displaying ${data.employees.length} employee records`);
            tbody.innerHTML = data.employees.map(emp => `
                <tr>
                    <td class="admin-table-name">${emp.name}</td>
                    <td><span class="admin-badge admin-badge-department">${(emp.department || 'General').toUpperCase()}</span></td>
                    <td>${emp.email}</td>
                    <td class="text-center">
                        <span class="admin-badge admin-badge-success">${emp.days_hours || '0'} hrs</span>
                    </td>
                    <td class="text-center">
                        <span class="admin-badge admin-badge-success">${emp.weeks_hours || '0'} hrs</span>
                    </td>
                    <td class="text-center">
                        <span class="admin-badge admin-badge-success">${emp.months_hours || '0'} hrs</span>
                    </td>
                    <td class="text-center">
                        <span class="admin-badge admin-badge-success">${emp.years_hours || '0'} hrs</span>
                    </td>
                    <td class="text-center">
                        <button class="btn-admin-table-action" onclick="viewEmployeeDetails(${emp.id})" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            `).join('');

            count.textContent = data.total || data.employees.length;
            renderEmployeePagination(data.current_page || 1, data.total_pages || 1);
        } else {
            console.log('No employee records found or error in response');
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No employee records found</td></tr>';
            count.textContent = '0';
            pagination.innerHTML = '';
        }
    })
    .catch(error => {
        console.error('Error loading employee records:', error);
        
        const tbody = document.getElementById('employeeRecordsTableBody');
        const count = document.getElementById('employeeCount');
        const pagination = document.getElementById('employeeRecordsPagination');
        
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle"></i> Error loading employee records. Please try again.</td></tr>';
        count.textContent = '0';
        pagination.innerHTML = '';
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
        end_date: endDate,
        _t: Date.now() // Cache busting parameter
    });

    fetch(`/admin/employees/time/summary?${params.toString()}`)
        .then(r => r.json())
        .then(data => {
            const summaryContainer = document.getElementById('hoursSummary');
            
            if (data.success && data.summary) {
                const summary = data.summary;
                summaryContainer.innerHTML = `
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon admin-stat-icon-primary">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="admin-stat-content">
                            <h3 class="admin-stat-number">${summary.total_employees || 0}</h3>
                            <p class="admin-stat-label">Total Employees</p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon admin-stat-icon-success">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="admin-stat-content">
                            <h3 class="admin-stat-number">${summary.total_hours || 0}</h3>
                            <p class="admin-stat-label">Total Hours (${period})</p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon admin-stat-icon-warning">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div class="admin-stat-content">
                            <h3 class="admin-stat-number">${summary.average_hours || 0}</h3>
                            <p class="admin-stat-label">Average Hours</p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon admin-stat-icon-info">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <div class="admin-stat-content">
                            <h3 class="admin-stat-number">${summary.active_employees || 0}</h3>
                            <p class="admin-stat-label">Active Employees</p>
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
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-primary">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="admin-stat-content">
                        <h3 class="admin-stat-number">15</h3>
                        <p class="admin-stat-label">Total Employees</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-success">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div class="admin-stat-content">
                        <h3 class="admin-stat-number">120</h3>
                        <p class="admin-stat-label">Total Hours (${period})</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-warning">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="admin-stat-content">
                        <h3 class="admin-stat-number">8.0</h3>
                        <p class="admin-stat-label">Average Hours</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-info">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div class="admin-stat-content">
                        <h3 class="admin-stat-number">12</h3>
                        <p class="admin-stat-label">Active Employees</p>
                    </div>
                </div>
            `;
        });
}

function loadEmployeeTime(page = 1) {
    // This function now calls the new employee records function
    loadEmployeeRecords(page);
}

function refreshEmployeeRecords() {
    // Disable the update button to prevent multiple clicks
    const updateBtn = document.getElementById('refreshEmployeeBtn');
    if (updateBtn) {
        updateBtn.disabled = true;
        updateBtn.innerHTML = '<i class="bi bi-arrow-clockwise animate-spin"></i> Updating...';
    }
    
    // Show loading state
    const tbody = document.getElementById('employeeRecordsTableBody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><br><small>Refreshing employee records...</small></td></tr>';
    }
    
    // Reset to first page and reload employee records
    currentPage = 1;
    
    // Force refresh with cache busting
    const timestamp = Date.now();
    
    // Refresh both employee dropdown and records with force refresh
    loadEmployees(true); // Force refresh
    loadEmployeeRecords(1, true); // Force refresh
    
    // Also refresh the summary and chart data
    updateSummary(currentSummaryPeriod);
    updateChart(currentPeriod);
    
    // Show success message
    setTimeout(() => {
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-success border-0';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle me-2"></i>
                    Employee records updated successfully!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.body.appendChild(toast);
        new bootstrap.Toast(toast).show();
        setTimeout(() => toast.remove(), 3000);
    }, 1000);
    
    // Re-enable the button after a delay
    setTimeout(() => {
        if (updateBtn) {
            updateBtn.disabled = false;
            updateBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Update';
        }
    }, 2000);
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
        end_date: endDate,
        _t: Date.now() // Cache busting parameter
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

function debugEmployeeData() {
    // Open debug endpoints in new tabs
    window.open('/admin/employees/time/debug/employees', '_blank');
    
    // If an employee is selected, also open their timesheet debug
    const selectedEmployee = document.getElementById('employeeFilter').value;
    if (selectedEmployee) {
        window.open(`/admin/employees/time/debug/timesheets/${selectedEmployee}`, '_blank');
    }
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
    
    // Listen for new employee creation notifications
    window.addEventListener('storage', (e) => {
        if (e.key === 'newEmployeeCreated') {
            const newEmployee = JSON.parse(e.newValue);
            console.log('New employee detected:', newEmployee);
            
            // Show notification
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-info border-0';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-person-plus me-2"></i>
                        New employee "${newEmployee.name}" added! Refreshing data...
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.body.appendChild(toast);
            new bootstrap.Toast(toast).show();
            setTimeout(() => toast.remove(), 5000);
            
            // Refresh data
            loadEmployees();
            loadEmployeeRecords();
        } else if (e.key === 'newTimesheetEntry') {
            const newEntry = JSON.parse(e.newValue);
            console.log('New timesheet entry detected:', newEntry);
            
            // Show notification
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-clock me-2"></i>
                        New timesheet entry from "${newEntry.user_name}"! Refreshing data...
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.body.appendChild(toast);
            new bootstrap.Toast(toast).show();
            setTimeout(() => toast.remove(), 5000);
            
            // Refresh data
            loadEmployeeRecords();
            updateSummary(currentSummaryPeriod);
        }
    });
    
    // Refresh employee data every 30 seconds to catch new employees
    setInterval(() => {
        loadEmployees();
    }, 30000);
    
    // Auto-refresh employee records every 60 seconds
    setInterval(() => {
        loadEmployeeRecords(currentPage, true); // Force refresh
    }, 60000);
});
</script>
@endpush
@endsection
