@extends('layouts.app')

@section('title', 'Admin Timesheet Calendar')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 fw-bold text-dark mb-1"><i class="bi bi-calendar3 me-2"></i>Admin Timesheet Calendar</h1>
                    <p class="text-muted mb-0">Manage and monitor all employee timesheets</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="exportData()">
                        <i class="bi bi-download"></i> Export
                    </button>
                    <button class="btn btn-primary" onclick="refreshCalendar()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Controls -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Employee Filter</label>
                    <select id="userFilter" class="form-select">
                        <option value="">All Employees</option>
                        <!-- Populated by JavaScript -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status Filter</label>
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending"><i class="bi bi-circle-fill text-warning me-1"></i>Pending</option>
                        <option value="approved"><i class="bi bi-circle-fill text-success me-1"></i>Approved</option>
                        <option value="rejected"><i class="bi bi-circle-fill text-danger me-1"></i>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">View Mode</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="viewMode" id="viewDaily" value="day">
                        <label class="btn btn-outline-primary" for="viewDaily">Daily</label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="viewWeekly" value="week">
                        <label class="btn btn-outline-primary" for="viewWeekly">Weekly</label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="viewMonthly" value="month" checked>
                        <label class="btn btn-outline-primary" for="viewMonthly">Monthly</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Month/Year</label>
                    <div class="d-flex gap-1">
                        <button class="btn btn-outline-secondary" onclick="navigateMonth(-1)">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <span id="currentPeriod" class="btn btn-light">{{ now()->format('M Y') }}</span>
                        <button class="btn btn-outline-secondary" onclick="navigateMonth(1)">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" onclick="applyFilters()">
                        <i class="bi bi-funnel"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Calendar View</h5>
                <div id="bulkActions" class="d-none">
                    <span class="me-2">Selected: <strong id="selectedCount">0</strong></span>
                    <button class="btn btn-sm btn-success me-1" onclick="bulkApprove()">
                        <i class="bi bi-check-all"></i> Approve All
                    </button>
                    <button class="btn btn-sm btn-danger me-1" onclick="bulkReject()">
                        <i class="bi bi-x-circle"></i> Reject All
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                        Clear
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div id="calendarContainer">
                <!-- Calendar will be loaded here -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Loading calendar data...</div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Day Details Modal -->
<div class="modal fade" id="dayDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%); color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-day me-2"></i>
                    <span id="modalDateTitle">Day Details</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="dayDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve/Reject Modal -->
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

@endsection

@push('scripts')
<script>
// Global variables
let currentMonth = {{ now()->month }};
let currentYear = {{ now()->year }};
let currentView = 'month';
let selectedTimesheets = new Set();
let calendarData = [];

// Initialize the calendar
document.addEventListener('DOMContentLoaded', function() {
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
            loadCalendarData();
            updateViewModeActive();
        });
    });

    // Action form submission
    document.getElementById('actionForm').addEventListener('submit', handleActionSubmit);
}

function loadUsers() {
    fetch('/admin/users/data')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.users) {
                const userSelect = document.getElementById('userFilter');
                userSelect.innerHTML = '<option value="">All Employees</option>';
                data.users.forEach(user => {
                    if (user.role !== 'admin') {
                        userSelect.innerHTML += `<option value="${user.id}">${user.name}</option>`;
                    }
                });
            }
        })
        .catch(error => console.error('Error loading users:', error));
}

function loadCalendarData() {
    const params = new URLSearchParams({
        month: currentMonth,
        year: currentYear,
        view: currentView,
        user_id: document.getElementById('userFilter').value,
        status: document.getElementById('statusFilter').value
    });

    fetch(`/admin/timesheet-calendar/data?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                calendarData = data.calendar_data;
                renderCalendar(data.calendar_data);
                updatePeriodDisplay();
                if (!data.calendar_data || data.calendar_data.length === 0) {
                    showToast('No timesheets found for this period', 'info');
                }
            } else {
                showError('Failed to load calendar data: ' + data.message);
                // Render an empty calendar to remove the loading state
                renderCalendar([]);
                updatePeriodDisplay();
            }
        })
        .catch(error => {
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
    } else {
        renderDailyCalendar(data);
    }
}

// Toggle active styles for view mode labels
function updateViewModeActive() {
    const modes = ['day','week','month'];
    modes.forEach(mode => {
        const radio = document.getElementById('view' + (mode === 'day' ? 'Daily' : mode === 'week' ? 'Weekly' : 'Monthly'));
        const label = document.querySelector(`label[for="${radio.id}"]`);
        if (radio && label) {
            if (radio.checked || currentView === mode) {
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
            
            html += `<td class="p-2 align-top calendar-cell ${isCurrentMonth ? '' : 'text-muted'} ${isToday ? 'bg-light' : ''}" 
                         onclick="showDayDetails('${dateStr}')" style="height: 120px; cursor: pointer;">`;
            html += `<div class="fw-bold mb-1">${currentDate.getDate()}</div>`;
            
            if (dayData && dayData.summary) {
                html += renderDaySummary(dayData.summary);
            }
            
            html += '</td>';
            currentDate.setDate(currentDate.getDate() + 1);
        }
        html += '</tr>';
    }
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function renderDaySummary(summary) {
    let html = '<div class="small">';
    
    if (summary.pending_count > 0) {
        html += `<div class="badge bg-warning text-dark me-1">🟡 ${summary.pending_count}</div>`;
    }
    if (summary.approved_count > 0) {
        html += `<div class="badge bg-success me-1">🟢 ${summary.approved_count}</div>`;
    }
    if (summary.rejected_count > 0) {
        html += `<div class="badge bg-danger me-1">🔴 ${summary.rejected_count}</div>`;
    }
    if (summary.overtime_count > 0) {
        html += `<div class="badge bg-info me-1">⚠️ ${summary.overtime_count}</div>`;
    }
    if (summary.missing_count > 0) {
        html += `<div class="badge bg-secondary me-1">⭕ ${summary.missing_count}</div>`;
    }
    
    if (summary.total_hours > 0) {
        html += `<div class="text-muted mt-1">${summary.total_hours}h total</div>`;
    }
    
    html += '</div>';
    return html;
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
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllDay" onchange="selectAllDayEntries(this.checked)"></th>
                        <th>Employee</th>
                        <th>Hours</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    data.timesheets.forEach(timesheet => {
        const statusBadge = getStatusBadge(timesheet.status, timesheet.is_overtime);
        html += `
            <tr>
                <td><input type="checkbox" class="timesheet-select" value="${timesheet.id}" onchange="updateSelection()"></td>
                <td class="fw-medium">${timesheet.user_name}</td>
                <td>${timesheet.hours}h</td>
                <td>${statusBadge}</td>
                <td>${timesheet.location || '-'}</td>
                <td class="text-truncate" style="max-width: 200px;" title="${timesheet.description}">${timesheet.description}</td>
                <td>
                    ${timesheet.status === 'pending' ? `
                        <button class="btn btn-sm btn-success me-1" onclick="approveTimesheet(${timesheet.id})">
                            <i class="bi bi-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="rejectTimesheet(${timesheet.id})">
                            <i class="bi bi-x"></i>
                        </button>
                    ` : `
                        <button class="btn btn-sm btn-outline-info" onclick="viewTimesheetDetails(${timesheet.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                    `}
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    
    // Add summary
    html += `
        <div class="mt-3 p-3 bg-light rounded">
            <div class="row text-center">
                <div class="col-md-2"><strong>${data.summary.total_entries}</strong><br><small>Total</small></div>
                <div class="col-md-2"><strong>${data.summary.pending_count}</strong><br><small>Pending</small></div>
                <div class="col-md-2"><strong>${data.summary.approved_count}</strong><br><small>Approved</small></div>
                <div class="col-md-2"><strong>${data.summary.rejected_count}</strong><br><small>Rejected</small></div>
                <div class="col-md-2"><strong>${data.summary.overtime_count}</strong><br><small>Overtime</small></div>
                <div class="col-md-2"><strong>${data.summary.total_hours}h</strong><br><small>Total Hours</small></div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

function getStatusBadge(status, isOvertime) {
    let badge = '';
    switch (status) {
        case 'pending':
            badge = '<span class="badge bg-warning">🟡 Pending</span>';
            break;
        case 'approved':
            badge = '<span class="badge bg-success">🟢 Approved</span>';
            break;
        case 'rejected':
            badge = '<span class="badge bg-danger">🔴 Rejected</span>';
            break;
        default:
            badge = '<span class="badge bg-secondary">Unknown</span>';
    }
    
    if (isOvertime) {
        badge += ' <span class="badge bg-info">⚠️ OT</span>';
    }
    
    return badge;
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
    document.getElementById('totalEntries').textContent = stats.total_entries || 0;
    document.getElementById('pendingCount').textContent = stats.pending_count || 0;
    document.getElementById('approvedCount').textContent = stats.approved_count || 0;
    document.getElementById('rejectedCount').textContent = stats.rejected_count || 0;
    document.getElementById('overtimeCount').textContent = stats.overtime_entries || 0;
    
    // Calculate missing entries (this is a simplified calculation)
    const missingCount = Math.max(0, (stats.unique_employees || 0) * 30 - stats.total_entries);
    document.getElementById('missingCount').textContent = missingCount;
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
    loadStatistics();
}

function updatePeriodDisplay() {
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                       'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    document.getElementById('currentPeriod').textContent = 
        `${monthNames[currentMonth - 1]} ${currentYear}`;
}

function applyFilters() {
    loadCalendarData();
    loadStatistics();
}

function refreshCalendar() {
    loadCalendarData();
    loadStatistics();
    showSuccess('Calendar refreshed successfully');
}

function showError(message) {
    showToast(message, 'danger');
}

function showSuccess(message) {
    showToast(message, 'success');
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
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
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
    showActionModal('approve', Array.from(selectedTimesheets), `Approve ${selectedTimesheets.size} timesheet entries?`);
}

function bulkReject() {
    if (selectedTimesheets.size === 0) {
        showError('Please select timesheets to reject');
        return;
    }
    showActionModal('reject', Array.from(selectedTimesheets), `Reject ${selectedTimesheets.size} timesheet entries?`);
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
    
    const endpoint = timesheetIds.length === 1 
        ? `/admin/timesheet-calendar/${action}/${timesheetIds[0]}`
        : `/admin/timesheet-calendar/bulk-${action}`;
    
    const requestData = timesheetIds.length === 1
        ? { reason }
        : { timesheet_ids: timesheetIds, reason };
    
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
        status: document.getElementById('statusFilter').value,
        format: 'csv'
    });
    
    window.location.href = `/admin/timesheet-calendar/export?${params}`;
}
</script>
@endpush
