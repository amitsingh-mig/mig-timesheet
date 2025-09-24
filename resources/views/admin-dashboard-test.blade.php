<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <div class="container-fluid p-4">
        <h1 class="mb-4">🛠️ Admin Dashboard API Test</h1>
        
        <!-- Current User Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Current User Information</h5>
            </div>
            <div class="card-body">
                <p><strong>User:</strong> {{ auth()->user()->name }}</p>
                <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                <p><strong>Role:</strong> {{ auth()->user()->role ? auth()->user()->role->name : 'No Role' }}</p>
                <p><strong>Is Admin:</strong> {{ auth()->user()->role && auth()->user()->role->name === 'admin' ? 'Yes' : 'No' }}</p>
            </div>
        </div>

        <!-- Test Dashboard Stats API -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Dashboard Statistics API Test</h5>
                <button class="btn btn-primary btn-sm" onclick="testDashboardStats()">Test Stats API</button>
            </div>
            <div class="card-body">
                <div id="statsResults">
                    <div class="text-muted">Click "Test Stats API" to load statistics...</div>
                </div>
            </div>
        </div>

        <!-- Test Recent Activity API -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Recent Activity API Test</h5>
                <button class="btn btn-primary btn-sm" onclick="testRecentActivity()">Test Activity API</button>
            </div>
            <div class="card-body">
                <div id="activityResults">
                    <div class="text-muted">Click "Test Activity API" to load recent activity...</div>
                </div>
            </div>
        </div>

        <!-- Test User Management API -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>User Management API Test</h5>
                <button class="btn btn-primary btn-sm" onclick="testUserManagement()">Test Users API</button>
            </div>
            <div class="card-body">
                <div id="usersResults">
                    <div class="text-muted">Click "Test Users API" to load users...</div>
                </div>
            </div>
        </div>

        <!-- Database Direct Check -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Database Direct Check (Raw Database Queries)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-primary">{{ \App\Models\User::whereHas('role', function($query) { $query->where('name', '!=', 'admin'); })->where('email_verified_at', '!=', null)->count() }}</h4>
                                <small>Active Employees<br><span class="text-muted">(excluding admins)</span></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-success">{{ \App\Models\Attendance::whereDate('date', today())->whereNotNull('clock_in')->distinct('user_id')->count() }}</h4>
                                <small>Present Today<br><span class="text-muted">(clocked in)</span></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                @php
                                    $thisMonth = now()->startOfMonth();
                                    $endOfMonth = now()->endOfMonth();
                                    $monthlyHours = \App\Models\Timesheet::whereBetween('date', [$thisMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])->sum('hours_worked');
                                @endphp
                                <h4 class="text-info">{{ round($monthlyHours, 1) }}h</h4>
                                <small>Hours This Month<br><span class="text-muted">({{ $thisMonth->format('M Y') }})</span></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                @php
                                    $employees = \App\Models\User::whereHas('role', function($query) { $query->where('name', '!=', 'admin'); })->count();
                                    $workingDaysThisMonth = 0;
                                    $current = now()->startOfMonth();
                                    while ($current <= now()) {
                                        if ($current->dayOfWeek !== 0 && $current->dayOfWeek !== 6) {
                                            $workingDaysThisMonth++;
                                        }
                                        $current->addDay();
                                    }
                                    $attendanceRecords = \App\Models\Attendance::whereBetween('date', [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')])->whereNotNull('clock_in')->distinct(['user_id', 'date'])->count();
                                    $expectedRecords = $employees * $workingDaysThisMonth;
                                    $avgAttendance = $expectedRecords > 0 ? round(($attendanceRecords / $expectedRecords) * 100, 1) : 0;
                                @endphp
                                <h4 class="text-warning">{{ $avgAttendance }}%</h4>
                                <small>Avg Attendance<br><span class="text-muted">({{ $workingDaysThisMonth }} working days)</span></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <strong>Debug Info:</strong> 
                        Total DB Users: {{ \App\Models\User::count() }} | 
                        Total Attendance Records: {{ \App\Models\Attendance::count() }} | 
                        Total Timesheets: {{ \App\Models\Timesheet::count() }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function testDashboardStats() {
            const resultsDiv = document.getElementById('statsResults');
            resultsDiv.innerHTML = '<div class="text-info">Loading statistics...</div>';
            
            fetch('/admin/users/data?stats_only=true', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                console.log('Stats Response Status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Stats Response Data:', data);
                resultsDiv.innerHTML = `
                    <div class="alert alert-${data.success ? 'success' : 'danger'}">
                        <h6>API Response:</h6>
                        <pre class="mb-0">${JSON.stringify(data, null, 2)}</pre>
                    </div>
                `;
            })
            .catch(error => {
                console.error('Stats API Error:', error);
                resultsDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${error.message}
                    </div>
                `;
            });
        }

        function testRecentActivity() {
            const resultsDiv = document.getElementById('activityResults');
            resultsDiv.innerHTML = '<div class="text-info">Loading recent activity...</div>';
            
            fetch('/attendance/data?limit=10', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                console.log('Activity Response Status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Activity Response Data:', data);
                resultsDiv.innerHTML = `
                    <div class="alert alert-${data.success ? 'success' : 'danger'}">
                        <h6>API Response:</h6>
                        <pre class="mb-0" style="max-height: 300px; overflow-y: auto;">${JSON.stringify(data, null, 2)}</pre>
                    </div>
                `;
            })
            .catch(error => {
                console.error('Activity API Error:', error);
                resultsDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${error.message}
                    </div>
                `;
            });
        }

        function testUserManagement() {
            const resultsDiv = document.getElementById('usersResults');
            resultsDiv.innerHTML = '<div class="text-info">Loading users...</div>';
            
            fetch('/admin/users/data', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                console.log('Users Response Status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Users Response Data:', data);
                resultsDiv.innerHTML = `
                    <div class="alert alert-${data.success ? 'success' : 'danger'}">
                        <h6>API Response:</h6>
                        <pre class="mb-0" style="max-height: 300px; overflow-y: auto;">${JSON.stringify(data, null, 2)}</pre>
                    </div>
                `;
            })
            .catch(error => {
                console.error('Users API Error:', error);
                resultsDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${error.message}
                    </div>
                `;
            });
        }

        // Auto-test all APIs on page load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(testDashboardStats, 1000);
            setTimeout(testRecentActivity, 2000);
            setTimeout(testUserManagement, 3000);
        });
    </script>
</body>
</html>
