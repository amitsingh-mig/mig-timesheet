{{-- User Navigation Component - For Regular Employees --}}
@php
    $idSuffix = ($mobile ?? false) ? 'Mobile' : '';
@endphp
@if(Auth::check() && (!Auth::user()->role || Auth::user()->role->name !== 'admin'))
    <!-- Employee Navigation Section -->
    <li class="nav-item-header mt-3 mb-2">
        <span class="nav-header-text">
            <i class="bi bi-person-workspace me-1"></i>
            EMPLOYEE PANEL
        </span>
    </li>

    <!-- Clock Status Display -->
    <li class="clock-status-container mb-3">
        <div class="clock-status-display px-3 py-2">
            <div class="current-status" id="currentClockStatus">
                <i class="bi bi-clock text-info"></i>
                <small class="text-white-75">Status: <span id="statusText">Not Clocked In</span></small>
            </div>
            <div class="current-time" id="currentTime">
                <small class="text-white-50"></small>
            </div>
        </div>
    </li>
    
    
    {{-- Employee Dashboard --}}
    <li>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : 'text-white' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
    </li>
    
    {{-- Timesheet Management --}}
    <li>
        <a href="{{ route('timesheet.index') }}" class="nav-link {{ request()->is('timesheet*') ? 'active' : 'text-white' }}">
            <i class="bi bi-card-checklist"></i>
            <span>My Timesheet</span>
        </a>
    </li>
    
    {{-- Attendance Tracking --}}
    <li>
        <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->is('attendance*') ? 'active' : 'text-white' }}">
            <i class="bi bi-calendar-check"></i>
            <span>My Attendance</span>
        </a>
    </li>
    
    {{-- Leave Requests (only show if route exists) --}}
    @if(Route::has('leave.index'))
    <li>
        <a href="{{ route('leave.index') }}" class="nav-link {{ request()->routeIs('leave.*') ? 'active' : 'text-white' }}">
            <i class="bi bi-briefcase"></i>
            <span>Leave Requests</span>
        </a>
    </li>
    @endif

    {{-- Daily Updates --}}
    <li>
        <a href="{{ route('daily-update.index') }}" class="nav-link {{ request()->is('daily-update*') ? 'active' : 'text-white' }}">
            <i class="bi bi-journal-text"></i>
            <span>Daily Updates</span>
        </a>
    </li>
    
    
    
      
@endif

{{-- @else
    <!-- Non-authenticated or other user types -->
    <li class="nav-item-header mt-3 mb-2">
        <span class="nav-header-text">
            <i class="bi bi-info-circle me-1"></i>
            PLEASE LOGIN
        </span>
    </li>
    
    <li>
        <div class="nav-link text-white text-center" style="cursor: default; opacity: 0.7;">
            <i class="bi bi-box-arrow-in-right me-2"></i>
            <span>Login Required</span>
        </div>
    </li>
@endif --}}

{{-- Employee Clock In/Out Actions --}}
@if(Auth::check() && (!Auth::user()->role || Auth::user()->role->name !== 'admin'))
    
    
    <!-- Clock In/Out Action Buttons -->
    <li class="clock-actions-container px-2">
        <div class="clock-actions-grid">
            <button class="btn btn-success btn-clock-action" id="clockInBtn{{ $idSuffix }}" onclick="clockIn()">
                <i class="bi bi-play-circle me-2"></i>
                <span>Clock In</span>
            </button>
            <button class="btn btn-danger btn-clock-action" id="clockOutBtn{{ $idSuffix }}" onclick="clockOut()">
                <i class="bi bi-stop-circle me-2"></i>
                <span>Clock Out</span>
            </button>
        </div>
    </li>

    <li>
        <a href="#" class="nav-link sub-nav-link text-white" onclick="quickAddTimesheet()">
            <i class="bi bi-plus-circle me-2"></i>
            <span>Add Timesheet Entry</span>
        </a>
    </li>

    {{-- Profile Management --}}
    <li>
        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->is('profile*') ? 'active' : 'text-white' }}">
            <i class="bi bi-person-gear"></i>
            <span>Profile</span>
        </a>
    </li>

@endif

{{-- JavaScript for user navigation functionality --}}
@push('scripts')
<script>
// Clock In/Clock Out functionality for employees
function clockIn() {
    const desktopBtn = document.getElementById('clockInBtn');
    const mobileBtn = document.getElementById('clockInBtnMobile');
    
    // Set loading state
    setClockButtonLoading([desktopBtn, mobileBtn], 'Clocking In...');
    
    // Make AJAX request
    fetch('{{ route("attendance.clockin") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Reset buttons to clocked in state
            resetClockButtonsAfterClockIn([desktopBtn, mobileBtn]);
            updateClockStatus('Clocked In', 'success');
        } else {
            showToast(data.message, 'warning');
            // Reset buttons to original state
            resetClockButtonsOriginal([desktopBtn, mobileBtn]);
        }
    })
    .catch(error => {
        console.error('Clock in error:', error);
        showToast('Failed to clock in. Please try again.', 'danger');
        resetClockButtonsOriginal([desktopBtn, mobileBtn]);
    });
}

function clockOut() {
    const desktopBtn = document.getElementById('clockOutBtn');
    const mobileBtn = document.getElementById('clockOutBtnMobile');
    
    // Set loading state
    setClockButtonLoading([desktopBtn, mobileBtn], 'Clocking Out...');
    
    // Make AJAX request
    fetch('{{ route("attendance.clockout") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Reset buttons to clocked out state
            resetClockButtonsAfterClockOut([desktopBtn, mobileBtn]);
            updateClockStatus('Clocked Out', 'warning');
        } else {
            showToast(data.message, 'warning');
            // Reset buttons to original state
            resetClockButtonsOriginal([desktopBtn, mobileBtn]);
        }
    })
    .catch(error => {
        console.error('Clock out error:', error);
        showToast('Failed to clock out. Please try again.', 'danger');
        resetClockButtonsOriginal([desktopBtn, mobileBtn]);
    });
}

function setClockButtonLoading(buttons, text) {
    buttons.forEach(btn => {
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<i class="bi bi-hourglass-split me-2"></i>${text}`;
        }
    });
}

function resetClockButtonsOriginal(buttons) {
    buttons.forEach(btn => {
        if (btn) {
            btn.disabled = false;
            if (btn.id.includes('clockIn')) {
                btn.innerHTML = '<i class="bi bi-play-circle me-2"></i><span>Clock In</span>';
                btn.className = 'btn btn-success btn-clock-action';
            } else {
                btn.innerHTML = '<i class="bi bi-stop-circle me-2"></i><span>Clock Out</span>';
                btn.className = 'btn btn-danger btn-clock-action';
            }
        }
    });
}

function resetClockButtonsAfterClockIn(buttons) {
    buttons.forEach(btn => {
        if (btn) {
            btn.disabled = false;
            if (btn.id.includes('clockIn')) {
                btn.innerHTML = '<i class="bi bi-check-circle me-2"></i><span>Clocked In</span>';
                btn.className = 'btn btn-secondary btn-clock-action';
            }
        }
    });
}

function resetClockButtonsAfterClockOut(buttons) {
    buttons.forEach(btn => {
        if (btn) {
            btn.disabled = false;
            if (btn.id.includes('clockOut')) {
                btn.innerHTML = '<i class="bi bi-check-circle me-2"></i><span>Clocked Out</span>';
                btn.className = 'btn btn-secondary btn-clock-action';
            }
        }
    });
}

function updateClockStatus(status, type) {
    const statusElement = document.getElementById('statusText');
    const statusIcon = document.querySelector('#currentClockStatus i');
    
    if (statusElement) {
        statusElement.textContent = status;
    }
    
    if (statusIcon) {
        statusIcon.className = `bi bi-clock text-${type}`;
    }
}

// Quick action functions for employees
function quickAddTimesheet() {
    // Open quick timesheet entry modal
    const modal = createQuickModal('Add Timesheet Entry', generateQuickTimesheetForm());
    showModal(modal);
}

function quickViewSummary() {
    // Show quick summary of user's time data
    fetch('/timesheet/summary', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        const summaryHTML = generateSummaryHTML(data);
        const modal = createQuickModal('My Time Summary', summaryHTML);
        showModal(modal);
    })
    .catch(error => {
        console.error('Summary error:', error);
        showToast('Unable to load summary at this time', 'warning');
    });
}

function exportMyData() {
    // Export user's timesheet data
    showToast('Preparing your data export...', 'info');
    
    // Create download link
    const link = document.createElement('a');
    link.href = '/timesheet/export';
    link.download = 'my_timesheet_data.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    setTimeout(() => {
        showToast('Data export initiated', 'success');
    }, 1000);
}

function generateQuickTimesheetForm() {
    return `
        <form id="quickTimesheetForm" class="quick-form">
            <div class="mb-3">
                <label class="form-label">Task Description</label>
                <input type="text" name="task" class="form-control" placeholder="What did you work on?" required>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Hours</label>
                    <select name="hours" class="form-select" required>
                        <option value="">Select hours</option>
                        ${Array.from({length: 12}, (_, i) => `<option value="${i}">${i}h</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Minutes</label>
                    <select name="minutes" class="form-select" required>
                        <option value="">Select minutes</option>
                        <option value="0">0m</option>
                        <option value="15">15m</option>
                        <option value="30">30m</option>
                        <option value="45">45m</option>
                    </select>
                </div>
            </div>
            <div class="mb-3 mt-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-secondary me-2" onclick="closeQuickModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Entry</button>
            </div>
        </form>
    `;
}

function generateSummaryHTML(data) {
    return `
        <div class="time-summary">
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="summary-stat">
                        <h3 class="text-primary">${data.today_hours || '0h'}</h3>
                        <small class="text-muted">Today</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-stat">
                        <h3 class="text-success">${data.week_hours || '0h'}</h3>
                        <small class="text-muted">This Week</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-stat">
                        <h3 class="text-info">${data.month_hours || '0h'}</h3>
                        <small class="text-muted">This Month</small>
                    </div>
                </div>
            </div>
            <hr>
            <div class="recent-activities">
                <h6>Recent Activities</h6>
                <ul class="list-unstyled">
                    ${(data.recent_entries || []).map(entry => 
                        `<li class="d-flex justify-content-between">
                            <span>${entry.task}</span>
                            <small class="text-muted">${entry.hours}</small>
                        </li>`
                    ).join('') || '<li class="text-muted">No recent activities</li>'}
                </ul>
            </div>
        </div>
    `;
}

function createQuickModal(title, content) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(90deg, #28a745, #20c997); color: white;">
                    <h5 class="modal-title"><i class="bi bi-person-workspace me-2"></i>${title}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
            </div>
        </div>
    `;
    return modal;
}

function showModal(modal) {
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    modal.addEventListener('hidden.bs.modal', () => {
        modal.remove();
    });
}

function closeQuickModal() {
    const modals = document.querySelectorAll('.modal.show');
    modals.forEach(modal => {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) bsModal.hide();
    });
}

// Update current time display
function updateCurrentTime() {
    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        timeElement.querySelector('small').textContent = timeString;
    }
}

// Initialize time updates
document.addEventListener('DOMContentLoaded', function() {
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
});

// Handle quick timesheet form submission
document.addEventListener('submit', function(e) {
    if (e.target.id === 'quickTimesheetForm') {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const hours = formData.get('hours') + ':' + formData.get('minutes').padStart(2, '0');
        
        const timesheetData = {
            task: formData.get('task'),
            hours: hours,
            date: formData.get('date')
        };
        
        // Submit via AJAX
        fetch('/timesheet', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(timesheetData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || data.message) {
                showToast('Timesheet entry added successfully!', 'success');
                closeQuickModal();
            } else {
                showToast('Failed to add entry. Please try again.', 'danger');
            }
        })
        .catch(error => {
            console.error('Timesheet error:', error);
            showToast('Error adding entry. Please try again.', 'danger');
        });
    }
});
</script>
@endpush
