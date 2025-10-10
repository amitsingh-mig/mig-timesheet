@extends('layouts.app')

@section('content')
<!-- Cache busting for development -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<div class="container-fluid px-4">
    <!-- Modern Employee Header -->
    <div class="employee-header">
        <div class="employee-header-content">
            <div class="employee-title-section">
                <h1 class="employee-title">Timesheet Management</h1>
                <p class="employee-subtitle">Track your daily work hours and manage your tasks efficiently</p>
            </div>
                <div class="employee-actions">
                <button class="btn-employee-primary" data-bs-toggle="modal" data-bs-target="#timesheetModal" onclick="testForm()">
                    <i class="bi bi-plus-circle"></i>
                    Add New Entry
                </button>
            </div>
        </div>
    </div>

    <!-- Modern Summary Cards -->
    <div class="employee-stats-grid">
        <div class="employee-stat-card employee-stat-primary">
            <div class="employee-stat-icon">
                <i class="bi bi-calendar-day"></i>
            </div>
            <div class="employee-stat-content">
                <h3 class="employee-stat-number" id="todayHours">0h</h3>
                <p class="employee-stat-label">Today's Hours</p>
            </div>
        </div>
        <div class="employee-stat-card employee-stat-success">
            <div class="employee-stat-icon">
                <i class="bi bi-calendar-week"></i>
            </div>
            <div class="employee-stat-content">
                <h3 class="employee-stat-number" id="weekHours">0h</h3>
                <p class="employee-stat-label">This Week</p>
            </div>
        </div>
        <div class="employee-stat-card employee-stat-info">
            <div class="employee-stat-icon">
                <i class="bi bi-calendar-month"></i>
            </div>
            <div class="employee-stat-content">
                <h3 class="employee-stat-number" id="monthHours">0h</h3>
                <p class="employee-stat-label">This Month</p>
            </div>
        </div>
        <div class="employee-stat-card employee-stat-warning">
            <div class="employee-stat-icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="employee-stat-content">
                <h3 class="employee-stat-number" id="totalEntries">0</h3>
                <p class="employee-stat-label">Total Entries</p>
            </div>
        </div>
    </div>

    <!-- Weekly Calendar View -->
    <div class="employee-card mb-4">
        <div class="employee-card-header">
            <div class="employee-card-title">
                <i class="bi bi-calendar-week"></i>
                Weekly Calendar
            </div>
            <div class="calendar-controls">
                <span id="weekRange" class="week-range">Loading...</span>
                <div class="btn-group">
                    <button class="btn-employee-secondary btn-sm" onclick="navigateWeek(-1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="btn-employee-secondary btn-sm" onclick="navigateWeek(1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="employee-card-body p-0">
            <div class="row g-0" id="weeklyCalendar">
                <!-- Calendar populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Timesheet Records -->
    <div class="card-modern">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title-modern">
                        <i class="bi bi-table me-2"></i>
                        Timesheet Records
                    </h3>
                    <p class="card-subtitle-modern">View and manage all your timesheet entries</p>
                </div>
                <div class="table-actions-modern">
                    <button class="btn-action-modern" onclick="exportTimesheet()">
                        <i class="bi bi-download"></i>
                        Export
                    </button>
                </div>
            </div>
        </div>
        <div class="employee-card-body p-0">
            <div class="table-responsive">
                <table class="table employee-table">
                    <thead>
                        <tr>
                            <th class="text-start">Date</th>
                            <th class="text-center">Clock In</th>
                            <th class="text-center">Clock Out</th>
                            <th class="text-center">Duration</th>
                            <th class="text-center">Tasks</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="timesheetTableBody">
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="employee-loading">
                                    <div class="employee-spinner"></div>
                                    <p>Loading timesheet data...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modern Timesheet Entry Modal -->
<div class="modal fade" id="timesheetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="employee-modal-content">
            <div class="employee-modal-header">
                <h5 class="employee-modal-title" id="modalTitle">
                    <i class="bi bi-clock"></i>Add Timesheet Entry
                </h5>
                <button type="button" class="employee-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <form id="timesheetForm" method="POST" action="{{ route('timesheet.storeOrUpdate') }}">
                @csrf
                <input type="hidden" id="entryId" name="id" value="">
                <div class="employee-modal-body">
                    <div id="formAlert" class="alert-modern d-none"></div>
                    
                    <div class="form-grid-modern">
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="bi bi-calendar me-1"></i>
                                Date <span class="required-mark">*</span>
                            </label>
                            <input type="date" name="date" id="entryDate" class="form-input-modern" required>
                        </div>
                        
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="bi bi-clock me-1"></i>
                                Clock In <span class="required-mark">*</span>
                            </label>
                            <input type="time" name="clock_in" id="clockIn" class="form-input-modern" required>
                        </div>
                        
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="bi bi-clock-fill me-1"></i>
                                Clock Out <span class="required-mark">*</span>
                            </label>
                            <input type="time" name="clock_out" id="clockOut" class="form-input-modern" required>
                        </div>
                        
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="bi bi-hourglass me-1"></i>
                                Duration (Auto-calculated)
                            </label>
                            <input type="text" id="durationDisplay" class="form-input-modern" readonly placeholder="0.0 hours">
                        </div>
                    </div>
                    
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="bi bi-list-task me-1"></i>
                            Task Description <span class="required-mark">*</span>
                        </label>
                        <textarea name="task_description" id="taskDescription" class="form-textarea-modern" rows="4" placeholder="Describe what you worked on today..." required></textarea>
                    </div>
                </div>
                <div class="employee-modal-footer">
                    <button type="button" class="btn-employee-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn-employee-primary">
                        <span class="employee-spinner d-none" role="status"></span>
                        <i class="bi bi-check2" id="submitIcon"></i>
                        <span id="submitText">Save Entry</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
let currentWeekStart = new Date();
currentWeekStart.setDate(currentWeekStart.getDate() - currentWeekStart.getDay() + 1); // Monday as start

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Timesheet page loaded');
    
    // Clear any cached form data
    if (typeof(Storage) !== "undefined") {
        localStorage.removeItem('timesheetFormData');
        sessionStorage.removeItem('timesheetFormData');
    }
    
    renderWeeklyCalendar();
    loadTimesheetData();
    setupEventListeners();
    
    // Set default date to today
    const dateField = document.getElementById('entryDate');
    if (dateField) {
        dateField.value = new Date().toISOString().split('T')[0];
        console.log('Default date set to:', dateField.value);
    } else {
        console.error('Date field not found!');
    }
    
    // Debug: Check if all form elements exist
    console.log('Form elements check:');
    console.log('Form:', document.getElementById('timesheetForm'));
    console.log('Date field:', document.getElementById('entryDate'));
    console.log('Clock In field:', document.getElementById('clockIn'));
    console.log('Clock Out field:', document.getElementById('clockOut'));
    console.log('Task Description field:', document.getElementById('taskDescription'));
});

function setupEventListeners() {
    // Form submission
    const form = document.getElementById('timesheetForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
        console.log('Timesheet form event listener added');
    } else {
        console.error('Timesheet form not found!');
    }
    
    // Auto-calculate duration when time changes
    const clockInField = document.getElementById('clockIn');
    const clockOutField = document.getElementById('clockOut');
    
    if (clockInField) {
        clockInField.addEventListener('change', calculateDuration);
        console.log('Clock In field found and event listener added');
    } else {
        console.error('Clock In field not found!');
    }
    
    if (clockOutField) {
        clockOutField.addEventListener('change', calculateDuration);
        console.log('Clock Out field found and event listener added');
    } else {
        console.error('Clock Out field not found!');
    }
    
    // Reset form when modal is hidden
    const modal = document.getElementById('timesheetModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', resetForm);
        console.log('Modal event listener added');
    } else {
        console.error('Modal not found!');
    }
}

function renderWeeklyCalendar() {
    const container = document.getElementById('weeklyCalendar');
    container.innerHTML = '';
    const dayNames = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    
    // Update week range display
    const start = new Date(currentWeekStart);
    const end = new Date(currentWeekStart);
    end.setDate(start.getDate() + 6);
    document.getElementById('weekRange').textContent = 
        `${start.toLocaleDateString()} - ${end.toLocaleDateString()}`;

    for (let i = 0; i < 7; i++) {
        const date = new Date(currentWeekStart);
        date.setDate(currentWeekStart.getDate() + i);
        const isoDate = date.toISOString().split('T')[0];
        const dayNum = date.getDate();
        const isToday = isoDate === new Date().toISOString().split('T')[0];

        const col = document.createElement('div');
        col.className = 'col';
        col.innerHTML = `
            <div class="card shadow-sm h-100 ${isToday ? 'border-primary' : ''}" style="cursor: pointer;" onclick="openEntryModal('${isoDate}')">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold ${isToday ? 'text-primary' : ''}">${dayNames[i]}</span>
                        <span class="badge ${isToday ? 'bg-primary' : 'bg-light text-dark'}">${dayNum}</span>
                    </div>
                    <div id="day-${isoDate}" class="small">
                        <div class="text-muted">No entries</div>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(col);
    }
}

function navigateWeek(direction) {
    currentWeekStart.setDate(currentWeekStart.getDate() + (direction * 7));
    renderWeeklyCalendar();
    loadTimesheetData();
}

function loadTimesheetData() {
    const start = new Date(currentWeekStart);
    const end = new Date(currentWeekStart);
    end.setDate(start.getDate() + 6);
    const startStr = start.toISOString().split('T')[0];
    const endStr = end.toISOString().split('T')[0];

    console.log('Loading timesheet data for range:', startStr, 'to', endStr);

    fetch(`{{ route('timesheet.summary') }}?start_date=${startStr}&end_date=${endStr}`, {
        method: 'GET',
        headers: { 
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Timesheet data received:', data);
        if (data.success) {
            updateCalendarEntries(data.records || []);
            updateTimesheetTable(data.records || []);
            updateStats(data.records || []);
        } else {
            console.error('Server returned error:', data.message);
            showNoDataMessage();
        }
    })
    .catch(error => {
        console.error('Error loading timesheet data:', error);
        showNoDataMessage();
    });
}

function updateStats(records) {
    const today = new Date().toISOString().split('T')[0];
    const weekStart = new Date(currentWeekStart);
    const monthStart = new Date();
    monthStart.setDate(1);
    
    let todayHours = 0;
    let weekHours = 0;
    let monthHours = 0;
    
    records.forEach(record => {
        const recordDate = new Date(record.date);
        const hours = parseFloat(record.duration) || 0;
        
        if (record.date === today) {
            todayHours += hours;
        }
        
        if (recordDate >= weekStart && recordDate <= new Date(weekStart.getTime() + 6 * 24 * 60 * 60 * 1000)) {
            weekHours += hours;
        }
        
        if (recordDate >= monthStart) {
            monthHours += hours;
        }
    });
    
    document.getElementById('todayHours').textContent = `${todayHours.toFixed(1)}h`;
    document.getElementById('weekHours').textContent = `${weekHours.toFixed(1)}h`;
    document.getElementById('monthHours').textContent = `${monthHours.toFixed(1)}h`;
    document.getElementById('totalEntries').textContent = records.length;
}

function showNoDataMessage() {
    // Show empty state with helpful message
    const tbody = document.getElementById('timesheetTableBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-5">
                <div class="text-muted">
                    <i class="bi bi-calendar-x mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                    <h5 class="mb-2">No timesheet entries found</h5>
                    <p class="mb-1">No timesheet entries found for this week.</p>
                    <small>Click "Add New Entry" to create your first timesheet entry.</small>
                </div>
            </td>
        </tr>
    `;
    
    // Clear calendar entries
    for (let i = 0; i < 7; i++) {
        const date = new Date(currentWeekStart);
        date.setDate(currentWeekStart.getDate() + i);
        const isoDate = date.toISOString().split('T')[0];
        const dayEl = document.getElementById(`day-${isoDate}`);
        if (dayEl) {
            dayEl.innerHTML = '<div class="text-muted">No entries</div>';
        }
    }
    
    // Reset stats
    document.getElementById('todayHours').textContent = '0h';
    document.getElementById('weekHours').textContent = '0h';
    document.getElementById('monthHours').textContent = '0h';
    document.getElementById('totalEntries').textContent = '0';
}

function updateCalendarEntries(records) {
    console.log('Updating calendar entries with records:', records);
    
    // Clear all day entries first
    for (let i = 0; i < 7; i++) {
        const date = new Date(currentWeekStart);
        date.setDate(currentWeekStart.getDate() + i);
        const isoDate = date.toISOString().split('T')[0];
        const dayEl = document.getElementById(`day-${isoDate}`);
        if (dayEl) {
            dayEl.innerHTML = '<div class="text-muted">No entries</div>';
        }
    }
    
    // Group records by date (in case there are multiple entries per day)
    const recordsByDate = {};
    records.forEach(record => {
        const date = record.date;
        if (!recordsByDate[date]) {
            recordsByDate[date] = [];
        }
        recordsByDate[date].push(record);
    });
    
    // Update with actual data
    Object.keys(recordsByDate).forEach(date => {
        const dayEl = document.getElementById(`day-${date}`);
        if (dayEl) {
            const dayRecords = recordsByDate[date];
            const totalHours = dayRecords.reduce((sum, record) => {
                return sum + (parseFloat(record.duration) || 0);
            }, 0);
            const taskCount = dayRecords.length;
            const mainTask = dayRecords[0].task_description || dayRecords[0].tasks || 'No description';
            
            dayEl.innerHTML = `
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-success fw-bold">${totalHours.toFixed(1)}h</small>
                    <small class="text-primary">${taskCount} task${taskCount !== 1 ? 's' : ''}</small>
                </div>
                <div class="small text-muted text-truncate" title="${mainTask}">
                    ${mainTask.length > 25 ? mainTask.substring(0, 25) + '...' : mainTask}
                </div>
            `;
        }
    });
}

function updateTimesheetTable(records) {
    const tbody = document.getElementById('timesheetTableBody');
    if (!records.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No timesheet entries found</td></tr>';
        return;
    }
    
    tbody.innerHTML = records.map(record => {
        // Calculate duration from clock_in and clock_out
        let duration = '0.0h';
        if (record.clock_in && record.clock_out) {
            const start = new Date(`2000-01-01T${record.clock_in}:00`);
            const end = new Date(`2000-01-01T${record.clock_out}:00`);
            const diffMs = end - start;
            
            if (diffMs > 0) {
                const hours = (diffMs / (1000 * 60 * 60)).toFixed(1);
                duration = `${hours}h`;
            }
        } else if (record.duration) {
            // Fallback to provided duration if available
            duration = `${parseFloat(record.duration).toFixed(1)}h`;
        }
        
        return `
            <tr>
                <td class="fw-medium">${new Date(record.date).toLocaleDateString()}</td>
                <td class="text-center">${record.clock_in || '-'}</td>
                <td class="text-center">${record.clock_out || '-'}</td>
                <td class="text-center">
                    <span class="badge bg-success">${duration}</span>
                </td>
                <td class="text-truncate" style="max-width: 200px;" title="${record.task_description || ''}">
                    ${record.task_description || 'No description'}
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editEntry(${record.id}, '${record.date}', '${record.clock_in}', '${record.clock_out}', '${(record.task_description || '').replace(/'/g, "\\'")}')" title="Edit Entry">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteEntry(${record.id})" title="Delete Entry">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function openEntryModal(date = null) {
    resetForm();
    if (date) {
        document.getElementById('entryDate').value = date;
    }
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-clock me-2"></i>Add Timesheet Entry';
    document.getElementById('submitText').textContent = 'Save Entry';
    new bootstrap.Modal(document.getElementById('timesheetModal')).show();
}

function editEntry(id, date, clockIn, clockOut, taskDescription) {
    document.getElementById('entryId').value = id;
    document.getElementById('entryDate').value = date;
    document.getElementById('clockIn').value = clockIn;
    document.getElementById('clockOut').value = clockOut;
    document.getElementById('taskDescription').value = taskDescription;
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Timesheet Entry';
    document.getElementById('submitText').textContent = 'Update Entry';
    calculateDuration();
    new bootstrap.Modal(document.getElementById('timesheetModal')).show();
}

function calculateDuration() {
    const clockIn = document.getElementById('clockIn').value;
    const clockOut = document.getElementById('clockOut').value;
    const durationDisplay = document.getElementById('durationDisplay');
    
    if (clockIn && clockOut) {
        const start = new Date(`2000-01-01T${clockIn}:00`);
        const end = new Date(`2000-01-01T${clockOut}:00`);
        const diffMs = end - start;
        
        if (diffMs > 0) {
            const hours = (diffMs / (1000 * 60 * 60)).toFixed(1);
            durationDisplay.value = `${hours} hours`;
        } else {
            durationDisplay.value = 'Invalid time range';
        }
    } else {
        durationDisplay.value = '';
    }
}

function handleFormSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const spinner = submitBtn.querySelector('.employee-spinner');
    const alertEl = document.getElementById('formAlert');
    
    // Basic validation
    const date = formData.get('date');
    const clockIn = formData.get('clock_in');
    const clockOut = formData.get('clock_out');
    const taskDescription = formData.get('task_description');
    
    if (!date || !clockIn || !clockOut || !taskDescription) {
        alertEl.className = 'alert-modern alert-danger';
        alertEl.textContent = 'Please fill in all required fields';
        alertEl.classList.remove('d-none');
        return;
    }
    
    // Debug: Log form data
    console.log('Form data being submitted:');
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }
    
    // Show loading state
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    alertEl.classList.add('d-none');
    
    const url = '{{ route("timesheet.storeOrUpdate") }}';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Close modal and refresh data
            bootstrap.Modal.getInstance(document.getElementById('timesheetModal')).hide();
            loadTimesheetData();
            
            // Trigger dashboard refresh
            if (window.refreshDashboard) {
                window.refreshDashboard();
            }
            
            showToast('Timesheet entry saved successfully!', 'success');
        } else {
            alertEl.className = 'alert-modern alert-danger';
            alertEl.textContent = data.message || 'Failed to save entry';
            alertEl.classList.remove('d-none');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alertEl.className = 'alert-modern alert-danger';
        alertEl.textContent = 'An error occurred while saving the entry: ' + error.message;
        alertEl.classList.remove('d-none');
    })
    .finally(() => {
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
    });
}

function deleteEntry(id) {
    if (!confirm('Are you sure you want to delete this timesheet entry?')) return;
    
    fetch(`{{ url('/timesheet') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadTimesheetData();
            if (window.refreshDashboard) {
                window.refreshDashboard();
            }
            showToast('Timesheet entry deleted successfully!', 'success');
        } else {
            showToast(data.message || 'Failed to delete entry', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting the entry', 'error');
    });
}

function resetForm() {
    document.getElementById('timesheetForm').reset();
    document.getElementById('entryId').value = '';
    document.getElementById('durationDisplay').value = '';
    document.getElementById('formAlert').classList.add('d-none');
    document.getElementById('entryDate').value = new Date().toISOString().split('T')[0];
}

function exportTimesheet() {
    const start = new Date(currentWeekStart).toISOString().split('T')[0];
    const end = new Date(currentWeekStart.getTime() + 6 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    window.open(`{{ route('timesheet.export') }}?start_date=${start}&end_date=${end}`, '_blank');
}

function testForm() {
    console.log('Testing form elements...');
    const form = document.getElementById('timesheetModal');
    if (form) {
        console.log('Modal found:', form);
        const formElement = document.getElementById('timesheetForm');
        if (formElement) {
            console.log('Form element found:', formElement);
            console.log('Form action:', formElement.action);
            console.log('Form method:', formElement.method);
            
            // Check all form fields
            const fields = ['entryDate', 'clockIn', 'clockOut', 'taskDescription'];
            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    console.log(`${fieldId} field found:`, field);
                    console.log(`${fieldId} name:`, field.name);
                    console.log(`${fieldId} type:`, field.type);
                } else {
                    console.error(`${fieldId} field NOT found!`);
                }
            });
        } else {
            console.error('Form element NOT found!');
        }
    } else {
        console.error('Modal NOT found!');
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    new bootstrap.Toast(toast).show();
    
    setTimeout(() => toast.remove(), 5000);
}
</script>
@endpush