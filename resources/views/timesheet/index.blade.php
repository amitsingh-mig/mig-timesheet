@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">Timesheet Calendar</h1>
            <p class="text-muted mb-0">Manage your daily work hours and tasks</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#timesheetModal">
                <i class="bi bi-plus-circle me-2"></i>Add New Entry
            </button>
        </div>
    </div>

    <!-- Weekly Calendar View -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Weekly Calendar</h5>
            <div class="d-flex gap-2">
                <span id="weekRange" class="badge bg-light text-dark">Loading...</span>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="navigateWeek(-1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="btn btn-outline-primary" onclick="navigateWeek(1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="row g-0" id="weeklyCalendar">
                <!-- Calendar populated by JavaScript -->
            </div>
        </div>
    </div>


    <!-- Timesheet Records Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i>Timesheet Records</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="exportTimesheet()">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Duration</th>
                            <th>Tasks</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="timesheetTableBody">
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Timesheet Entry Modal -->
<div class="modal fade" id="timesheetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-admin text-white">
                <h5 class="modal-title" id="modalTitle"><i class="bi bi-clock me-2"></i>Add Timesheet Entry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="timesheetForm">
                @csrf
                <input type="hidden" id="entryId" name="id" value="">
                <div class="modal-body">
                    <div id="formAlert" class="alert d-none"></div>
                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="entryDate" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Clock In <span class="text-danger">*</span></label>
                                <input type="time" name="clock_in" id="clockIn" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Clock Out <span class="text-danger">*</span></label>
                                <input type="time" name="clock_out" id="clockOut" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Task Description <span class="text-danger">*</span></label>
                        <textarea name="task_description" id="taskDescription" class="form-control" rows="3" placeholder="Describe what you worked on..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duration (Auto-calculated)</label>
                        <input type="text" id="durationDisplay" class="form-control" readonly placeholder="0.0 hours">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
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
    renderWeeklyCalendar();
    loadTimesheetData();
    setupEventListeners();
    
    // Set default date to today
    document.getElementById('entryDate').value = new Date().toISOString().split('T')[0];
});

function setupEventListeners() {
    // Form submission
    document.getElementById('timesheetForm').addEventListener('submit', handleFormSubmit);
    
    // Auto-calculate duration when time changes
    document.getElementById('clockIn').addEventListener('change', calculateDuration);
    document.getElementById('clockOut').addEventListener('change', calculateDuration);
    
    // Reset form when modal is hidden
    document.getElementById('timesheetModal').addEventListener('hidden.bs.modal', resetForm);
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
            <div class="card shadow-sm h-100 h-30 ${isToday ? 'border-primary' : ''}" style="cursor: pointer;" onclick="openEntryModal('${isoDate}')">
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

function showNoDataMessage() {
    // Show empty state with helpful message
    const tbody = document.getElementById('timesheetTableBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="text-muted">
                    <i class="bi bi-calendar-x mb-2" style="font-size: 2rem;"></i>
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
    
    tbody.innerHTML = records.map(record => `
        <tr>
            <td class="fw-medium">${new Date(record.date).toLocaleDateString()}</td>
            <td>${record.clock_in || '-'}</td>
            <td>${record.clock_out || '-'}</td>
            <td><span class="badge bg-primary">${record.duration || '0'}h</span></td>
            <td class="text-truncate" style="max-width: 200px;" title="${record.task_description || ''}">
                ${record.task_description || 'No description'}
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editEntry(${record.id}, '${record.date}', '${record.clock_in}', '${record.clock_out}', '${(record.task_description || '').replace(/'/g, "\\'")}')"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteEntry(${record.id})"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    `).join('');
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
    const spinner = submitBtn.querySelector('.spinner-border');
    const alertEl = document.getElementById('formAlert');
    
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
    .then(response => response.json())
    .then(data => {
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
            alertEl.className = 'alert alert-danger';
            alertEl.textContent = data.message || 'Failed to save entry';
            alertEl.classList.remove('d-none');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alertEl.className = 'alert alert-danger';
        alertEl.textContent = 'An error occurred while saving the entry';
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
