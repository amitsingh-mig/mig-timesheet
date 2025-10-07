@extends('layouts.app')

@section('title', 'Admin Timesheet Calendar')

@section('content')
    <div class="container-fluid">
        <!-- Enhanced Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-1">
                            <i class="bi bi-calendar3 me-2 text-primary"></i>Admin Timesheet Calendar
                        </h1>
                        <p class="text-muted mb-0">Comprehensive employee working time management and monitoring</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-success" onclick="exportData()">
                            <i class="bi bi-download me-1"></i> Export Data
                        </button>
                        <button class="btn btn-primary" onclick="refreshCalendar()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                        </button>
                        <button class="btn btn-info" onclick="showHelp()">
                            <i class="bi bi-question-circle me-1"></i> Help
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics Dashboard -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="bi bi-people-fill text-primary fs-4 me-2"></i>
                            <h5 class="card-title text-primary mb-0" id="totalEmployees">0</h5>
                        </div>
                        <p class="card-text small text-muted mb-0">Total Employees</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="bi bi-clock-fill text-success fs-4 me-2"></i>
                            <h5 class="card-title text-success mb-0" id="totalHours">0h</h5>
                        </div>
                        <p class="card-text small text-muted mb-0">Total Hours</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="bi bi-check-circle-fill text-success fs-4 me-2"></i>
                            <h5 class="card-title text-success mb-0" id="approvedCount">0</h5>
                        </div>
                        <p class="card-text small text-muted mb-0">Approved</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="bi bi-hourglass-split text-warning fs-4 me-2"></i>
                            <h5 class="card-title text-warning mb-0" id="pendingCount">0</h5>
                        </div>
                        <p class="card-text small text-muted mb-0">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="bi bi-x-circle-fill text-danger fs-4 me-2"></i>
                            <h5 class="card-title text-danger mb-0" id="rejectedCount">0</h5>
                        </div>
                        <p class="card-text small text-muted mb-0">Rejected</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-4 me-2"></i>
                            <h5 class="card-title text-warning mb-0" id="overtimeCount">0</h5>
                        </div>
                        <p class="card-text small text-muted mb-0">Overtime Entries</p>
                        <div class="mt-1">
                            <small class="text-warning" id="overtimeHours">0h OT</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Performance Overview -->
        <div class="row mb-4" id="employeeOverviewSection" style="display: none;">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                        <h6 class="mb-0">
                            <i class="bi bi-people-fill me-2"></i>Employee Performance Overview
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="employeeOverviewContent">
                            <!-- Employee breakdown will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Filter Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters & Controls</h6>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-person-fill me-1"></i>Employee Filter
                        </label>
                        <select id="userFilter" class="form-select form-select-lg">
                            <option value="">All Employees</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-calendar-range me-1"></i>View Mode
                        </label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="viewMode" id="viewDaily" value="day">
                            <label class="btn btn-outline-primary" for="viewDaily">
                                <i class="bi bi-calendar-day me-1"></i>Daily
                            </label>

                            <input type="radio" class="btn-check" name="viewMode" id="viewWeekly" value="week">
                            <label class="btn btn-outline-primary" for="viewWeekly">
                                <i class="bi bi-calendar-week me-1"></i>Weekly
                            </label>

                            <input type="radio" class="btn-check" name="viewMode" id="viewMonthly" value="month" checked>
                            <label class="btn btn-outline-primary" for="viewMonthly">
                                <i class="bi bi-calendar-month me-1"></i>Monthly
                            </label>

                            <input type="radio" class="btn-check" name="viewMode" id="viewYearly" value="year">
                            <label class="btn btn-outline-primary" for="viewYearly">
                                <i class="bi bi-calendar-year me-1"></i>Yearly
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-calendar3 me-1"></i>Period Navigation
                        </label>
                        <div class="d-flex gap-1">
                            <button class="btn btn-outline-secondary" onclick="navigateMonth(-1)" title="Previous">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <span id="currentPeriod" class="btn btn-light flex-grow-1">{{ now()->format('M Y') }}</span>
                            <button class="btn btn-outline-secondary" onclick="navigateMonth(1)" title="Next">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-gear me-1"></i>Quick Actions
                        </label>
                        <div class="d-flex gap-1">
                            <button class="btn btn-success btn-sm" onclick="applyFilters()">
                                <i class="bi bi-funnel me-1"></i>Apply
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                                <i class="bi bi-x-circle me-1"></i>Clear
                        </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Enhanced Calendar Display -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="bi bi-calendar3 me-2"></i>Calendar View
                        </h5>
                        <small class="opacity-75">Employee working time by <span id="viewModeText">month</span></small>
                    </div>
                    <div id="bulkActions" class="d-none">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-check2-square me-1"></i>Selected: <strong id="selectedCount">0</strong>
                            </span>
                            <button class="btn btn-sm btn-success" onclick="bulkApprove()">
                                <i class="bi bi-check-all me-1"></i> Approve All
                        </button>
                            <button class="btn btn-sm btn-danger" onclick="bulkReject()">
                                <i class="bi bi-x-circle me-1"></i> Reject All
                        </button>
                            <button class="btn btn-sm btn-outline-light" onclick="clearSelection()">
                                <i class="bi bi-x me-1"></i> Clear
                        </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="calendarContainer" class="position-relative">
                    <!-- Loading overlay -->
                    <div id="loadingOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75" style="z-index: 10;">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                            <div class="text-muted">Loading calendar data...</div>
                    </div>
                    </div>
                    <!-- Calendar content will be loaded here -->
                </div>
            </div>
        </div>

    </div>

    <!-- Enhanced Day Details Modal -->
    <div class="modal fade" id="dayDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-day me-2"></i>
                        <span id="modalDateTitle">Day Details</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="dayDetailsContent">
                        <!-- Content will be loaded here -->
                        </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="actionModalTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="actionForm">
                    <div class="modal-body">
                        <div id="actionModalContent">
                            <div class="mb-3">
                                <label class="form-label">Reason (Optional)</label>
                                <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason for this action..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="actionConfirmBtn">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .calendar-day {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .calendar-day:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .calendar-day.has-data {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-left: 4px solid #2196f3;
        }
        
        .calendar-day.has-overtime {
            background: linear-gradient(135deg, #fff3e0 0%, #ffcc02 100%);
            border-left: 4px solid #ff9800;
        }
        
        .calendar-day.has-pending {
            background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
            border-left: 4px solid #9c27b0;
        }
        
        .calendar-day.has-rejected {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            border-left: 4px solid #f44336;
        }
        
        .avatar-sm {
            font-weight: 600;
        }
        
        .statistics-card {
            transition: all 0.3s ease;
        }
        
        .statistics-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        
        .gradient-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .btn-group .btn {
            border-radius: 0.375rem;
        }
        
        .btn-group .btn:not(:last-child) {
            margin-right: 2px;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }
        
        .badge {
            font-size: 0.75em;
        }
        
        .loading-overlay {
            backdrop-filter: blur(2px);
        }
        
        .calendar-container {
            min-height: 500px;
        }
        
        .view-mode-btn {
            transition: all 0.2s ease;
        }
        
        .view-mode-btn:hover {
            transform: scale(1.05);
        }
        
        .filter-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .overtime-indicator {
            background: linear-gradient(135deg, #fff3e0 0%, #ffcc02 100%);
            border-left: 4px solid #ff9800;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.8; }
            100% { opacity: 1; }
        }
        
        .overtime-badge {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
            font-weight: 600;
        }
        
        .overtime-hours {
            color: #ff9800;
            font-weight: 600;
        }
        
        .calendar-day.has-overtime {
            background: linear-gradient(135deg, #fff3e0 0%, #ffcc02 100%);
            border-left: 4px solid #ff9800;
            position: relative;
        }
        
        .calendar-day.has-overtime::after {
            content: "⚠️";
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 12px;
        }
    </style>

@endsection

@push('scripts')
    <script>
        // Global variables
        let currentMonth = 10; // October
        let currentYear = 2024; // Year with test data
        let currentView = 'month';
        let selectedTimesheets = new Set();
        let calendarData = [];

        // Initialize the calendar
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing calendar...');
            initializeCalendar();
            loadUsers();
            setupEventListeners();
            // Initialize view mode active state
            updateViewModeActive();
        });

        function initializeCalendar() {
            loadCalendarData();
            loadStatistics();
        }

        function setupEventListeners() {
            // View mode change
            document.querySelectorAll('input[name="viewMode"]').forEach(input => {
                input.addEventListener('change', function() {
                    currentView = this.value;
                    updateViewModeText();
                    loadCalendarData();
                    updateViewModeActive();
                });
            });

            // Action form submission
            document.getElementById('actionForm').addEventListener('submit', handleActionSubmit);
        }

        // Render day summary with working time data
        function renderDaySummary(summary) {
            let html = '<div class="small">';
            
            // Total hours with overtime breakdown
            if (summary.overtime_hours > 0) {
                html += `<div class="mb-1">
                    <strong>${summary.total_hours.toFixed(1)}h</strong> total
                    <br><small class="text-info">${summary.overtime_hours.toFixed(1)}h OT</small>
                </div>`;
            } else {
                html += `<div class="mb-1"><strong>${summary.total_hours.toFixed(1)}h</strong> total</div>`;
            }
            
            // Status counts
            html += `<div class="mb-1"><span class="text-success fw-bold">${summary.approved_count}</span> ✅</div>`;
            html += `<div class="mb-1"><span class="text-warning fw-bold">${summary.pending_count}</span> ⏳</div>`;
            html += `<div class="mb-1"><span class="text-danger fw-bold">${summary.rejected_count}</span> ❌</div>`;
            
            // Overtime indicator
            if (summary.overtime_count > 0) {
                html += `<div class="mb-1">
                    <span class="badge bg-warning text-dark">${summary.overtime_count} OT</span>
                </div>`;
            }
            
            html += '</div>';
            return html;
        }

        function loadUsers() {
            fetch('/admin/users/employees', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.data) {
                    const userSelect = document.getElementById('userFilter');
                    userSelect.innerHTML = '<option value="">All Employees</option>';
                    data.data.forEach(user => {
                        userSelect.innerHTML += `<option value="${user.id}">${user.name} (${user.email})</option>`;
                    });
                    console.log(`Loaded ${data.data.length} employees for filter`);
                } else {
                    console.error('Failed to load employees:', data.message || 'Unknown error');
                    // Show fallback message
                    const userSelect = document.getElementById('userFilter');
                    userSelect.innerHTML = '<option value="">All Employees</option><option value="" disabled>Failed to load employees</option>';
                }
            })
            .catch(error => {
                console.error('Error loading employees:', error);
                // Try fallback to original endpoint
                console.log('Trying fallback endpoint...');
            fetch('/admin/users/data')
                .then(response => response.json())
                .then(data => {
                        if (data.success && data.data) {
                        const userSelect = document.getElementById('userFilter');
                        userSelect.innerHTML = '<option value="">All Employees</option>';
                            data.data.forEach(user => {
                            if (user.role !== 'admin') {
                                userSelect.innerHTML += `<option value="${user.id}">${user.name}</option>`;
                            }
                        });
                            console.log(`Fallback: Loaded ${data.data.length} employees`);
                        } else {
                            throw new Error('Fallback also failed');
                        }
                    })
                    .catch(fallbackError => {
                        console.error('Fallback also failed:', fallbackError);
                        const userSelect = document.getElementById('userFilter');
                        userSelect.innerHTML = '<option value="">All Employees</option><option value="" disabled>Error loading employees</option>';
                    });
            });
        }

        function loadCalendarData() {
            showLoading();
            const params = new URLSearchParams({
                month: currentMonth,
                year: currentYear,
                view: currentView,
                user_id: document.getElementById('userFilter').value
            });

            fetch(`/admin/timesheet-calendar/data?${params}`)
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        calendarData = data.calendar_data;
                        updatePeriodDisplay();

                        if (!data.calendar_data || data.calendar_data.length === 0) {
                            document.getElementById('calendarContainer').innerHTML = `
                                <div class="p-5 text-center text-muted">
                                    <i class="bi bi-calendar-x fs-1 mb-3"></i>
                                    <h5>No timesheets found</h5>
                                    <p>No timesheet entries found for the selected period and filters.</p>
                                </div>`;
                            return;
                        }

                        renderCalendar(data.calendar_data);
                        updateStatisticsFromCalendar(data.calendar_data);
                        
                        // Update statistics with comprehensive employee data
                        if (data.employee_statistics) {
                            updateComprehensiveStatistics(data.employee_statistics);
                        }
                        
                        // Load additional employee time data
                        loadEmployeeTimeData();
                    } else {
                        showError('Failed to load calendar data: ' + data.message);
                        renderCalendar([]);
                        updatePeriodDisplay();
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error loading calendar:', error);
                    showError('Failed to load calendar data');
                    renderCalendar([]);
                });
        }

        function renderCalendar(data) {
            const container = document.getElementById('calendarContainer');

            if (currentView === 'month') {
                renderMonthlyCalendar(data);
            } else if (currentView === 'week') {
                renderWeeklyCalendar(data);
            } else if (currentView === 'day') {
                renderDailyCalendar(data);
            } else if (currentView === 'year') {
                renderYearlyCalendar(data);
            } else {
                renderMonthlyCalendar(data); // Default to monthly view
            }
        }

        function renderWeeklyCalendar(data) {
            const container = document.getElementById('calendarContainer');
            const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

            let html = '<div class="table-responsive">';
            html += '<table class="table table-bordered mb-0">';

            // Header
            html += '<thead class="table-dark"><tr>';
            daysOfWeek.forEach(day => {
                html += `<th class="text-center p-2">${day}</th>`;
            });
            html += '</tr></thead><tbody><tr>';

            // Get current week dates
            const today = new Date();
            const startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay());

            for (let i = 0; i < 7; i++) {
                const currentDate = new Date(startOfWeek);
                currentDate.setDate(startOfWeek.getDate() + i);
                const dateStr = currentDate.toISOString().split('T')[0];
                const dayData = data.find(d => d.date === dateStr);
                const isToday = dateStr === new Date().toISOString().split('T')[0];

                html += `<td class="p-2 align-top calendar-cell ${isToday ? 'bg-light' : ''}" 
                         onclick="showDayDetails('${dateStr}')" style="height: 150px; cursor: pointer;">`;
                html += `<div class="fw-bold mb-1">${currentDate.getDate()}</div>`;

                if (dayData && dayData.summary) {
                    html += renderDaySummary(dayData.summary);
                }

                html += '</td>';
            }

            html += '</tr></tbody></table></div>';
            container.innerHTML = html;
        }

        function renderDailyCalendar(data) {
            const container = document.getElementById('calendarContainer');
            const today = new Date();
            const dateStr = today.toISOString().split('T')[0];
            const dayData = data.find(d => d.date === dateStr);

            let html = '<div class="row">';
            html += '<div class="col-12">';
            html += '<div class="card">';
            html += '<div class="card-header">';
            html += `<h5 class="mb-0">Today - ${today.toLocaleDateString()}</h5>`;
            html += '</div>';
            html += '<div class="card-body">';

            if (dayData && dayData.timesheets && dayData.timesheets.length > 0) {
                html += '<div class="table-responsive">';
                html += '<table class="table table-hover">';
                html += '<thead><tr>';
                html += '<th>Employee</th><th>Hours</th><th>Status</th><th>Description</th><th>Actions</th>';
                html += '</tr></thead><tbody>';

                dayData.timesheets.forEach(timesheet => {
                    const statusBadge = getStatusBadge(timesheet.status, timesheet.is_overtime);
                    html += '<tr>';
                    html += `<td>${timesheet.user_name}</td>`;
                    html += `<td>${timesheet.hours}h</td>`;
                    html += `<td>${statusBadge}</td>`;
                    html += `<td>${timesheet.description || '-'}</td>`;
                    html += '<td>';
                    if (timesheet.status === 'pending') {
                        html += `<button class="btn btn-sm btn-success me-1" onclick="approveTimesheet(${timesheet.id})">
                                    <i class="bi bi-check"></i>
                                </button>`;
                        html += `<button class="btn btn-sm btn-danger" onclick="rejectTimesheet(${timesheet.id})">
                                    <i class="bi bi-x"></i>
                                </button>`;
                    } else {
                        html += `<button class="btn btn-sm btn-outline-info" onclick="viewTimesheetDetails(${timesheet.id})">
                                    <i class="bi bi-eye"></i>
                                </button>`;
                    }
                    html += '</td></tr>';
                });

                html += '</tbody></table></div>';
            } else {
                html += '<div class="text-center py-4">';
                html += '<i class="bi bi-calendar-x fs-1 text-muted"></i>';
                html += '<p class="text-muted mt-2">No timesheet entries for today</p>';
                html += '</div>';
            }

            html += '</div></div></div></div>';
            container.innerHTML = html;
        }

        function renderYearlyCalendar(data) {
            const container = document.getElementById('calendarContainer');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            let html = '<div class="row">';
            
            for (let month = 1; month <= 12; month++) {
                const monthData = data.filter(d => {
                    const date = new Date(d.date);
                    return date.getMonth() === month - 1;
                });
                
                const totalHours = monthData.reduce((sum, day) => sum + (day.summary?.total_hours || 0), 0);
                const totalEntries = monthData.reduce((sum, day) => sum + (day.timesheets?.length || 0), 0);
                
                html += `
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0">${months[month - 1]} ${currentYear}</h6>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="h4 text-primary">${totalHours.toFixed(1)}h</div>
                                    <div class="small text-muted">${totalEntries} entries</div>
                                </div>
                                <div class="mt-2">
                                    <div class="d-flex justify-content-between small">
                                        <span>✅ ${monthData.reduce((sum, day) => sum + (day.summary?.approved_count || 0), 0)}</span>
                                        <span>⏳ ${monthData.reduce((sum, day) => sum + (day.summary?.pending_count || 0), 0)}</span>
                                        <span>❌ ${monthData.reduce((sum, day) => sum + (day.summary?.rejected_count || 0), 0)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            html += '</div>';
            container.innerHTML = html;
        }

        // Toggle active styles for view mode labels
        function updateViewModeActive() {
            const modes = ['day', 'week', 'month', 'year']; // Added 'year' here
            modes.forEach(mode => {
                const radio = document.getElementById('view' + (mode === 'day' ? 'Daily' : mode === 'week' ?
                    'Weekly' : mode === 'month' ? 'Monthly' : 'Yearly'));
                const label = document.querySelector(`label[for="${radio.id}"]`);
                if (radio && label) {
                    if (radio.checked) { // Check against radio.checked is usually enough
                        label.classList.remove('btn-outline-primary');
                        label.classList.add('btn-primary');
                    } else {
                        label.classList.add('btn-outline-primary');
                        label.classList.remove('btn-primary');
                    }
                }
            });
        }

        function renderMonthlyCalendar(data) {
            const container = document.getElementById('calendarContainer');
            const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

            let html = '<div class="table-responsive">';
            html += '<table class="table table-bordered mb-0">';

            // Header
            html += '<thead class="table-dark"><tr>';
            daysOfWeek.forEach(day => {
                html += `<th class="text-center p-2">${day}</th>`;
            });
            html += '</tr></thead><tbody>';

            // Create calendar grid
            const firstDay = new Date(currentYear, currentMonth - 1, 1);
            const lastDay = new Date(currentYear, currentMonth, 0);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay());

            let currentDate = new Date(startDate);

            while (currentDate <= lastDay || currentDate.getDay() !== 0) {
                html += '<tr>';
                for (let i = 0; i < 7; i++) {
                    const dateStr = currentDate.toISOString().split('T')[0];
                    const dayData = data.find(d => d.date === dateStr);
                    const isCurrentMonth = currentDate.getMonth() === currentMonth - 1;
                    const isToday = dateStr === new Date().toISOString().split('T')[0];

                    let cellClass = `p-2 align-top calendar-cell ${isCurrentMonth ? '' : 'text-muted'} ${isToday ? 'bg-light' : ''}`;
                    if (dayData && dayData.summary && dayData.summary.overtime_count > 0) {
                        cellClass += ' has-overtime';
                    }
                    
                    html += `<td class="${cellClass}" 
                             onclick="showDayDetails('${dateStr}')" style="height: 120px; cursor: pointer;">`;
                    html += `<div class="fw-bold mb-1">${currentDate.getDate()}</div>`;

                    // Display working time data
                    if (dayData && dayData.summary) {
                        html += renderDaySummary(dayData.summary);
                    } else {
                        html += '<div class="small text-muted">No data</div>';
                    }

                    html += '</td>';
                    currentDate.setDate(currentDate.getDate() + 1);
                }
                html += '</tr>';
            }

            html += '</tbody></table></div>';
            container.innerHTML = html;
        }


        function showDayDetails(date) {
            fetch(`/admin/timesheet-calendar/day/${date}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('modalDateTitle').textContent =
                            `Day Details - ${new Date(date).toLocaleDateString()}`;
                        renderDayDetailsModal(data);
                        new bootstrap.Modal(document.getElementById('dayDetailsModal')).show();
                    }
                })
                .catch(error => {
                    console.error('Error loading day details:', error);
                    showError('Failed to load day details');
                });
        }

        function renderDayDetailsModal(data) {
            const container = document.getElementById('dayDetailsContent');

            if (!data.timesheets || data.timesheets.length === 0) {
                container.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                <p class="text-muted mt-2">No timesheet entries for this day</p>
            </div>
        `;
                return;
            }

            let html = `
        <div class="p-4">
        <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAllDay" onchange="selectAllDayEntries(this.checked)" class="form-check-input">
                            </th>
                            <th><i class="bi bi-person me-1"></i>Employee</th>
                            <th><i class="bi bi-clock me-1"></i>Time Range</th>
                            <th><i class="bi bi-hourglass me-1"></i>Hours</th>
                            <th><i class="bi bi-flag me-1"></i>Status</th>
                            <th><i class="bi bi-geo-alt me-1"></i>Location</th>
                            <th><i class="bi bi-file-text me-1"></i>Description</th>
                            <th width="120"><i class="bi bi-gear me-1"></i>Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;

            data.timesheets.forEach(timesheet => {
                const statusBadge = getStatusBadge(timesheet.status, timesheet.is_overtime);
                const timeRange = timesheet.start_time && timesheet.end_time 
                    ? `${timesheet.start_time} - ${timesheet.end_time}` 
                    : (timesheet.start_time || timesheet.end_time || '-');
                
                html += `
            <tr>
                <td>
                    <input type="checkbox" class="timesheet-select form-check-input" value="${timesheet.id}" onchange="updateSelection()">
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 12px;">
                            ${timesheet.user_name.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="fw-medium">${timesheet.user_name}</div>
                            <small class="text-muted">${timesheet.user_email}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <small class="text-muted">${timeRange}</small>
                </td>
                <td>
                    ${timesheet.is_overtime ? `
                        <div class="d-flex flex-column">
                            <span class="badge bg-primary mb-1">${timesheet.hours}h</span>
                            <small class="text-warning">
                                <i class="bi bi-clock-history me-1"></i>
                                ${timesheet.overtime_hours}h OT
                            </small>
                        </div>
                    ` : `
                        <span class="badge bg-primary">${timesheet.hours}h</span>
                    `}
                </td>
                <td>${statusBadge}</td>
                <td>
                    <small class="text-muted">${timesheet.location || '-'}</small>
                </td>
                <td>
                    <div class="text-truncate" style="max-width: 200px;" title="${timesheet.description}">
                        ${timesheet.description || '-'}
                    </div>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        ${timesheet.status === 'submitted' ? `
                            <button class="btn btn-sm btn-success" onclick="approveTimesheet(${timesheet.id})" title="Approve">
                                                <i class="bi bi-check"></i>
                                            </button>
                            <button class="btn btn-sm btn-danger" onclick="rejectTimesheet(${timesheet.id})" title="Reject">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        ` : `
                            <button class="btn btn-sm btn-outline-info" onclick="viewTimesheetDetails(${timesheet.id})" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        `}
                    </div>
                </td>
            </tr>
        `;
            });

            html += '</tbody></table></div>';

            // Add enhanced summary
            html += `
        <div class="mt-4 p-4 bg-light rounded-3">
            <h6 class="mb-3"><i class="bi bi-bar-chart me-2"></i>Day Summary</h6>
            <div class="row g-3 text-center">
                <div class="col-md-2">
                    <div class="p-3 bg-white rounded shadow-sm">
                        <div class="h4 text-primary mb-1">${data.summary.total_entries}</div>
                        <small class="text-muted">Total Entries</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="p-3 bg-white rounded shadow-sm">
                        <div class="h4 text-warning mb-1">${data.summary.pending_count}</div>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="p-3 bg-white rounded shadow-sm">
                        <div class="h4 text-success mb-1">${data.summary.approved_count}</div>
                        <small class="text-muted">Approved</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="p-3 bg-white rounded shadow-sm">
                        <div class="h4 text-danger mb-1">${data.summary.rejected_count}</div>
                        <small class="text-muted">Rejected</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="p-3 bg-white rounded shadow-sm">
                        <div class="h4 text-info mb-1">${data.summary.overtime_count}</div>
                        <small class="text-muted">Overtime</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="p-3 bg-white rounded shadow-sm">
                        <div class="h4 text-primary mb-1">${data.summary.total_hours.toFixed(1)}h</div>
                        <small class="text-muted">Total Hours</small>
                        ${data.summary.overtime_hours > 0 ? `
                            <div class="mt-1">
                                <small class="text-warning">
                                    <i class="bi bi-clock-history me-1"></i>
                                    ${data.summary.overtime_hours.toFixed(1)}h OT
                                </small>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            </div>
        </div>
    `;

            container.innerHTML = html;
        }

        function getStatusBadge(status, isOvertime) {
            let badge = '';
            switch (status) {
                case 'draft':
                    badge = '<span class="badge bg-secondary">📝 Draft</span>';
                    break;
                case 'submitted':
                    badge = '<span class="badge bg-warning">⏳ Pending</span>';
                    break;
                case 'approved':
                    badge = '<span class="badge bg-success">✅ Approved</span>';
                    break;
                case 'rejected':
                    badge = '<span class="badge bg-danger">❌ Rejected</span>';
                    break;
                default:
                    badge = '<span class="badge bg-light text-dark">❓ ' + (status || 'Unknown') + '</span>';
            }

            if (isOvertime) {
                badge += ' <span class="badge bg-info">⚠️ OT</span>';
            }

            return badge;
        }


        // Utility functions
        function navigateMonth(direction) {
            currentMonth += direction;
            if (currentMonth > 12) {
                currentMonth = 1;
                currentYear++;
            } else if (currentMonth < 1) {
                currentMonth = 12;
                currentYear--;
            }
            loadCalendarData();
        }

        function updatePeriodDisplay() {
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ];
            document.getElementById('currentPeriod').textContent =
                `${monthNames[currentMonth - 1]} ${currentYear}`;
        }

        function applyFilters() {
            loadCalendarData();
        }

        function clearFilters() {
            document.getElementById('userFilter').value = '';
            loadCalendarData();
            showSuccess('Filters cleared successfully');
        }

        function refreshCalendar() {
            loadCalendarData();
            showSuccess('Calendar refreshed successfully');
        }

        function showLoading() {
            document.getElementById('loadingOverlay').classList.remove('d-none');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.add('d-none');
        }

        function updateViewModeText() {
            const viewModeText = document.getElementById('viewModeText');
            const modeTexts = {
                'day': 'day',
                'week': 'week', 
                'month': 'month',
                'year': 'year'
            };
            viewModeText.textContent = modeTexts[currentView] || 'month';
        }

        function loadStatistics() {
            const params = new URLSearchParams({
                start_date: `${currentYear}-${String(currentMonth).padStart(2, '0')}-01`,
                end_date: new Date(currentYear, currentMonth, 0).toISOString().split('T')[0]
            });

            fetch(`/admin/timesheet-calendar/stats?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStatisticsDisplay(data.statistics);
                    }
                })
                .catch(error => console.error('Error loading statistics:', error));
        }

        function updateStatisticsDisplay(stats) {
            document.getElementById('totalEmployees').textContent = stats.unique_employees || 0;
            document.getElementById('totalHours').textContent = (stats.total_hours || 0).toFixed(1) + 'h';
            document.getElementById('approvedCount').textContent = stats.approved_count || 0;
            document.getElementById('pendingCount').textContent = stats.pending_count || 0;
            document.getElementById('rejectedCount').textContent = stats.rejected_count || 0;
            document.getElementById('overtimeCount').textContent = stats.overtime_entries || 0;
        }

        function updateStatisticsFromCalendar(calendarData) {
            let totalHours = 0;
            let totalOvertimeHours = 0;
            let totalEmployees = new Set();
            let approvedCount = 0;
            let pendingCount = 0;
            let rejectedCount = 0;
            let overtimeCount = 0;

            calendarData.forEach(day => {
                if (day.summary) {
                    totalHours += day.summary.total_hours || 0;
                    totalOvertimeHours += day.summary.overtime_hours || 0;
                    approvedCount += day.summary.approved_count || 0;
                    pendingCount += day.summary.pending_count || 0;
                    rejectedCount += day.summary.rejected_count || 0;
                    overtimeCount += day.summary.overtime_count || 0;
                }
                if (day.timesheets) {
                    day.timesheets.forEach(timesheet => {
                        totalEmployees.add(timesheet.user_id);
                    });
                }
            });

            document.getElementById('totalEmployees').textContent = totalEmployees.size;
            document.getElementById('totalHours').textContent = totalHours.toFixed(1) + 'h';
            document.getElementById('approvedCount').textContent = approvedCount;
            document.getElementById('pendingCount').textContent = pendingCount;
            document.getElementById('rejectedCount').textContent = rejectedCount;
            document.getElementById('overtimeCount').textContent = overtimeCount;
            document.getElementById('overtimeHours').textContent = totalOvertimeHours.toFixed(1) + 'h OT';
        }

        function updateComprehensiveStatistics(employeeStats) {
            // Update main statistics cards
            document.getElementById('totalEmployees').textContent = employeeStats.unique_employees || 0;
            document.getElementById('totalHours').textContent = (employeeStats.total_hours || 0).toFixed(1) + 'h';
            document.getElementById('approvedCount').textContent = employeeStats.status_breakdown?.approved || 0;
            document.getElementById('pendingCount').textContent = employeeStats.status_breakdown?.pending || 0;
            document.getElementById('rejectedCount').textContent = employeeStats.status_breakdown?.rejected || 0;
            document.getElementById('overtimeCount').textContent = employeeStats.overtime_entries || 0;
            document.getElementById('overtimeHours').textContent = (employeeStats.overtime_hours || 0).toFixed(1) + 'h OT';
            
            // Store employee breakdown for potential use
            window.currentEmployeeBreakdown = employeeStats.employee_breakdown || [];
            
            // Render employee overview if there are employees
            if (employeeStats.employee_breakdown && employeeStats.employee_breakdown.length > 0) {
                renderEmployeeOverview(employeeStats.employee_breakdown);
            }
            
            console.log('Updated comprehensive statistics:', employeeStats);
        }

        function renderEmployeeOverview(employeeBreakdown) {
            const container = document.getElementById('employeeOverviewContent');
            const section = document.getElementById('employeeOverviewSection');
            
            if (!employeeBreakdown || employeeBreakdown.length === 0) {
                section.style.display = 'none';
                return;
            }
            
            let html = '<div class="row g-3">';
            
            employeeBreakdown.forEach(employee => {
                const overtimePercentage = employee.total_hours > 0 ? 
                    ((employee.overtime_hours / employee.total_hours) * 100).toFixed(1) : 0;
                
                html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        ${employee.name.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <h6 class="mb-0">${employee.name}</h6>
                                        <small class="text-muted">${employee.email}</small>
                                    </div>
                                </div>
                                
                                <div class="row g-2 text-center">
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded">
                                            <div class="h6 text-primary mb-0">${employee.total_hours}h</div>
                                            <small class="text-muted">Total</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded">
                                            <div class="h6 text-success mb-0">${employee.regular_hours}h</div>
                                            <small class="text-muted">Regular</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded">
                                            <div class="h6 text-warning mb-0">${employee.overtime_hours}h</div>
                                            <small class="text-muted">Overtime</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Entries: ${employee.entries_count}</span>
                                        <span>OT: ${employee.overtime_entries}</span>
                                    </div>
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-success" style="width: ${100 - overtimePercentage}%"></div>
                                        <div class="progress-bar bg-warning" style="width: ${overtimePercentage}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
            section.style.display = 'block';
        }

        function loadEmployeeTimeData() {
            const params = new URLSearchParams({
                start_date: `${currentYear}-${String(currentMonth).padStart(2, '0')}-01`,
                end_date: new Date(currentYear, currentMonth, 0).toISOString().split('T')[0],
                employee_id: document.getElementById('userFilter').value
            });

            fetch(`/admin/timesheet-calendar/employee-time?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Employee time data loaded:', data.data);
                        // Store employee time data for use in calendar
                        window.employeeTimeData = data.data;
                    } else {
                        console.error('Failed to load employee time data:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading employee time data:', error);
                });
        }

        function showHelp() {
            alert('Admin Timesheet Calendar Help:\n\n' +
                  '• Use Employee Filter to view specific employee data\n' +
                  '• Switch between Daily, Weekly, Monthly, and Yearly views\n' +
                  '• Click on any day to see detailed timesheet entries\n' +
                  '• Use bulk actions to approve/reject multiple entries\n' +
                  '• Export data to CSV for external analysis\n' +
                  '• Statistics show real-time data for the selected period\n' +
                  '• Working hours are calculated from employee time records\n' +
                  '• Overtime is automatically calculated and highlighted');
        }

        function showError(message) {
            showToast(message, 'danger');
        }

        function showSuccess(message) {
            showToast(message, 'success');
        }

        function showToast(message, type) {
            const toastContainer = document.getElementById('toastContainer') || createToastContainer();

            const toastId = 'toast_' + Date.now();
            const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
            const icon = type === 'success' ? '✓' : '✗';

            const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0 mb-2" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${icon}</strong> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, {
                delay: 5000
            });
            toast.show();

            // Remove toast element after it's hidden
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }

        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1055';
            document.body.appendChild(container);
            return container;
        }

        // Approval/Rejection functions
        function approveTimesheet(id) {
            showActionModal('approve', [id], 'Approve this timesheet entry?');
        }

        function rejectTimesheet(id) {
            showActionModal('reject', [id], 'Reject this timesheet entry?');
        }

        function bulkApprove() {
            if (selectedTimesheets.size === 0) {
                showError('Please select timesheets to approve');
                return;
            }
            showActionModal('approve', Array.from(selectedTimesheets),
                `Approve ${selectedTimesheets.size} timesheet entries?`);
        }

        function bulkReject() {
            if (selectedTimesheets.size === 0) {
                showError('Please select timesheets to reject');
                return;
            }
            showActionModal('reject', Array.from(selectedTimesheets),
                `Reject ${selectedTimesheets.size} timesheet entries?`);
        }

        function showActionModal(action, timesheetIds, message) {
            const modal = document.getElementById('actionModal');
            const title = document.getElementById('actionModalTitle');
            const content = document.getElementById('actionModalContent');
            const confirmBtn = document.getElementById('actionConfirmBtn');

            title.textContent = action === 'approve' ? 'Confirm Approval' : 'Confirm Rejection';
            content.innerHTML = `
        <div class="mb-3">
            <p class="fw-medium">${message}</p>
            <label class="form-label">${action === 'reject' ? 'Reason (Required)' : 'Reason (Optional)'}</label>
            <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason for this action..." ${action === 'reject' ? 'required' : ''}></textarea>
        </div>
    `;

            confirmBtn.textContent = action === 'approve' ? 'Approve' : 'Reject';
            confirmBtn.className = `btn ${action === 'approve' ? 'btn-success' : 'btn-danger'}`;

            // Store action data for form submission
            modal.dataset.action = action;
            modal.dataset.timesheetIds = JSON.stringify(timesheetIds);

            new bootstrap.Modal(modal).show();
        }

        function handleActionSubmit(e) {
            e.preventDefault();

            const modal = document.getElementById('actionModal');
            const action = modal.dataset.action;
            const timesheetIds = JSON.parse(modal.dataset.timesheetIds);
            const reason = modal.querySelector('textarea[name="reason"]').value;
            const submitBtn = document.getElementById('actionConfirmBtn');

            if (action === 'reject' && !reason.trim()) {
                showError('Reason is required for rejection');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

            const endpoint = timesheetIds.length === 1 ?
                `/admin/timesheet-calendar/${action}/${timesheetIds[0]}` :
                `/admin/timesheet-calendar/bulk-${action}`;

            const requestData = timesheetIds.length === 1 ? {
                reason
            } : {
                timesheet_ids: timesheetIds,
                reason
            };

            fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(modal).hide();
                        showSuccess(data.message);
                        loadCalendarData();
                        loadStatistics();
                        selectedTimesheets.clear();
                        updateBulkActions();
                    } else {
                        showError(data.message || `Failed to ${action} timesheet`);
                    }
                })
                .catch(error => {
                    console.error(`Error ${action}ing timesheet:`, error);
                    showError(`An error occurred while ${action}ing the timesheet`);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = action === 'approve' ? 'Approve' : 'Reject';
                    submitBtn.className = `btn ${action === 'approve' ? 'btn-success' : 'btn-danger'}`;
                });
        }

        // Selection management functions
        function updateSelection() {
            const checkboxes = document.querySelectorAll('.timesheet-select:checked');
            selectedTimesheets.clear();
            checkboxes.forEach(cb => selectedTimesheets.add(parseInt(cb.value)));
            updateBulkActions();
        }

        function selectAllDayEntries(checked) {
            const checkboxes = document.querySelectorAll('.timesheet-select');
            checkboxes.forEach(cb => {
                cb.checked = checked;
                if (checked) {
                    selectedTimesheets.add(parseInt(cb.value));
                } else {
                    selectedTimesheets.delete(parseInt(cb.value));
                }
            });
            updateBulkActions();
        }

        function updateBulkActions() {
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');

            if (selectedTimesheets.size > 0) {
                bulkActions.classList.remove('d-none');
                selectedCount.textContent = selectedTimesheets.size;
            } else {
                bulkActions.classList.add('d-none');
            }
        }

        function clearSelection() {
            selectedTimesheets.clear();
            document.querySelectorAll('.timesheet-select').forEach(cb => cb.checked = false);
            document.getElementById('selectAllDay')?.checked = false;
            updateBulkActions();
        }

        function viewTimesheetDetails(id) {
            // This could open a detailed view modal
            console.log('View timesheet details:', id);
        }

        function exportData() {
            const params = new URLSearchParams({
                month: currentMonth,
                year: currentYear,
                user_id: document.getElementById('userFilter').value,
                format: 'csv'
            });

            window.location.href = `/admin/timesheet-calendar/export?${params}`;
        }

        // 🛠️ Step 2: Removed Duplicate JS Block at the end of the script!
    </script>
@endpush