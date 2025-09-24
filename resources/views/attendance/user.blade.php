@extends('layouts.app')

@push('meta')
<meta name="user-id" content="{{ auth()->id() }}">
<meta name="user-email" content="{{ auth()->user()->email }}">
@endpush

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-3 gap-3">
    <div>
        <h4 class="mb-1">📅 My Attendance</h4>
        <small class="text-muted">Track your work hours and attendance</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- Real-time Clock -->
        <div class="current-time-display p-2 rounded-3 text-center">
            <small class="text-light d-block opacity-75">Current Time</small>
            <strong id="current-time" class="text-light h6 mb-0"></strong>
        </div>
        <!-- Date Display -->
        <div class="bg-light p-2 rounded-3 text-center">
            <small class="text-muted d-block">Today</small>
            <strong class="text-dark">{{ \Carbon\Carbon::now()->format('M j, Y') }}</strong>
        </div>
    </div>
    <div>
        <form action="{{ route('attendance.index') }}" method="GET" class="d-flex gap-2">
            <input type="date" name="start_date" value="{{ $start }}" class="form-control form-control-sm rounded-input" placeholder="Start Date" />
            <input type="date" name="end_date" value="{{ $end }}" class="form-control form-control-sm rounded-input" placeholder="End Date" />
            <button class="btn btn-primary btn-sm btn-rounded" type="submit">📊 Filter</button>
            @if($start || $end)
                <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary btn-sm btn-rounded">🗂️ All</a>
            @endif
        </form>
    </div>
</div>

<div class="row g-3">

    <!-- Quick Actions Card -->
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-3">
                <h6 class="card-title mb-2">⚡ Quick Actions</h6>
                <div class="d-grid gap-2 d-md-flex">
                    <button class="btn btn-success btn-rounded flex-fill" onclick="clockIn()">
                        <span class="me-1">⏰</span> Clock In
                    </button>
                    <button class="btn btn-warning btn-rounded flex-fill" onclick="clockOut()">
                        <span class="me-1">🏃</span> Clock Out
                    </button>
                </div>
                <div class="mt-2 p-2 bg-light rounded" id="attendance-status">
                    <small class="text-muted">Status: <span id="current-attendance-status">🔄 Please log in to view status</span></small>
                </div>
                
                <!-- Debug Section - Remove after fixing authentication -->
                <div class="mt-2 p-2 bg-warning-subtle border border-warning rounded">
                    <h6 class="text-warning-emphasis mb-2">🐛 Debug: Authentication Status</h6>
                    <button onclick="debugAuthStatus()" class="btn btn-sm btn-outline-warning me-1">
                        Test Auth Status
                    </button>
                    <button onclick="debugSessionInfo()" class="btn btn-sm btn-outline-info me-1">
                        Check Session
                    </button>
                    <button onclick="testAttendanceStatus()" class="btn btn-sm btn-outline-success">
                        Test Attendance API
                    </button>
                    <div id="debug-output" class="mt-2 small text-dark" style="max-height: 150px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Summary -->
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-3">
                <h6 class="card-title mb-2">📈 Today's Summary</h6>
                @if(isset($dailyUpdateToday))
                    <div class="alert alert-success mb-2 py-2">
                        <div class="d-flex align-items-start">
                            <div class="me-2">📝</div>
                            <div>
                                <strong>Daily Update:</strong>
                                <div class="small text-dark" style="white-space: pre-line;">{{ Str::limit($dailyUpdateToday->summary, 100) }}</div>
                            </div>
                            <a href="{{ url('/daily-update') }}" class="btn btn-sm btn-outline-success ms-auto">Edit</a>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning d-flex align-items-center mb-2 py-2">
                        <div class="me-2">📝</div>
                        <div>
                            <strong>No Daily Update yet.</strong>
                            <div class="small">work on this</div>
                        </div>
                        <a href="{{ url('/daily-update') }}" class="btn btn-sm btn-primary ms-auto">Edit</a>
                    </div>
                @endif
                @php
                    $today = \Carbon\Carbon::now()->startOfDay();
                    // Get today's attendance directly from database to avoid pagination issues
                    $todayAttendance = \App\Models\Attendance::where('user_id', auth()->id())
                        ->where('date', $today->toDateString())
                        ->orderBy('created_at', 'desc')
                        ->first();
                @endphp
                
                @if($todayAttendance)
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3">
                                <div class="text-center">
                                    <div class="h5 text-success mb-1">
                                        @if($todayAttendance->clock_in)
                                            {{ $todayAttendance->clock_in->setTimezone(config('app.timezone', 'UTC'))->format('g:i A') }}
                                        @else
                                            --:--
                                        @endif
                                    </div>
                                    <small class="text-muted fw-medium">⏰ Clock In</small>
                                </div>
                                
                                <div class="text-center">
                                    <div class="h5 text-warning mb-1">
                                        @if($todayAttendance->clock_out)
                                            {{ $todayAttendance->clock_out->setTimezone(config('app.timezone', 'UTC'))->format('g:i A') }}
                                        @else
                                            @if($todayAttendance->clock_in)
                                                <span class="text-muted">Still Working...</span>
                                            @else
                                                --:--
                                            @endif
                                        @endif
                                    </div>
                                    <small class="text-muted fw-medium">🏃 Clock Out</small>
                                </div>
                                
                                <div class="text-center">
                                    <div class="h5 text-primary mb-1">{{ $todayAttendance->total_hours }}</div>
                                    <small class="text-muted fw-medium">⏱️ Duration</small>
                                </div>
                            </div>
                        </div>
                        
                        @if($todayAttendance->clock_in && !$todayAttendance->clock_out)
                            <div class="col-12">
                                <div class="alert alert-info d-flex align-items-center">
                                    <div class="me-2">⏰</div>
                                    <div>
                                        <strong>Currently Working</strong>
                                        <div class="small">Started at {{ $todayAttendance->clock_in->setTimezone(config('app.timezone', 'UTC'))->format('g:i A') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center text-muted">
                        <div class="py-3">
                            <span class="display-4 opacity-50">📝</span>
                            <p class="mt-2 mb-0">No attendance record for today</p>
                            <small>Start by clocking in!</small>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Attendance History -->
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">🕒 Attendance History</h5>
                    <span class="badge bg-light text-dark">{{ $attendance->total() }} records</span>
                </div>
            </div>
            <div class="card-body">
                @if($attendance->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="15%">📅 Date</th>
                                    <th width="15%">⏰ Clock In</th>
                                    <th width="15%">🏃 Clock Out</th>
                                    <th width="15%">⏱️ Duration</th>
                                    <th width="20%">📊 Status</th>
                                    <th width="20%">📝 Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendance as $record)
                                <tr class="{{ $record->date->isToday() ? 'table-primary' : '' }}">
                                    <td>
                                        <div class="fw-medium">{{ $record->date->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ $record->date->format('l') }}</small>
                                    </td>
                                    <td>
                                        @if($record->clock_in)
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-success-subtle text-success fw-bold">
                                                    {{ $record->clock_in->setTimezone(config('app.timezone', 'UTC'))->format('g:i A') }}
                                                </span>
                                                <small class="text-muted mt-1">
                                                    {{ $record->clock_in->setTimezone(config('app.timezone', 'UTC'))->format('M j') }}
                                                </small>
                                            </div>
                                        @else
                                            <span class="text-muted">--:-- --</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->clock_out)
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-warning-subtle text-warning fw-bold">
                                                    {{ $record->clock_out->setTimezone(config('app.timezone', 'UTC'))->format('g:i A') }}
                                                </span>
                                                <small class="text-muted mt-1">
                                                    {{ $record->clock_out->setTimezone(config('app.timezone', 'UTC'))->format('M j') }}
                                                </small>
                                            </div>
                                        @else
                                            @if($record->clock_in)
                                                <span class="text-muted fst-italic">Still working...</span>
                                            @else
                                                <span class="text-muted">--:-- --</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->total_minutes > 0)
                                            <span class="fw-medium text-primary">{{ $record->total_hours }}</span>
                                        @else
                                            <span class="text-muted">0:00</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->status === 'Complete')
                                            <span class="badge bg-success">✅ Complete</span>
                                        @elseif($record->status === 'Clocked In')
                                            <span class="badge bg-warning">⏰ In Progress</span>
                                        @else
                                            <span class="badge bg-secondary">❌ Not Started</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->is_missing_clock_out)
                                            <small class="text-warning">⚠️ Missing clock out</small>
                                        @elseif($record->total_minutes > 480)
                                            <small class="text-info">💪 Long day ({{ $record->total_hours }})</small>
                                        @elseif($record->total_minutes < 240 && $record->total_minutes > 0)
                                            <small class="text-muted">⏱️ Short day</small>
                                        @else
                                            <small class="text-muted">Regular</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Showing {{ $attendance->firstItem() }} to {{ $attendance->lastItem() }} of {{ $attendance->total() }} records
                        </div>
                        <div>
                            {{ $attendance->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <span class="display-1 opacity-25">📝</span>
                        <h6 class="mt-3">No Attendance Records Found</h6>
                        <p class="mb-3">
                            @if($start || $end)
                                No records found for the selected date range.
                            @else
                                You haven't started tracking your attendance yet.
                            @endif
                        </p>
                        @if(!($start || $end))
                            <button class="btn btn-primary btn-rounded" onclick="clockIn()">
                                <span class="me-1">🚀</span> Start Your First Day
                            </button>
                        @else
                            <a href="{{ route('attendance.index') }}" class="btn btn-outline-primary btn-rounded">
                                <span class="me-1">📊</span> View All Records
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for attendance functionality -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const currentAttendanceStatus = document.getElementById('current-attendance-status');
    const attendanceStatusContainer = document.getElementById('attendance-status');
    const currentTimeEl = document.getElementById('current-time');
    
    // Real-time clock update
    function updateCurrentTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', {
            hour12: true,
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit'
        });
        if (currentTimeEl) {
            currentTimeEl.textContent = timeString;
        }
    }
    
    // Update clock every second
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
    
    // Store current attendance data for live updates
    let currentAttendanceData = null;
    
    // Update work duration in real-time if user is clocked in
    function updateWorkDuration() {
        if (currentAttendanceData && currentAttendanceData.is_clocked_in && !currentAttendanceData.is_clocked_out) {
            // User is currently working, update duration
            fetchAttendanceStatus();
        }
    }
    
    // Update duration every minute for active sessions
    setInterval(updateWorkDuration, 60000);
    
    // Fetch attendance status
    async function fetchAttendanceStatus() {
        try {
            console.log('🔄 Fetching attendance status...');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };
            
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }
            
            const response = await fetch('/attendance/status', {
                method: 'GET',
                headers: headers,
                credentials: 'same-origin'
            });
            
            console.log('📈 API Response status:', response.status, response.statusText);
            
            if (response.ok) {
                const contentType = response.headers.get('content-type');
                console.log('📊 Content-Type:', contentType);
                
                if (contentType && contentType.includes('application/json')) {
                    const status = await response.json();
                    console.log('📊 API Response data:', status);
                    updateAttendanceStatus(status);
                } else {
                    // Received HTML instead of JSON - likely an error page
                    const htmlText = await response.text();
                    console.error('❌ Received HTML instead of JSON:', htmlText.substring(0, 200));
                    
                    if (htmlText.includes('login') || htmlText.includes('Login')) {
                        currentAttendanceStatus.textContent = '🔒 Please log in to view status';
                        // Optionally redirect to login
                        // window.location.href = '/login';
                    } else {
                        currentAttendanceStatus.textContent = '⚙️ System error - please refresh';
                    }
                }
            } else {
                console.error('❌ API failed with status:', response.status);
                const errorText = await response.text();
                console.error('❌ Error response:', errorText);
                
                if (response.status === 401) {
                    currentAttendanceStatus.textContent = '🔒 Session expired - Please refresh page';
                    // Auto-refresh after 3 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } else if (response.status === 403) {
                    currentAttendanceStatus.textContent = '❌ Access denied';
                } else if (response.status === 404) {
                    currentAttendanceStatus.textContent = '❌ API endpoint not found';
                } else {
                    currentAttendanceStatus.textContent = `❌ Error ${response.status}: ${response.statusText}`;
                }
            }
        } catch (error) {
            console.error('❌ Failed to fetch attendance status:', error);
            currentAttendanceStatus.textContent = 'Status unavailable - ' + error.message;
        }
    }
    
    function updateAttendanceStatus(status) {
        if (!status || !status.success) {
            currentAttendanceStatus.textContent = 'Status unavailable';
            return;
        }
        
        // Store current status for real-time updates
        currentAttendanceData = status;
        
        switch (status.status) {
            case 'not_started':
                currentAttendanceStatus.textContent = '⭐ Ready to start your workday';
                attendanceStatusContainer.className = 'mt-3 p-2 bg-light rounded-3 border';
                attendanceStatusContainer.style.animation = 'none';
                break;
                
            case 'clocked_in':
                const duration = status.current_duration || status.total_hours || '0:00';
                currentAttendanceStatus.textContent = `⏰ Currently working - ${duration} elapsed`;
                attendanceStatusContainer.className = 'mt-3 p-2 bg-success-subtle rounded-3 border border-success';
                
                // Add pulsing effect for active status
                attendanceStatusContainer.style.animation = 'pulse 2s infinite';
                break;
                
            case 'can_start_new':
                const completedHours = status.total_hours_today || '0:00';
                const sessionsCount = status.sessions_completed || 0;
                currentAttendanceStatus.textContent = `✅ ${sessionsCount} session(s) completed today (${completedHours}). Ready to start new session.`;
                attendanceStatusContainer.className = 'mt-3 p-2 bg-info-subtle rounded-3 border border-info';
                attendanceStatusContainer.style.animation = 'none';
                break;
                
            case 'completed':
                const totalHours = status.total_hours || '0:00';
                currentAttendanceStatus.textContent = `✅ Work completed for today - Total: ${totalHours}`;
                attendanceStatusContainer.className = 'mt-3 p-2 bg-primary-subtle rounded-3 border border-primary';
                attendanceStatusContainer.style.animation = 'none';
                break;
                
            default:
                // Show the actual message from backend for debugging
                currentAttendanceStatus.textContent = status.message || `Unknown status: ${status.status}`;
                attendanceStatusContainer.className = 'mt-3 p-2 bg-warning-subtle rounded-3 border border-warning';
                attendanceStatusContainer.style.animation = 'none';
                console.log('Unhandled status:', status); // Debug log
        }
    }

    // Clock in function
    window.clockIn = async function() {
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        
        try {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Clocking In...';
            
            const response = await fetch('/attendance/clock-in', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification('success', data.message);
                fetchAttendanceStatus();
                setTimeout(() => window.location.reload(), 2000);
            } else {
                showNotification('warning', data.message);
            }
        } catch (error) {
            showNotification('error', 'Failed to clock in. Please try again.');
        } finally {
            button.disabled = false;
            button.innerHTML = originalContent;
        }
    };

    // Clock out function
    window.clockOut = async function() {
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        
        try {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Clocking Out...';
            
            const response = await fetch('/attendance/clock-out', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification('success', `${data.message}. Total work time: ${data.total_hours}`);
                fetchAttendanceStatus();
                setTimeout(() => window.location.reload(), 2000);
            } else {
                showNotification('warning', data.message);
            }
        } catch (error) {
            showNotification('error', 'Failed to clock out. Please try again.');
        } finally {
            button.disabled = false;
            button.innerHTML = originalContent;
        }
    };
    
    // Notification system
    function showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <strong>${type.charAt(0).toUpperCase() + type.slice(1)}!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
    
    // Debug authentication status
    console.log('🔐 Auth Debug Info:');
    console.log('- Current URL:', window.location.href);
    console.log('- User Agent:', navigator.userAgent.substring(0, 100));
    
    // Check for auth indicators on page
    const hasAuthUser = document.querySelector('meta[name="user-id"]');
    const hasAuthToken = document.querySelector('meta[name="csrf-token"]');
    console.log('- CSRF Token present:', !!hasAuthToken);
    console.log('- User ID meta present:', !!hasAuthUser);
    
    // Wait a bit before making API call to ensure page is fully loaded
    setTimeout(() => {
        fetchAttendanceStatus();
    }, 1000);
    
    // Also try again every 30 seconds in case of temporary network issues
    setInterval(() => {
        if (currentAttendanceStatus.textContent.includes('unavailable') || 
            currentAttendanceStatus.textContent.includes('error') ||
            currentAttendanceStatus.textContent.includes('log in')) {
            console.log('🔄 Retrying attendance status due to error state...');
            fetchAttendanceStatus();
        }
    }, 30000);
    
    // DEBUG FUNCTIONS - Remove after fixing authentication issues
    window.debugAuthStatus = async function() {
        const debugOutput = document.getElementById('debug-output');
        debugOutput.innerHTML = '<div class="text-info">🔄 Testing authentication...</div>';
        
        try {
            const response = await fetch('/debug/auth-status', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            debugOutput.innerHTML = `
                <div class="border rounded p-2 bg-light">
                    <strong>🔐 Auth Status Response:</strong><br>
                    <div class="small mt-1">
                        <strong>Status:</strong> ${response.status} ${response.statusText}<br>
                        <strong>Authenticated:</strong> ${data.authenticated ? '✅ Yes' : '❌ No'}<br>
                        <strong>User ID:</strong> ${data.user_id || 'N/A'}<br>
                        <strong>User Email:</strong> ${data.user_email || 'N/A'}<br>
                        <strong>Session ID:</strong> ${data.session_id ? data.session_id.substring(0, 16) + '...' : 'N/A'}<br>
                        <strong>CSRF Token:</strong> ${data.csrf_token ? data.csrf_token.substring(0, 16) + '...' : 'N/A'}<br>
                        <strong>Server Time:</strong> ${data.current_time || 'N/A'}<br>
                        <strong>Middleware:</strong> ${data.middleware_applied || 'N/A'}
                    </div>
                </div>
            `;
            
            console.log('🔐 Debug Auth Response:', data);
        } catch (error) {
            debugOutput.innerHTML = `<div class="text-danger">❌ Error: ${error.message}</div>`;
            console.error('🔐 Debug Auth Error:', error);
        }
    };
    
    window.debugSessionInfo = function() {
        const debugOutput = document.getElementById('debug-output');
        const userIdMeta = document.querySelector('meta[name="user-id"]');
        const userEmailMeta = document.querySelector('meta[name="user-email"]');
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        
        debugOutput.innerHTML = `
            <div class="border rounded p-2 bg-light">
                <strong>🖥️ Frontend Session Info:</strong><br>
                <div class="small mt-1">
                    <strong>Current URL:</strong> ${window.location.href}<br>
                    <strong>User ID Meta:</strong> ${userIdMeta?.content || '❌ Missing'}<br>
                    <strong>User Email Meta:</strong> ${userEmailMeta?.content || '❌ Missing'}<br>
                    <strong>CSRF Meta:</strong> ${csrfMeta?.content ? csrfMeta.content.substring(0, 16) + '...' : '❌ Missing'}<br>
                    <strong>Cookies:</strong> ${document.cookie ? 'Present (' + document.cookie.split(';').length + ' cookies)' : '❌ No cookies'}<br>
                    <strong>Local Storage:</strong> ${localStorage.length} items<br>
                    <strong>Session Storage:</strong> ${sessionStorage.length} items<br>
                    <strong>User Agent:</strong> ${navigator.userAgent.substring(0, 60)}...
                </div>
            </div>
        `;
        
        console.log('🖥️ Session Debug Info:', {
            url: window.location.href,
            userIdMeta: userIdMeta?.content,
            userEmailMeta: userEmailMeta?.content,
            csrfMeta: csrfMeta?.content?.substring(0, 16) + '...',
            cookies: document.cookie,
            localStorage: localStorage.length,
            sessionStorage: sessionStorage.length
        });
    };
    
    window.testAttendanceStatus = async function() {
        const debugOutput = document.getElementById('debug-output');
        debugOutput.innerHTML = '<div class="text-info">🔄 Testing attendance status APIs...</div>';
        
        const results = [];
        
        // Test 1: Legacy Web Route
        try {
            const response = await fetch('/attendance/status', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                credentials: 'same-origin'
            });
            
            let data;
            let contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                data = { error: 'Received HTML instead of JSON', content: text.substring(0, 200) };
            }
            
            results.push({
                name: 'Legacy Web Route (/attendance/status)',
                status: response.status,
                statusText: response.statusText,
                contentType: contentType,
                data: data
            });
        } catch (error) {
            results.push({
                name: 'Legacy Web Route (/attendance/status)',
                error: error.message
            });
        }
        
        // Test 2: Legacy API Route
        try {
            const response = await fetch('/api/attendance/status', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                credentials: 'same-origin'
            });
            
            let data;
            let contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                data = { error: 'Received HTML instead of JSON', content: text.substring(0, 200) };
            }
            
            results.push({
                name: 'Legacy API Route (/api/attendance/status)',
                status: response.status,
                statusText: response.statusText,
                contentType: contentType,
                data: data
            });
        } catch (error) {
            results.push({
                name: 'Legacy API Route (/api/attendance/status)',
                error: error.message
            });
        }
        
        // Test 3: Enhanced Sessions API Route
        try {
            const response = await fetch('/api/attendance-sessions/status', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                credentials: 'same-origin'
            });
            
            let data;
            let contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                data = { error: 'Received HTML instead of JSON', content: text.substring(0, 200) };
            }
            
            results.push({
                name: 'Enhanced Sessions API (/api/attendance-sessions/status)',
                status: response.status,
                statusText: response.statusText,
                contentType: contentType,
                data: data
            });
        } catch (error) {
            results.push({
                name: 'Enhanced Sessions API (/api/attendance-sessions/status)',
                error: error.message
            });
        }
        
        // Display results
        let output = '<div class="border rounded p-2 bg-light"><strong>🔍 API Test Results:</strong>';
        
        results.forEach((result, index) => {
            output += `<hr class="my-2"><div class="small"><strong>${index + 1}. ${result.name}</strong><br>`;
            
            if (result.error) {
                output += `<span class="text-danger">❌ Error: ${result.error}</span><br>`;
            } else {
                output += `Status: ${result.status} ${result.statusText}<br>`;
                output += `Content-Type: ${result.contentType}<br>`;
                
                if (result.data.error) {
                    output += `<span class="text-warning">⚠️ ${result.data.error}</span><br>`;
                    if (result.data.content) {
                        output += `<code class="small">${result.data.content}...</code><br>`;
                    }
                } else {
                    output += `Success: ${result.data.success ? '✅ Yes' : '❌ No'}<br>`;
                    output += `Status: ${result.data.status || 'N/A'}<br>`;
                    output += `Message: ${result.data.message || 'N/A'}<br>`;
                    
                    if (result.data.can_clock_in !== undefined) {
                        output += `Can Clock In: ${result.data.can_clock_in ? '✅ Yes' : '❌ No'}<br>`;
                    }
                    if (result.data.can_clock_out !== undefined) {
                        output += `Can Clock Out: ${result.data.can_clock_out ? '✅ Yes' : '❌ No'}<br>`;
                    }
                    if (result.data.total_sessions !== undefined) {
                        output += `Sessions Today: ${result.data.total_sessions}<br>`;
                    }
                }
            }
            
            output += '</div>';
        });
        
        output += '</div>';
        debugOutput.innerHTML = output;
        
        console.log('🔍 API Test Results:', results);
    };
});
</script>
@endpush
@endsection
