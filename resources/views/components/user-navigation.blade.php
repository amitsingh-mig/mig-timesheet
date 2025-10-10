{{-- Modern User Navigation Component - For Regular Employees --}}
@php
    $idSuffix = ($mobile ?? false) ? 'Mobile' : '';
@endphp

@if(Auth::check() && (!Auth::user()->role || Auth::user()->role->name !== 'admin'))
    <!-- Employee Navigation Section -->
    <li class="nav-item-header-modern mt-3 mb-3">
        <div class="nav-header-modern">
            <div class="nav-header-icon employee-icon">
                <i class="bi bi-person-workspace"></i>
            </div>
            <div class="nav-header-content">
                <span class="nav-header-title">Employee Panel</span>
                <span class="nav-header-subtitle">Your Workspace</span>
            </div>
        </div>
    </li>

    <!-- Modern Clock Status Display -->
    <li class="nav-item-modern">
        <div class="clock-status-modern">
            <div class="clock-status-header">
                <div class="clock-status-icon">
                    <i class="bi bi-clock" id="clockStatusIcon"></i>
                </div>
                <div class="clock-status-content">
                    <div class="clock-status-title">Current Status</div>
                    <div class="clock-status-value" id="statusText">Not Clocked In</div>
                </div>
            </div>
            <div class="clock-time-display">
                <div class="current-time" id="currentTime">
                    <i class="bi bi-calendar3 me-1"></i>
                    <span id="timeDisplay"></span>
                </div>
            </div>
        </div>
    </li>
    
    {{-- Employee Dashboard --}}
    <li class="nav-item-modern">
        <a href="{{ route('dashboard') }}" class="nav-link-modern {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <div class="nav-link-icon">
                <i class="bi bi-speedometer2"></i>
            </div>
            <div class="nav-link-content">
                <span class="nav-link-title">Dashboard</span>
                <span class="nav-link-subtitle">Overview & Summary</span>
            </div>
        </a>
    </li>
    
    {{-- Timesheet Management --}}
    <li class="nav-item-modern">
        <a href="{{ route('timesheet.index') }}" class="nav-link-modern {{ request()->is('timesheet*') ? 'active' : '' }}">
            <div class="nav-link-icon">
                <i class="bi bi-card-checklist"></i>
            </div>
            <div class="nav-link-content">
                <span class="nav-link-title">My Timesheet</span>
                <span class="nav-link-subtitle">Track your work hours</span>
            </div>
        </a>
    </li>
    
    {{-- Attendance Tracking --}}
    <li class="nav-item-modern">
        <a href="{{ route('attendance.index') }}" class="nav-link-modern {{ request()->is('attendance*') ? 'active' : '' }}">
            <div class="nav-link-icon">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="nav-link-content">
                <span class="nav-link-title">My Attendance</span>
                <span class="nav-link-subtitle">Clock in/out history</span>
            </div>
        </a>
    </li>

    {{-- Daily Updates --}}
    <li class="nav-item-modern">
        <a href="{{ route('daily-update.index') }}" class="nav-link-modern {{ request()->is('daily-update*') ? 'active' : '' }}">
            <div class="nav-link-icon">
                <i class="bi bi-journal-text"></i>
            </div>
            <div class="nav-link-content">
                <span class="nav-link-title">Daily Updates</span>
                <span class="nav-link-subtitle">Share your progress</span>
            </div>
        </a>
    </li>

    {{-- Leave Requests (only show if route exists) --}}
    @if(Route::has('leave.index'))
    <li class="nav-item-modern">
        <a href="{{ route('leave.index') }}" class="nav-link-modern {{ request()->routeIs('leave.*') ? 'active' : '' }}">
            <div class="nav-link-icon">
                <i class="bi bi-briefcase"></i>
            </div>
            <div class="nav-link-content">
                <span class="nav-link-title">Leave Requests</span>
                <span class="nav-link-subtitle">Request time off</span>
            </div>
        </a>
    </li>
    @endif

    {{-- Quick Actions Section --}}
    <li class="nav-item-modern">
        <div class="nav-section-header">
            <i class="bi bi-lightning-fill me-2"></i>
            <span>Quick Actions</span>
        </div>
    </li>

    <!-- Modern Clock In/Out Action Buttons -->
    <li class="nav-item-modern">
        <div class="clock-actions-modern">
            <button class="btn-clock-modern btn-clock-in" id="clockInBtn{{ $idSuffix }}" onclick="clockIn()">
                <div class="btn-clock-icon">
                    <i class="bi bi-play-circle-fill"></i>
                </div>
                <div class="btn-clock-content">
                    <span class="btn-clock-title">Clock In</span>
                </div>
            </button>
            
            <button class="btn-clock-modern btn-clock-out" id="clockOutBtn{{ $idSuffix }}" onclick="clockOut()">
                <div class="btn-clock-icon">
                    <i class="bi bi-stop-circle-fill"></i>
                </div>
                <div class="btn-clock-content">
                    <span class="btn-clock-title">Clock Out</span>
                </div>
            </button>
        </div>
    </li>

    <li class="nav-item-modern">
        <a href="#" class="nav-link-modern" onclick="quickAddTimesheet()">
            <div class="nav-link-icon">
                <i class="bi bi-plus-circle-fill"></i>
            </div>
            <div class="nav-link-content">
                <span class="nav-link-title">Add Timesheet Entry</span>
            </div>
        </a>
    </li>

    {{-- Profile Management --}}
    <li class="nav-item-modern">
        <a href="{{ route('profile.edit') }}" class="nav-link-modern {{ request()->is('profile*') ? 'active' : '' }}">
            <div class="nav-link-icon">
                <i class="bi bi-person-gear"></i>
            </div>
            <div class="nav-link-content">
                <span class="nav-link-title">Profile Settings</span>
                <span class="nav-link-subtitle">Manage your account</span>
            </div>
        </a>
    </li>

@endif

{{-- Modern User Navigation Styles --}}
@push('styles')
<style>
/* Employee-specific header styling */
.employee-icon {
    background: linear-gradient(135deg, #28a745, #20c997) !important;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3) !important;
}

/* Modern Clock Status */
.clock-status-modern {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 16px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
}

.clock-status-header {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
}

.clock-status-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #17a2b8, #138496);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
}

.clock-status-icon i {
    font-size: 18px;
    color: white;
}

.clock-status-content {
    flex: 1;
}

.clock-status-title {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.7);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}

.clock-status-value {
    font-size: 14px;
    font-weight: 600;
    color: white;
}

.clock-time-display {
    text-align: center;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.current-time {
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.8);
    font-size: 13px;
    font-weight: 500;
}

/* Modern Clock Action Buttons */
.clock-actions-modern {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
}

.btn-clock-modern {
    flex: 1;
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border: none;
    border-radius: 10px;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.btn-clock-in {
    background: linear-gradient(135deg, #28a745, #20c997);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-clock-out {
    background: linear-gradient(135deg, #dc3545, #c82333);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.btn-clock-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.btn-clock-modern:active {
    transform: translateY(0);
}

.btn-clock-modern:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-clock-icon {
    width: 32px;
    height: 32px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
}

.btn-clock-icon i {
    font-size: 16px;
    color: white;
}

.btn-clock-content {
    flex: 1;
    text-align: left;
}

.btn-clock-title {
    display: block;
    font-weight: 600;
    font-size: 13px;
    line-height: 1.2;
    margin-bottom: 2px;
}

.btn-clock-subtitle {
    display: block;
    font-size: 10px;
    opacity: 0.8;
    line-height: 1.2;
}

/* Quick Action Link Special Styling */
.quick-action-link {
    background: linear-gradient(135deg, rgba(78, 205, 196, 0.2), rgba(68, 160, 141, 0.2));
    border: 1px solid rgba(78, 205, 196, 0.3);
}

.quick-action-link:hover {
    background: linear-gradient(135deg, rgba(78, 205, 196, 0.3), rgba(68, 160, 141, 0.3));
    border-color: rgba(78, 205, 196, 0.5);
}

/* Ripple Effect */
.ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: scale(0);
    animation: ripple-animation 0.6s linear;
    pointer-events: none;
}

@keyframes ripple-animation {
    to {
        transform: scale(4);
        opacity: 0;
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .clock-actions-modern {
        flex-direction: column;
        gap: 6px;
    }
    
    .btn-clock-modern {
        padding: 10px 12px;
    }
    
    .clock-status-modern {
        padding: 12px;
    }
}
</style>
@endpush

{{-- JavaScript for modern user navigation functionality --}}
@push('scripts')
<script>
// Modern Clock In/Clock Out functionality
function clockIn() {
    const desktopBtn = document.getElementById('clockInBtn');
    const mobileBtn = document.getElementById('clockInBtnMobile');
    
    // Set loading state
    setClockButtonLoading([desktopBtn, mobileBtn], 'Clocking In...', 'clock-in');
    
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
            resetClockButtonsAfterClockIn([desktopBtn, mobileBtn]);
            updateClockStatus('Clocked In', 'success');
        } else {
            showToast(data.message, 'warning');
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
    setClockButtonLoading([desktopBtn, mobileBtn], 'Clocking Out...', 'clock-out');
    
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
            resetClockButtonsAfterClockOut([desktopBtn, mobileBtn]);
            updateClockStatus('Clocked Out', 'warning');
        } else {
            showToast(data.message, 'warning');
            resetClockButtonsOriginal([desktopBtn, mobileBtn]);
        }
    })
    .catch(error => {
        console.error('Clock out error:', error);
        showToast('Failed to clock out. Please try again.', 'danger');
        resetClockButtonsOriginal([desktopBtn, mobileBtn]);
    });
}

function setClockButtonLoading(buttons, text, type) {
    buttons.forEach(btn => {
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `
                <div class="btn-clock-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="btn-clock-content">
                    <span class="btn-clock-title">${text}</span>
                    <span class="btn-clock-subtitle">Please wait...</span>
                </div>
            `;
        }
    });
}

function resetClockButtonsOriginal(buttons) {
    buttons.forEach(btn => {
        if (btn) {
            btn.disabled = false;
            if (btn.id.includes('clockIn')) {
                btn.innerHTML = `
                    <div class="btn-clock-icon">
                        <i class="bi bi-play-circle-fill"></i>
                    </div>
                    <div class="btn-clock-content">
                        <span class="btn-clock-title">Clock In</span>
                        <span class="btn-clock-subtitle">Start your day</span>
                    </div>
                `;
                btn.className = 'btn-clock-modern btn-clock-in';
            } else {
                btn.innerHTML = `
                    <div class="btn-clock-icon">
                        <i class="bi bi-stop-circle-fill"></i>
                    </div>
                    <div class="btn-clock-content">
                        <span class="btn-clock-title">Clock Out</span>
                        <span class="btn-clock-subtitle">End your day</span>
                    </div>
                `;
                btn.className = 'btn-clock-modern btn-clock-out';
            }
        }
    });
}

function resetClockButtonsAfterClockIn(buttons) {
    buttons.forEach(btn => {
        if (btn && btn.id.includes('clockIn')) {
            btn.disabled = false;
            btn.innerHTML = `
                <div class="btn-clock-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="btn-clock-content">
                    <span class="btn-clock-title">Clocked In</span>
                    <span class="btn-clock-subtitle">You're logged in</span>
                </div>
            `;
            btn.className = 'btn-clock-modern btn-clock-in';
        }
    });
}

function resetClockButtonsAfterClockOut(buttons) {
    buttons.forEach(btn => {
        if (btn && btn.id.includes('clockOut')) {
            btn.disabled = false;
            btn.innerHTML = `
                <div class="btn-clock-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="btn-clock-content">
                    <span class="btn-clock-title">Clocked Out</span>
                    <span class="btn-clock-subtitle">You're logged out</span>
                </div>
            `;
            btn.className = 'btn-clock-modern btn-clock-out';
        }
    });
}

function updateClockStatus(status, type) {
    const statusElement = document.getElementById('statusText');
    const statusIcon = document.getElementById('clockStatusIcon');
    
    if (statusElement) {
        statusElement.textContent = status;
    }
    
    if (statusIcon) {
        statusIcon.className = `bi bi-clock text-${type}`;
    }
}

// Quick action functions
function quickAddTimesheet() {
    const modal = createQuickModal('Add Timesheet Entry', generateQuickTimesheetForm());
    showModal(modal);
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
    const timeElement = document.getElementById('timeDisplay');
    if (timeElement) {
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        timeElement.textContent = timeString;
    }
}

// Initialize time updates
document.addEventListener('DOMContentLoaded', function() {
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
    
    // Add ripple effect to nav links
    document.querySelectorAll('.nav-link-modern').forEach(link => {
        link.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
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

// Toast notification function
function showToast(message, type = 'info') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    const toast = createToast(message, type);
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

function createToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    return toast;
}
</script>
@endpush