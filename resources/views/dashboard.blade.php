@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 fw-bold text-dark mb-1">🚀 Dashboard</h1>
        <p class="text-muted mb-0">Welcome back to your workspace</p>
    </div>
    <div class="text-end">
        <small class="text-muted">{{ now()->format('l, F j, Y') }}</small>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-5">
    <!-- Today's Hours Card -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 bg-gradient-1 card-gradient">
            <div class="card-body text-white text-center">
                <i class="bi bi-clock-fill text-2_5xl d-block mb-2"></i>
                <h2 class="h1 fw-bold mb-1" id="todayHours">0.0h</h2>
                <p class="mb-0 fs-6">Today's Hours</p>
                <small class="opacity-75">Current session</small>
            </div>
        </div>
    </div>
    
    <!-- Weekly Hours Card -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 bg-gradient-2 card-gradient">
            <div class="card-body text-white text-center">
                <i class="bi bi-calendar-week-fill text-2_5xl d-block mb-2"></i>
                <h2 class="h1 fw-bold mb-1" id="weeklyHours">0.0h</h2>
                <p class="mb-0 fs-6">Weekly Hours</p>
                <small class="opacity-75">This week</small>
            </div>
        </div>
    </div>
    
    <!-- Monthly Hours Card -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 bg-gradient-3 card-gradient">
            <div class="card-body text-white text-center">
                <i class="bi bi-calendar-month-fill text-2_5xl d-block mb-2"></i>
                <h2 class="h1 fw-bold mb-1" id="monthlyHours">0.0h</h2>
                <p class="mb-0 fs-6">Monthly Hours</p>
                <small class="opacity-75">This month</small>
            </div>
        </div>
    </div>
    
    <!-- Tasks Done Card -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 bg-gradient-5 card-gradient">
            <div class="card-body text-white text-center">
                <i class="bi bi-check-circle-fill text-2_5xl d-block mb-2"></i>
                <h2 class="h1 fw-bold mb-1" id="tasksDone">0</h2>
                <p class="mb-0 fs-6">Tasks Done</p>
                <small class="opacity-75">Completed</small>
            </div>
        </div>
    </div>
</div>

<!-- Work Time Chart Section -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-semibold">
                <i class="bi bi-bar-chart-line me-2 text-primary"></i>Employee Work Time 📈
                </h5>
                <div class="btn-group" role="group" aria-label="Chart period">
                    <button type="button" class="btn btn-outline-primary btn-sm chart-toggle" data-period="day">Day</button>
                    <button type="button" class="btn btn-outline-primary btn-sm chart-toggle active" data-period="week">Week</button>
                    <button type="button" class="btn btn-outline-primary btn-sm chart-toggle" data-period="month">Month</button>
                    <button type="button" class="btn btn-outline-primary btn-sm chart-toggle" data-period="year">Year</button>
                </div>
            </div>
            <div class="card-body py-4">
                <div class="position-relative chart-container">
                    <canvas id="clockInChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity & Progress Section -->
<div class="row g-4 mt-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-clock-history me-2 text-muted"></i>Recent Activity
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center py-3 border-bottom">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 icon-circle bg-gradient-1">
                        <i class="bi bi-clock-fill text-white"></i>
                    </div>
                    <div>
                        <p class="mb-1 fw-medium">Clocked in for the day</p>
                        <small class="text-muted">{{ now()->format('g:i A') }} • Just now</small>
                    </div>
                </div>
                <div class="d-flex align-items-center py-3 border-bottom">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 icon-circle bg-gradient-2">
                        <i class="bi bi-calendar-week-fill text-white"></i>
                    </div>
                    <div>
                        <p class="mb-1 fw-medium">Updated timesheet entry</p>
                        <small class="text-muted">1 hour ago</small>
                    </div>
                </div>
                <div class="d-flex align-items-center py-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 icon-circle bg-gradient-3">
                        <i class="bi bi-calendar-month-fill text-white"></i>
                    </div>
                    <div>
                        <p class="mb-1 fw-medium">Generated weekly report</p>
                        <small class="text-muted">2 hours ago</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-target me-2 text-muted"></i>Today's Progress
                </h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <div class="text-display-4" style="color: #4e54c8;" id="progressPercentage">0%</div>
                <p class="text-muted mb-4">Daily Goal Completion</p>
                <div class="progress mb-4 progress-custom">
                    <div class="progress-bar progress-bar-custom" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="progressBar"></div>
                </div>
                <div class="d-flex justify-content-between text-sm text-muted">
                    <span id="currentHours">0 hours</span>
                    <span id="targetHours">8 hours</span>
                </div>
                <div class="mt-3 pt-3 border-top">
                    <small class="text-muted">Keep up the great work! 🎯</small>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .card-gradient { border-radius: 1rem; overflow: hidden; }
    .bg-gradient-1 { background: linear-gradient(135deg, #4e54c8, #8f94fb) !important; }
    .bg-gradient-2 { background: linear-gradient(135deg, #06b6d4, #3b82f6) !important; }
    .bg-gradient-3 { background: linear-gradient(135deg, #10b981, #34d399) !important; }
    .bg-gradient-5 { background: linear-gradient(135deg, #f59e0b, #f97316) !important; }

    .text-2_5xl { font-size: 2.25rem; line-height: 1; }
    .icon-circle { width: 40px; height: 40px; }

    .progress-custom { height: 10px; border-radius: 9999px; background: #eef2ff; }
    .progress-bar-custom { border-radius: 9999px; background: linear-gradient(90deg, #4e54c8, #8f94fb); transition: width .3s ease; }

    .chart-container { height: 360px; max-width: 100%; overflow: hidden; }
    canvas#clockInChart { display: block; max-width: 100% !important; }

    /* Prevent layout shifts in cards */
    .card .card-body { overflow: hidden; }

    /* Buttons active state for chart toggles */
    .chart-toggle.active { color: #fff; background-color: #0d6efd; border-color: #0d6efd; }
</style>
@endpush

@push('scripts')
<script>
let workTimeChart;
let currentPeriod = 'week';

// Initialize chart when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
    loadWorkTimeData('week');
    
    // Add event listeners to toggle buttons
    document.querySelectorAll('.chart-toggle').forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            document.querySelectorAll('.chart-toggle').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Load data for selected period
            const period = this.getAttribute('data-period');
            currentPeriod = period;
            loadWorkTimeData(period);
        });
    });
});

function initializeChart() {
    const ctx = document.getElementById('clockInChart').getContext('2d');
    
    workTimeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Hours Worked',
                data: [],
                backgroundColor: 'rgba(78, 84, 200, 0.8)',
                borderColor: 'rgba(78, 84, 200, 1)',
                borderWidth: 1,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(78, 84, 200, 1)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' hours';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + 'h';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

// Global refresh function for other pages to call
window.refreshDashboard = function() {
    loadWorkTimeData(currentPeriod);
};

function loadWorkTimeData(period) {
    // Show loading state
    const chartContainer = document.querySelector('#clockInChart').parentElement;
    chartContainer.style.opacity = '0.5';
    
    fetch(`{{ route('dashboard.worktime-data') }}?period=${period}`, {
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
            // Use real data from timesheet entries
            workTimeChart.data.labels = data.labels || [];
            workTimeChart.data.datasets[0].data = data.data || [];
            
            // Update summary cards with real data
            if (data.summary) {
                updateSummaryCards(data.summary);
                updateProgressBar(data.summary.today || 0);
            }
        } else {
            console.error('Failed to load work time data:', data.message);
            // Show demo data as fallback
            loadDemoData(period);
            updateSummaryCards(getDemoSummary());
            updateProgressBar(0);
        }
        
        workTimeChart.update('active');
    })
    .catch(error => {
        console.error('Error loading work time data:', error);
        // Load demo data on error
        loadDemoData(period);
        updateSummaryCards(getDemoSummary(), false);
        updateProgressBar(0);
    })
    .finally(() => {
        // Remove loading state
        chartContainer.style.opacity = '1';
    });
}

function loadDemoData(period) {
    let labels, data;
    
    switch(period) {
        case 'day':
            labels = ['00:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'];
            data = [0,0,0,0,0,0,0,0,0,1,2,2.5,1,2.5,2,1.5,1,0,0,0,0,0,0,0];
            break;
        case 'week':
            labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            data = [8.5, 7.2, 8.0, 6.8, 7.5, 0, 0];
            break;
        case 'month':
            labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
            data = [35.2, 38.5, 32.8, 28.3];
            break;
        case 'year':
            labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            data = [160, 152, 168, 155, 172, 165, 158, 170, 162, 145, 0, 0];
            break;
        default:
            labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            data = [8.5, 7.2, 8.0, 6.8, 7.5, 0, 0];
    }
    
    workTimeChart.data.labels = labels;
    workTimeChart.data.datasets[0].data = data;
}

function getDemoSummary() {
    return {
        today_hours: 6.5,
        weekly_hours: 32.5,
        monthly_hours: 142.8,
        tasks_done: 8
    };
}

function updateSummaryCards(summary, isRealData = true) {
    // Handle both new and legacy data format
    const todayHours = summary.today || summary.today_hours || 0;
    const weeklyHours = summary.weekly || summary.weekly_hours || 0;
    const monthlyHours = summary.monthly || summary.monthly_hours || 0;
    const tasksDone = summary.tasks || summary.tasks_done || 0;
    
    // Update Today's Hours
    document.getElementById('todayHours').textContent = todayHours.toFixed(1) + 'h';
    
    // Update Weekly Hours
    document.getElementById('weeklyHours').textContent = weeklyHours.toFixed(1) + 'h';
    
    // Update Monthly Hours
    document.getElementById('monthlyHours').textContent = monthlyHours.toFixed(1) + 'h';
    
    // Update Tasks Done
    document.getElementById('tasksDone').textContent = tasksDone;
}

function updateProgressBar(todayHours) {
    const targetHours = 8;
    const percentage = Math.min((todayHours / targetHours) * 100, 100);
    
    document.getElementById('progressPercentage').textContent = Math.round(percentage) + '%';
    document.getElementById('progressBar').style.width = percentage + '%';
    document.getElementById('progressBar').setAttribute('aria-valuenow', percentage);
    document.getElementById('currentHours').textContent = todayHours.toFixed(1) + ' hours';
    document.getElementById('targetHours').textContent = targetHours + ' hours';
    
    // Update progress bar color based on progress
    const progressBar = document.getElementById('progressBar');
    if (percentage >= 100) {
        progressBar.style.background = 'linear-gradient(90deg, #10b981, #059669)';
    } else if (percentage >= 75) {
        progressBar.style.background = 'linear-gradient(90deg, #4e54c8, #8f94fb)';
    } else if (percentage >= 50) {
        progressBar.style.background = 'linear-gradient(90deg, #f59e0b, #d97706)';
    } else {
        progressBar.style.background = 'linear-gradient(90deg, #ef4444, #dc2626)';
    }
}

// Show demo data notification
function showDemoDataNotification() {
    // Don't show multiple notifications
    if (document.getElementById('demo-notification')) {
        return;
    }
    
    const notification = document.createElement('div');
    notification.id = 'demo-notification';
    notification.className = 'alert alert-info alert-dismissible fade show position-fixed';
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 350px;';
    notification.innerHTML = `
        <div class="d-flex align-items-start">
            <i class="bi bi-info-circle me-2 mt-1"></i>
            <div>
                <strong>Demo Data Shown</strong><br>
                <small>No attendance records found. Use the Clock In/Out buttons in the sidebar to start tracking your time, or visit the Attendance page to record your work hours.</small>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 10 seconds
    setTimeout(() => {
        if (notification && notification.parentNode) {
            notification.remove();
        }
    }, 10000);
}

// Auto-refresh data every 5 minutes
setInterval(() => {
    loadWorkTimeData(currentPeriod);
}, 300000);
</script>
@endpush
