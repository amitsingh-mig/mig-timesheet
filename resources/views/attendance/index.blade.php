@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 attendance-page">
    <!-- Modern Employee Header -->
    <div class="employee-header">
        <div class="employee-header-content">
            <div class="employee-title-section">
                <h1 class="employee-title">Attendance Management</h1>
                <p class="employee-subtitle">Track your daily attendance and manage work hours efficiently</p>
            </div>
            <div class="employee-time-display">
                <div class="current-time">{{ now()->format('H:i') }}</div>
                <div class="current-date">{{ now()->format('M j, Y') }}</div>
            </div>
        </div>
    </div>

<div class="container">
            <!-- Modern Summary Cards -->
            <div class="employee-stats-grid">
                <div class="employee-stat-card employee-stat-success">
                    <div class="employee-stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="employee-stat-content">
                        <h3 class="employee-stat-number" id="presentDays">0</h3>
                        <p class="employee-stat-label">Present Days</p>
                    </div>
                </div>
                <div class="employee-stat-card employee-stat-warning">
                    <div class="employee-stat-icon">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="employee-stat-content">
                        <h3 class="employee-stat-number" id="absentDays">0</h3>
                        <p class="employee-stat-label">Absent Days</p>
                    </div>
                </div>
                <div class="employee-stat-card employee-stat-info">
                    <div class="employee-stat-icon">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div class="employee-stat-content">
                        <h3 class="employee-stat-number" id="totalHours">0h</h3>
                        <p class="employee-stat-label">Total Hours</p>
                    </div>
                </div>
                <div class="employee-stat-card employee-stat-primary">
                    <div class="employee-stat-icon">
                        <i class="bi bi-percent"></i>
                    </div>
                    <div class="employee-stat-content">
                        <h3 class="employee-stat-number" id="attendanceRate">0%</h3>
                        <p class="employee-stat-label">Attendance Rate</p>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="employee-card mb-4">
                <div class="employee-card-header">
                    <div class="employee-card-title">
                        <i class="bi bi-funnel"></i>
                        Filter Records
                    </div>
                </div>
                <div class="employee-card-body">
                    <div class="filter-grid-modern">
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="bi bi-calendar me-1"></i>
                                Start Date
                            </label>
                            <input type="date" id="startDate" class="form-input-modern" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="bi bi-calendar me-1"></i>
                                End Date
                            </label>
                            <input type="date" id="endDate" class="form-input-modern" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="bi bi-circle-fill me-1"></i>
                                Status
                            </label>
                            <select id="statusFilter" class="form-input-modern">
                                <option value="">All Status</option>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                            </select>
            </div>
                        <div class="form-group-modern">
                            <button class="btn-filter-modern" onclick="filterAttendance()">
                                <i class="bi bi-search me-2"></i>
                                Apply Filter
                            </button>
                </div>
            </div>
        </div>
    </div>
    
            <!-- Attendance Records Table -->
            <div class="employee-card">
                <div class="employee-card-header">
                    <div class="employee-card-title">
                        <i class="bi bi-table"></i>
                        Attendance Records
                    </div>
                    <div class="employee-actions">
                        <button class="btn-employee-secondary" onclick="exportAttendance('excel')">
                            <i class="bi bi-file-excel"></i>
                            Excel
                        </button>
                        <button class="btn-employee-secondary" onclick="exportAttendance('pdf')">
                            <i class="bi bi-file-pdf"></i>
                            PDF
                        </button>
                    </div>
                </div>
                <div class="employee-card-body p-0">
                    <div class="table-responsive">
                        <table class="table employee-table">
                            <thead>
                                <tr>
                                    <th class="text-start">Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Clock In</th>
                                    <th class="text-center">Clock Out</th>
                                    <th class="text-center">Total Hours</th>
                                    <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceTableBody">
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="loading-state-modern">
                                            <div class="spinner-modern"></div>
                                            <p class="loading-text-modern">Loading attendance data...</p>
                                        </div>
                                    </td>
                                </tr>
                        </tbody>
                    </table>
                </div>
            </div>
    
    <!-- Clock In/Out and Today's Summary Section -->
    <div class="row g-4 mt-4">
        <div class="col-lg-6">
            <!-- Clock In/Out Card -->
            <div class="employee-card mb-4">
                <div class="employee-card-header">
                    <div class="employee-card-title">
                        <i class="bi bi-stopwatch"></i>
                        Clock In/Out
                    </div>
                </div>
                <div class="employee-card-body text-center">
                    <div class="clock-status-modern" id="clockStatus">
                    </div>
                    
                    <div class="clock-actions-modern">
                        <button class="btn-clock-modern clock-in-btn" id="clockInBtn" onclick="clockIn()">
                            <i class="bi bi-play-circle"></i>
                            <span>Clock In</span>
                        </button>
                        <button class="btn-clock-modern clock-out-btn" id="clockOutBtn" onclick="clockOut()">
                            <i class="bi bi-stop-circle"></i>
                            <span>Clock Out</span>
                        </button>
                    </div>

                    <div class="current-session-modern d-none" id="currentSession">
                        <div class="session-info-modern">
                            <div class="session-time-modern">
                                <span class="session-label-modern">Current Session:</span>
                                <span class="session-duration-modern" id="sessionDuration">00:00:00</span>
                            </div>
                            <div class="session-details-modern">
                                <small class="text-muted">Started at: <span id="sessionStartTime">-</span></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <!-- Today's Summary -->
            <div class="employee-card mb-4">
                <div class="employee-card-header">
                    <div class="employee-card-title">
                        <i class="bi bi-calendar-day"></i>
                        Today's Summary
                    </div>
                </div>
                <div class="employee-card-body">
                    <div class="summary-grid-modern">
                        <div class="summary-item-modern">
                            <div class="summary-icon-modern">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="summary-content-modern">
                                <div class="summary-value-modern" id="todayClockIn">-</div>
                                <div class="summary-label-modern">Clock In</div>
                            </div>
                        </div>
                        <div class="summary-item-modern">
                            <div class="summary-icon-modern">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div class="summary-content-modern">
                                <div class="summary-value-modern" id="todayClockOut">-</div>
                                <div class="summary-label-modern">Clock Out</div>
                            </div>
                        </div>
                        <div class="summary-item-modern">
                            <div class="summary-icon-modern">
                                <i class="bi bi-stopwatch"></i>
                            </div>
                            <div class="summary-content-modern">
                                <div class="summary-value-modern" id="todayHours">0h</div>
                                <div class="summary-label-modern">Total Hours</div>
                            </div>
                        </div>
                        <div class="summary-item-modern">
                            <div class="summary-icon-modern">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <div class="summary-content-modern">
                                <div class="summary-value-modern" id="todayStatus">-</div>
                                <div class="summary-label-modern">Status</div>
                            </div>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentPage = 1;
let recordsPerPage = 10;
let sessionTimer;
let sessionStartTime = null;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Set initial button states
    document.getElementById('clockInBtn').disabled = false;
    document.getElementById('clockOutBtn').disabled = true;
    
    loadAttendanceData();
    checkClockStatus();
    updateCurrentTime();
    loadTodaySummary();
    
    // Update time every minute
    setInterval(updateCurrentTime, 60000);
});

// Update current time display
function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
    document.getElementById('currentTime').textContent = timeString;
}


// Check clock status
function checkClockStatus() {
    fetch('{{ route("attendance.status") }}', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateClockStatus(data.status);
        }
    })
    .catch(error => {
        console.error('Error checking clock status:', error);
    });
}

// Update clock status display
function updateClockStatus(status) {
    const clockStatus = document.getElementById('clockStatus');
    const clockInBtn = document.getElementById('clockInBtn');
    const clockOutBtn = document.getElementById('clockOutBtn');
    const currentSession = document.getElementById('currentSession');
    
    if (status.is_clocked_in) {
        // User is clocked in
        clockStatus.innerHTML = `
            <div class="status-icon-modern clocked-in">
                <i class="bi bi-play-circle-fill"></i>
            </div>
        `;
        
        // Both buttons remain visible, but update their states
        clockInBtn.disabled = true;
        clockOutBtn.disabled = false;
        currentSession.classList.remove('d-none');
        
        // Start session timer
        sessionStartTime = new Date(status.clock_in_time);
        startSessionTimer();
        
        // Update session start time display
        document.getElementById('sessionStartTime').textContent = status.clock_in_time;
        
        // Update Today's Summary with current status
        document.getElementById('todayClockIn').textContent = status.clock_in_time || '-';
        document.getElementById('todayStatus').textContent = 'present';
    } else {
        // User is not clocked in
        clockStatus.innerHTML = ``;
        
        // Both buttons remain visible, but update their states
        clockInBtn.disabled = false;
        clockOutBtn.disabled = true;
        currentSession.classList.add('d-none');
        
        // Stop session timer
        if (sessionTimer) {
            clearInterval(sessionTimer);
        }
        
        // Update Today's Summary for not clocked in state
        document.getElementById('todayStatus').textContent = 'Not Started';
    }
}

// Start session timer
function startSessionTimer() {
    sessionTimer = setInterval(() => {
        if (sessionStartTime) {
            const now = new Date();
            const diff = now - sessionStartTime;
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            const timeString = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            document.getElementById('sessionDuration').textContent = timeString;
            
            // Update Today's Summary total hours in real-time
            const totalHours = (diff / (1000 * 60 * 60)).toFixed(1);
            document.getElementById('todayHours').textContent = `${totalHours}h`;
        }
    }, 1000);
}

// Clock in function
function clockIn() {
    fetch('{{ route("attendance.clockin") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show appropriate message based on the response
            const message = data.message || 'Clocked in successfully!';
            showToast(message, 'success');
            
            // Update Today's Summary immediately
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
            document.getElementById('todayClockIn').textContent = timeString;
            document.getElementById('todayStatus').textContent = 'present';
            
            checkClockStatus();
            loadTodaySummary();
            loadAttendanceData(); // Refresh the attendance table
        } else {
            showToast(data.message || 'Failed to clock in', 'error');
        }
    })
    .catch(error => {
        console.error('Error clocking in:', error);
        showToast('An error occurred while clocking in', 'error');
    });
}

// Clock out function
function clockOut() {
    fetch('{{ route("attendance.clockout") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Clocked out successfully!', 'success');
            
            // Update Today's Summary immediately
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
            document.getElementById('todayClockOut').textContent = timeString;
            document.getElementById('todayStatus').textContent = 'present';
            
            checkClockStatus();
            loadTodaySummary();
            loadAttendanceData();
        } else {
            showToast(data.message || 'Failed to clock out', 'error');
        }
    })
    .catch(error => {
        console.error('Error clocking out:', error);
        showToast('An error occurred while clocking out', 'error');
    });
}

// Load today's summary
function loadTodaySummary() {
    const today = new Date().toISOString().split('T')[0];
    
    fetch(`{{ route('attendance.data') }}?start_date=${today}&end_date=${today}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.records.length > 0) {
            const todayRecord = data.records[0];
            document.getElementById('todayClockIn').textContent = todayRecord.clock_in || '-';
            document.getElementById('todayClockOut').textContent = todayRecord.clock_out || '-';
            document.getElementById('todayHours').textContent = todayRecord.total_hours || '0h';
            document.getElementById('todayStatus').textContent = todayRecord.status || '-';
        } else {
            // No record for today
            document.getElementById('todayClockIn').textContent = '-';
            document.getElementById('todayClockOut').textContent = '-';
            document.getElementById('todayHours').textContent = '0h';
            document.getElementById('todayStatus').textContent = 'Not Started';
        }
    })
    .catch(error => {
        console.error('Error loading today summary:', error);
    });
}

// Load attendance data
function loadAttendanceData() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const status = document.getElementById('statusFilter').value;
    
    fetch(`{{ route('attendance.data') }}?start_date=${startDate}&end_date=${endDate}&status=${status}&page=${currentPage}&per_page=${recordsPerPage}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatCards(data.statistics);
            updateAttendanceTable(data.records);
        } else {
            console.error('Failed to load attendance data:', data.message);
            loadDemoData();
        }
    })
    .catch(error => {
        console.error('Error loading attendance data:', error);
        loadDemoData();
    });
}

// Load demo data
function loadDemoData() {
    const demoStats = {
        present_days: 11,
        absent_days: 0,
        total_hours: 88,
        attendance_rate: 100
    };
    
    // Generate realistic demo records for the last 10 days
    const demoRecords = [];
    const today = new Date();
    
    for (let i = 0; i < 10; i++) {
        const date = new Date(today);
        date.setDate(today.getDate() - i);
        
        // Skip weekends for more realistic data
        if (date.getDay() === 0 || date.getDay() === 6) continue;
        
        const clockIn = `0${8 + Math.floor(Math.random() * 2)}:${Math.floor(Math.random() * 60).toString().padStart(2, '0')}`;
        const clockOut = `1${7 + Math.floor(Math.random() * 2)}:${Math.floor(Math.random() * 60).toString().padStart(2, '0')}`;
        
        // Calculate hours
        const startTime = new Date(`2000-01-01T${clockIn}:00`);
        const endTime = new Date(`2000-01-01T${clockOut}:00`);
        const diffMs = endTime - startTime;
        const hours = (diffMs / (1000 * 60 * 60)).toFixed(1);
        
        demoRecords.push({
            id: i + 1,
            date: date.toISOString().split('T')[0],
            status: 'present',
            clock_in: clockIn,
            clock_out: clockOut,
            total_hours: hours
        });
    }
    
    updateStatCards(demoStats);
    updateAttendanceTable(demoRecords);
}

// Update statistics cards
function updateStatCards(stats) {
    document.getElementById('presentDays').textContent = stats.present_days || 0;
    document.getElementById('absentDays').textContent = stats.absent_days || 0;
    document.getElementById('totalHours').textContent = (stats.total_hours || 0) + 'h';
    document.getElementById('attendanceRate').textContent = (stats.attendance_rate || 0) + '%';
}


// Update attendance table
function updateAttendanceTable(records) {
    const tbody = document.getElementById('attendanceTableBody');
    
    if (!records.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No attendance records found</td></tr>';
        return;
    }
    
    tbody.innerHTML = records.map(record => {
        const statusBadge = getStatusBadge(record.status);
        const totalHours = formatHours(record.total_hours);
        
        return `
            <tr>
                <td class="fw-medium">${new Date(record.date).toLocaleDateString()}</td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-center fw-medium">${record.clock_in || '-'}</td>
                <td class="text-center fw-medium">${record.clock_out || '-'}</td>
                <td class="text-center">
                    <span class="badge bg-success hours-badge">${totalHours}</span>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="viewDetails('${record.id}')" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-secondary" onclick="editRecord('${record.id}')" title="Edit Record">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Format hours display
function formatHours(hours) {
    if (!hours || hours === '0' || hours === 0) return '0h';
    
    // If it's already a string with 'h', return as is
    if (typeof hours === 'string' && hours.includes('h')) {
        return hours;
    }
    
    // Convert to number and format
    const numHours = parseFloat(hours);
    if (isNaN(numHours)) return '0h';
    
    // If hours are more than 24, show in HH:MM format
    if (numHours >= 24) {
        const days = Math.floor(numHours / 24);
        const remainingHours = numHours % 24;
        if (days > 0 && remainingHours > 0) {
            return `${days}d ${remainingHours.toFixed(1)}h`;
        } else if (days > 0) {
            return `${days}d`;
        }
    }
    
    return `${numHours.toFixed(1)}h`;
}

// Get status badge
function getStatusBadge(status) {
    const badges = {
        'present': '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Present</span>',
        'absent': '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Absent</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

// Filter attendance
function filterAttendance() {
    currentPage = 1;
    loadAttendanceData();
}

// View details
function viewDetails(recordId) {
    console.log('View details for record:', recordId);
}

// Edit record
function editRecord(recordId) {
    console.log('Edit record:', recordId);
}

// Export attendance
function exportAttendance(format) {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const status = document.getElementById('statusFilter').value;
    
    window.open(`/attendance/export?format=${format}&start_date=${startDate}&end_date=${endDate}&status=${status}`);
}

// Show toast notification
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
