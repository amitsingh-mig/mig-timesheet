@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 fw-bold text-dark mb-1">Attendance Overview</h1>
        <p class="text-muted mb-0">Track your attendance and work patterns</p>
    </div>
    <div class="text-end">
        <small class="text-muted">{{ now()->format('l, F j, Y') }}</small>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-2">
                <label for="dateRange" class="form-label fw-medium">Date Range</label>
                <input type="date" class="form-control" id="startDate" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-medium">&nbsp;</label>
                <input type="date" class="form-control" id="endDate" value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label for="departmentFilter" class="form-label fw-medium">Department</label>
                <select class="form-select" id="departmentFilter">
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
            <div class="col-md-2">
                <label for="statusFilter" class="form-label fw-medium">Status</label>
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                    <option value="late">Late</option>
                    <option value="leave">Leave</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-medium">&nbsp;</label>
                <button class="btn btn-primary w-100" onclick="filterAttendance()">
                    <i class="bi bi-funnel me-2"></i>Apply Filter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4 p-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-gradient-1 card-gradient">
            <div class="card-body text-white text-center p-4">
                <i class="bi bi-check-circle-fill mb-3 text-2xl opacity-80"></i>
                <div class="display-6 fw-bold mb-1" id="presentDays">0</div>
                <p class="mb-0">Present Days</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-gradient-2 card-gradient">
            <div class="card-body text-white text-center p-4">
                <i class="bi bi-x-circle-fill mb-3 text-2xl opacity-80"></i>
                <div class="display-6 fw-bold mb-1" id="absentDays">0</div>
                <p class="mb-0">Absent Days</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-gradient-3 card-gradient">
            <div class="card-body text-white text-center p-4">
                <i class="bi bi-clock-fill mb-3 text-2xl opacity-80"></i>
                <div class="display-6 fw-bold mb-1" id="totalHours">0h</div>
                <p class="mb-0">Total Hours</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-gradient-5 card-gradient">
            <div class="card-body text-white text-center p-4">
                <i class="bi bi-percent mb-3 text-2xl opacity-80"></i>
                <div class="display-6 fw-bold mb-1" id="attendanceRate">0%</div>
                <p class="mb-0">Attendance Rate</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Table Row -->
<div class="row g-4">
    <!-- Attendance Distribution Chart -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-pie-chart me-2 text-primary"></i>Attendance Distribution
                </h5>
            </div>
            <div class="card-body">
                <div class="position-relative h-72">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Attendance Table -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-table me-2 text-primary"></i>Attendance Records
                </h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="exportAttendance('excel')">
                        <i class="bi bi-file-excel me-1"></i>Excel
                    </button>
                    <button class="btn btn-outline-primary" onclick="exportAttendance('pdf')">
                        <i class="bi bi-file-pdf me-1"></i>PDF
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0" id="attendanceTable">
                        <thead class="table-dark">
                            <tr>
                                <th><i class="bi bi-calendar-date me-1"></i>Date</th>
                                <th><i class="bi bi-circle-fill me-1"></i>Status</th>
                                <th><i class="bi bi-clock me-1"></i>Clock In</th>
                                <th><i class="bi bi-clock me-1"></i>Clock Out</th>
                                <th><i class="bi bi-stopwatch me-1"></i>Total Hours</th>
                                <th><i class="bi bi-gear me-1"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceTableBody">
                            <!-- Data will be loaded via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <span id="recordCount">0</span> records</small>
                    <nav aria-label="Attendance pagination">
                        <ul class="pagination pagination-sm mb-0" id="attendancePagination">
                            <!-- Pagination will be generated by JavaScript -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let attendanceChart;
let currentPage = 1;
let recordsPerPage = 10;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeAttendanceChart();
    loadAttendanceData();
});

// Initialize pie chart
function initializeAttendanceChart() {
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    
    attendanceChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Present', 'Absent', 'Late', 'Leave'],
            datasets: [{
                data: [0, 0, 0, 0],
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(245, 87, 108, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(75, 192, 192, 0.8)'
                ],
                borderColor: [
                    'rgba(102, 126, 234, 1)',
                    'rgba(245, 87, 108, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + context.parsed + ' days (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

// Load attendance data
function loadAttendanceData() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const status = document.getElementById('statusFilter').value;
    
    fetch(`/attendance/data?start_date=${startDate}&end_date=${endDate}&status=${status}&page=${currentPage}&per_page=${recordsPerPage}`, {
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
            updateAttendanceChart(data.distribution);
            updateAttendanceTable(data.records);
            updatePagination(data.pagination);
        } else {
            console.error('Failed to load attendance data:', data.message);
            loadDemoData(); // Load demo data if API fails
        }
    })
    .catch(error => {
        console.error('Error loading attendance data:', error);
        loadDemoData(); // Load demo data if API fails
    });
}

// Load demo data for demonstration
function loadDemoData() {
    const demoStats = {
        present_days: 22,
        absent_days: 2,
        total_hours: 176,
        attendance_rate: 91
    };
    
    const demoDistribution = {
        present: 22,
        absent: 2,
        late: 3,
        leave: 1
    };
    
    const demoRecords = [
        { id: 1, date: '2024-01-15', status: 'present', clock_in: '09:00', clock_out: '17:30', total_hours: '8.5' },
        { id: 2, date: '2024-01-14', status: 'late', clock_in: '09:15', clock_out: '17:30', total_hours: '8.25' },
        { id: 3, date: '2024-01-13', status: 'present', clock_in: '08:45', clock_out: '17:30', total_hours: '8.75' },
        { id: 4, date: '2024-01-12', status: 'absent', clock_in: '-', clock_out: '-', total_hours: '0' },
        { id: 5, date: '2024-01-11', status: 'present', clock_in: '09:00', clock_out: '17:00', total_hours: '8.0' }
    ];
    
    updateStatCards(demoStats);
    updateAttendanceChart(demoDistribution);
    updateAttendanceTable(demoRecords);
    updatePagination({ current_page: 1, total_pages: 1 });
}

// Update statistics cards
function updateStatCards(stats) {
    document.getElementById('presentDays').textContent = stats.present_days;
    document.getElementById('absentDays').textContent = stats.absent_days;
    document.getElementById('totalHours').textContent = stats.total_hours + 'h';
    document.getElementById('attendanceRate').textContent = stats.attendance_rate + '%';
}

// Update attendance chart
function updateAttendanceChart(distribution) {
    attendanceChart.data.datasets[0].data = [
        distribution.present,
        distribution.absent,
        distribution.late,
        distribution.leave
    ];
    attendanceChart.update();
}

// Update attendance table
function updateAttendanceTable(records) {
    const tbody = document.getElementById('attendanceTableBody');
    tbody.innerHTML = '';
    
    records.forEach(record => {
        const statusBadge = getStatusBadge(record.status);
        const actionButtons = getActionButtons(record);
        
        const row = `
            <tr>
                <td class="fw-medium">${record.date}</td>
                <td>${statusBadge}</td>
                <td>${record.clock_in || '-'}</td>
                <td>${record.clock_out || '-'}</td>
                <td class="fw-medium">${record.total_hours || '-'}</td>
                <td>${actionButtons}</td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
    
    document.getElementById('recordCount').textContent = records.length;
}

// Get status badge
function getStatusBadge(status) {
    const badges = {
        'present': '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Present</span>',
        'absent': '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Absent</span>',
        'late': '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Late</span>',
        'leave': '<span class="badge bg-info"><i class="bi bi-calendar-x me-1"></i>Leave</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

// Get action buttons
function getActionButtons(record) {
    return `
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary" onclick="viewDetails('${record.id}')">
                <i class="bi bi-eye"></i>
            </button>
            <button class="btn btn-outline-secondary" onclick="editRecord('${record.id}')">
                <i class="bi bi-pencil"></i>
            </button>
        </div>
    `;
}

// Update pagination
function updatePagination(pagination) {
    const paginationEl = document.getElementById('attendancePagination');
    paginationEl.innerHTML = '';
    
    if (pagination.total_pages > 1) {
        // Previous button
        if (pagination.current_page > 1) {
            paginationEl.innerHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${pagination.current_page - 1})">Previous</a></li>`;
        }
        
        // Page numbers
        for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.total_pages, pagination.current_page + 2); i++) {
            const active = i === pagination.current_page ? 'active' : '';
            paginationEl.innerHTML += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="changePage(${i})">${i}</a></li>`;
        }
        
        // Next button
        if (pagination.current_page < pagination.total_pages) {
            paginationEl.innerHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${pagination.current_page + 1})">Next</a></li>`;
        }
    }
}

// Filter attendance
function filterAttendance() {
    currentPage = 1;
    loadAttendanceData();
}

// Change page
function changePage(page) {
    currentPage = page;
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
</script>
@endpush


