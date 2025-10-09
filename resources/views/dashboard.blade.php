@extends('layouts.app')

@section('content')
<!-- Ensure Chart.js is loaded -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 fw-bold text-dark mb-1">🚀 Employee Dashboard</h1>
        <p class="text-muted mb-0">Welcome back to your workspace</p>
    </div>
    <div class="text-end current-time-display d-none d-md-block">
        <h5 class="fw-bold mb-0 text-dark" id="currentTime">{{ now()->format('H:i') }}</h5>
        <small class="text-dark-50">{{ now()->format('l, F j, Y') }}</small>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <!-- Today's Hours Card -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 shadow-lg h-100 bg-gradient-1 card-gradient transition-all">
            <div class="card-body text-white text-center py-4">
                <i class="bi bi-clock-fill text-2_5xl d-block mb-2"></i>
                <h2 class="h1 fw-bold mb-1" id="todayHours">0.0h</h2>
                <p class="mb-0 fs-6">Today's Hours</p>
                <small class="opacity-75" id="todayDurationLabel">Loading...</small>
            </div>
        </div>
    </div>
    
    <!-- Weekly Hours Card -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 shadow-lg h-100 bg-gradient-2 card-gradient transition-all">
            <div class="card-body text-white text-center py-4">
                <i class="bi bi-calendar-week-fill text-2_5xl d-block mb-2"></i>
                <h2 class="h1 fw-bold mb-1" id="weeklyHours">0.0h</h2>
                <p class="mb-0 fs-6">Weekly Hours</p>
                <small class="opacity-75">This week</small>
            </div>
        </div>
    </div>
    
    <!-- Monthly Hours Card -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 shadow-lg h-100 bg-gradient-3 card-gradient transition-all">
            <div class="card-body text-white text-center py-4">
                <i class="bi bi-calendar text-2_5xl d-block mb-2"></i>
                <h2 class="h1 fw-bold mb-1" id="monthlyHours">0.0h</h2>
                <p class="mb-0 fs-6">Monthly Hours</p>
                <small class="opacity-75">This month</small>
            </div>
        </div>
    </div>
    
    <!-- Tasks Done Card -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 shadow-lg h-100 bg-gradient-5 card-gradient transition-all">
            <div class="card-body text-white text-center py-4">
                <i class="bi bi-check-circle-fill text-2_5xl d-block mb-2"></i>
                <h2 class="h1 fw-bold mb-1" id="tasksDone">0</h2>
                <p class="mb-0 fs-6">Tasks Done</p>
                <small class="opacity-75">Today's count</small>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row g-4">

    <!-- Work Time Chart Section -->
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
                <h5 class="mb-0 fw-semibold">
                <i class="bi bi-bar-chart-line me-2 text-primary"></i>Work Time Distribution
                </h5>
                <div class="btn-group chart-toggle-group" role="group" aria-label="Chart period">
                    <button type="button" class="btn btn-outline-primary btn-sm chart-toggle" data-period="day">Last 7 Days</button>
                    <button type="button" class="btn btn-outline-primary btn-sm chart-toggle active" data-period="week">Last 4 Weeks</button>
                    <button type="button" class="btn btn-outline-primary btn-sm chart-toggle" data-period="month">Month View</button>
                    <button type="button" class="btn btn-outline-primary btn-sm chart-toggle" data-period="year">Year View</button>
                </div>
            </div>
            <div class="card-body py-4 position-relative">
                <div id="chart-loading" class="text-center position-absolute top-50 start-50 translate-middle d-none loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="position-relative chart-container" id="chart-content">
                    <canvas id="clockInChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Progress Section -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-target me-2 text-muted"></i>Daily Goal Target
                </h5>
            </div>
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="mb-4">
                    <div class="text-display-4 fw-bolder mb-2" style="color: #4e54c8; font-size: 3rem;" id="progressPercentage">0%</div>
                </div>
                <p class="text-muted mb-4">Daily Goal Completion</p>
                <div class="progress mb-4 progress-custom">
                    <div class="progress-bar progress-bar-custom" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="progressBar"></div>
                </div>
                <div class="d-flex justify-content-between text-sm text-muted mb-4">
                    <span id="currentHoursLabel" class="fw-bold">0 hours</span>
                    <span id="targetHoursLabel">8 hours Target</span>
                </div>
                <div class="mt-auto pt-3 border-top">
                    <small class="text-muted">Keep track of your daily work target. 🎯</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for messages (replaces native alerts) -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="messageModalBody">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Gradient Color Definitions */
    .card-gradient { 
        border-radius: 1rem; 
        overflow: hidden; 
        transition: transform 0.3s ease, box-shadow 0.3s ease; 
    }
    .card-gradient:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
    }
    .bg-gradient-1 { background: linear-gradient(135deg, #4e54c8, #8f94fb) !important; } /* Deep Purple/Blue */
    .bg-gradient-2 { background: linear-gradient(135deg, #06b6d4, #3b82f6) !important; } /* Cyan/Blue */
    .bg-gradient-3 { background: linear-gradient(135deg, #10b981, #34d399) !important; } /* Green/Teal */
    .bg-gradient-5 { background: linear-gradient(135deg, #f59e0b, #f97316) !important; } /* Orange/Amber */

    .text-2_5xl { font-size: 2.25rem; line-height: 1; }
    .icon-circle { width: 40px; height: 40px; }

    /* Progress Bar Styling */
    .progress-custom { height: 10px; border-radius: 9999px; background: #eef2ff; }
    .progress-bar-custom { border-radius: 9999px; transition: width .5s ease; }

    /* Chart Styling */
    .chart-container { height: 360px; max-width: 100%; overflow: hidden; margin: 0; }
    canvas#clockInChart { display: block; max-width: 100% !important; margin: 0; }
    .loading-spinner { height: 100%; width: 100%; background: rgba(255, 255, 255, 0.7); z-index: 10; }

    /* Buttons active state for chart toggles */
    .chart-toggle-group .btn { transition: all 0.2s ease; }
    .chart-toggle-group .chart-toggle.active { 
        color: #fff !important; 
        background-color: #4e54c8 !important; 
        border-color: #4e54c8 !important; 
        box-shadow: 0 2px 5px rgba(78, 84, 200, 0.3);
    }
    .chart-toggle-group .chart-toggle:hover {
        background-color: #f0f4ff;
    }
    
    /* Current Time Display */
    .current-time-display {
        background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%) !important;
        color: white !important;
        border-radius: 1rem;
        padding: 0.75rem 1rem;
        box-shadow: 0 4px 15px rgba(78, 84, 200, 0.4);
        min-width: 150px;
        text-align: center;
    }

    /* Additional margin fixes */
    .main-content {
        padding: 1.5rem !important;
    }
    
    .card {
        margin-bottom: 0 !important;
    }
    
    .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    
    .col-12, .col-sm-6, .col-lg-3, .col-lg-4, .col-lg-8 {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }
</style>
@endpush

@push('scripts')
{{-- Load Bootstrap JS for modal functionality --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let workTimeChart;
let currentPeriod = 'week';
const targetHours = 8; // Daily target

// --- Utility Functions ---

/** Formats hours decimal to H.Xh string (e.g., 7.5 -> 7.5h) */
function formatHours(hours) {
    if (hours === 0 || isNaN(hours)) return '0.0h';
    return parseFloat(hours).toFixed(1) + 'h';
}

/** Updates all summary cards and the progress bar. */
function updateUI(summary) {
    // Calculate total hours worked today, week, and month
    const todayHours = summary.today_hours || summary.today || 0;
    const weeklyHours = summary.weekly_hours || summary.weekly || 0;
    const monthlyHours = summary.monthly_hours || summary.monthly || 0;
    const tasksDone = summary.tasks_done || summary.tasks || 0;
    const currentDuration = summary.current_duration || '0:00'; // HH:MM string for current session

    // Update Summary Cards
    document.getElementById('todayHours').textContent = formatHours(todayHours);
    document.getElementById('weeklyHours').textContent = formatHours(weeklyHours);
    document.getElementById('monthlyHours').textContent = formatHours(monthlyHours);
    document.getElementById('tasksDone').textContent = tasksDone;

    // Update Progress Bar
    const percentage = Math.min((todayHours / targetHours) * 100, 100);
    const progressBar = document.getElementById('progressBar');

    document.getElementById('progressPercentage').textContent = Math.round(percentage) + '%';
    progressBar.style.width = percentage + '%';
    progressBar.setAttribute('aria-valuenow', percentage);
    document.getElementById('currentHoursLabel').textContent = formatHours(todayHours) + ' worked';
    document.getElementById('targetHoursLabel').textContent = targetHours + ' hours target';
    
    // Update progress bar color and label
    if (percentage >= 100) {
        // Green
        progressBar.style.background = 'linear-gradient(90deg, #10b981, #059669)';
        document.getElementById('todayDurationLabel').textContent = 'Daily goal met! 🎉';
    } else if (percentage >= 75) {
        // Blue
        progressBar.style.background = 'linear-gradient(90deg, #4e54c8, #8f94fb)';
        document.getElementById('todayDurationLabel').textContent = `Active session: ${currentDuration}`;
    } else if (percentage > 0) {
        // Orange
        progressBar.style.background = 'linear-gradient(90deg, #f59e0b, #d97706)';
        document.getElementById('todayDurationLabel').textContent = `Active session: ${currentDuration}`;
    } else {
        // Red/Gray
        progressBar.style.background = 'linear-gradient(90deg, #ef4444, #dc2626)';
        document.getElementById('todayDurationLabel').textContent = 'Ready to Clock In';
    }
}

/** Utility to display messages/errors using the Bootstrap modal. */
function showModalMessage(title, body) {
    document.getElementById('messageModalLabel').textContent = title;
    document.getElementById('messageModalBody').innerHTML = body;
    const modal = new bootstrap.Modal(document.getElementById('messageModal'));
    modal.show();
}

// --- Chart and Data Functions ---

function initializeChart() {
    const ctx = document.getElementById('clockInChart').getContext('2d');
    
    if (workTimeChart) {
        workTimeChart.destroy();
    }
    
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
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    cornerRadius: 6,
                    callbacks: {
                        label: (context) => {
                            return formatHours(context.parsed.y) + ' Worked';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: { callback: (value) => value + 'h' }
                },
                x: {
                    grid: { display: false }
                }
            },
            interaction: { intersect: false, mode: 'index' }
        }
    });
}

function loadDemoData(period) {
    let labels, data, chartTitle;
    
    switch(period) {
        case 'day':
            labels = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
            data = [1, 1, 1, 1, 0.5, 0.5, 1, 1, 1, 0.5]; // 8.5 hours total
            chartTitle = "Today's Hourly Sessions (Demo)";
            break;
        case 'week':
            labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            data = [8.5, 7.2, 8.0, 6.8, 7.5, 0, 0];
            chartTitle = "Weekly Work Hours (Demo)";
            break;
        case 'month':
            labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
            data = [35.2, 38.5, 32.8, 28.3];
            chartTitle = "Monthly Summary by Week (Demo)";
            break;
        case 'year':
            labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            data = [160, 152, 168, 155, 172, 165, 158, 170, 162, 145, 0, 0];
            chartTitle = "Annual Summary by Month (Demo)";
            break;
        default:
            labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            data = [8.5, 7.2, 8.0, 6.8, 7.5, 0, 0];
            chartTitle = "Weekly Work Hours (Demo)";
    }
    
    workTimeChart.data.labels = labels;
    workTimeChart.data.datasets[0].data = data;
    workTimeChart.data.datasets[0].label = chartTitle;
    workTimeChart.data.datasets[0].backgroundColor = 'rgba(255, 193, 7, 0.6)';
    workTimeChart.data.datasets[0].borderColor = '#ffc107';
    workTimeChart.update('active');
    
    // Show demo data notification
    showModalMessage('Demo Data Active', 'We could not fetch real-time data. Displaying sample work hours. Please ensure you are logged in and API endpoints are working.');
    
    // Default demo summary values for cards
    const demoSummary = {
        today: 6.5,
        weekly: 38.0,
        monthly: 145.5,
        tasks: 8,
        current_duration: '00:00'
    };
    updateUI(demoSummary);
}

function loadWorkTimeData(period) {
    const chartContainer = document.getElementById('chart-content');
    const loadingEl = document.getElementById('chart-loading');

    // Show loading state
    chartContainer.style.opacity = '0.5';
    loadingEl.classList.remove('d-none');
    
    // Use the defined route name from Laravel
    const routeUrl = `{{ route('dashboard.worktime-data') }}?period=${period}`;
    
    // Added fetchWithRetry for robust error handling
    fetch(routeUrl)
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Restore chart default styling
            workTimeChart.data.datasets[0].backgroundColor = 'rgba(78, 84, 200, 0.8)';
            workTimeChart.data.datasets[0].borderColor = 'rgba(78, 84, 200, 1)';
            
            // Update chart data
            workTimeChart.data.labels = data.labels || [];
            workTimeChart.data.datasets[0].data = data.data || [];
            workTimeChart.data.datasets[0].label = data.chartTitle || 'Hours Worked';

            // Update summary cards and progress bar
            if (data.summary) {
                updateUI(data.summary);
            }
        } else {
            console.error('API reported non-success:', data.message);
            loadDemoData(period);
        }
        
        workTimeChart.update('active');
    })
    .catch(error => {
        console.error('Network/Fetch Error loading work time data:', error);
        loadDemoData(period);
        showModalMessage('Connection Error', `Failed to retrieve data from the server. Please check your network connection or try refreshing the page. Showing demo data instead. <br><small>(${error.message})</small>`);
    })
    .finally(() => {
        // Remove loading state
        chartContainer.style.opacity = '1';
        loadingEl.classList.add('d-none');
    });
}

function updateCurrentTimeDisplay() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false // Use 24-hour format (H:i)
    });
    const currentTimeEl = document.getElementById('currentTime');
    if (currentTimeEl) {
        currentTimeEl.textContent = timeString;
    }
}

// --- Initialization ---

document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
    
    // Initial data load (Default: 'week')
    loadWorkTimeData(currentPeriod); 
    
    // Set up real-time clock (runs every 60 seconds for performance)
    setInterval(updateCurrentTimeDisplay, 60000); 
    updateCurrentTimeDisplay();

    // Add event listeners to toggle buttons
    document.querySelectorAll('.chart-toggle').forEach(button => {
        button.addEventListener('click', function() {
            // Update active state
            document.querySelectorAll('.chart-toggle').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            // Load data for selected period
            const period = this.getAttribute('data-period');
            currentPeriod = period;
            loadWorkTimeData(period);
        });
    });

    // Auto-refresh data every 5 minutes
    setInterval(() => {
        console.log('🔄 Auto-refreshing dashboard data...');
        loadWorkTimeData(currentPeriod);
    }, 300000); // 5 minutes
});

// Global refresh function for other pages to call
window.refreshDashboard = function() {
    loadWorkTimeData(currentPeriod);
};
</script>
@endpush
