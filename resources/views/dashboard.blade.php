@extends('layouts.app')

@section('content')
<!-- Ensure Chart.js is loaded -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<div class="container-fluid px-4">
    <!-- Modern Employee Dashboard Header -->
    <div class="employee-header">
        <div class="employee-header-content">
            <div class="employee-title-section">
                <h1 class="employee-title">Employee Dashboard</h1>
                <p class="employee-subtitle">Welcome back to your workspace</p>
            </div>
            <div class="employee-time-display">
                <div class="current-time" id="currentTime">{{ now()->format('H:i') }}</div>
                <div class="current-date">{{ now()->format('l, F j, Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Modern Summary Cards -->
    <div class="employee-stats-grid">
        <!-- Today's Hours Card -->
        <div class="employee-stat-card employee-stat-primary">
            <div class="employee-stat-icon">
                <i class="bi bi-clock-fill"></i>
            </div>
            <div class="employee-stat-content">
                <h3 class="employee-stat-number" id="todayHours">0.0h</h3>
                <p class="employee-stat-label">Today's Hours</p>
                <small class="employee-stat-subtitle" id="todayDurationLabel">Loading...</small>
            </div>
        </div>
        
        <!-- Weekly Hours Card -->
        <div class="employee-stat-card employee-stat-success">
            <div class="employee-stat-icon">
                <i class="bi bi-calendar-week-fill"></i>
            </div>
            <div class="employee-stat-content">
                <h3 class="employee-stat-number" id="weeklyHours">0.0h</h3>
                <p class="employee-stat-label">Weekly Hours</p>
                <small class="employee-stat-subtitle">This week</small>
            </div>
        </div>
        
        <!-- Monthly Hours Card -->
        <div class="employee-stat-card employee-stat-info">
            <div class="employee-stat-icon">
                <i class="bi bi-calendar"></i>
            </div>
            <div class="employee-stat-content">
                <h3 class="employee-stat-number" id="monthlyHours">0.0h</h3>
                <p class="employee-stat-label">Monthly Hours</p>
                <small class="employee-stat-subtitle">This month</small>
            </div>
        </div>
        
        <!-- Tasks Done Card -->
        <div class="employee-stat-card employee-stat-warning">
            <div class="employee-stat-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="employee-stat-content">
                <h3 class="employee-stat-number" id="tasksDone">0</h3>
                <p class="employee-stat-label">Tasks Done</p>
                <small class="employee-stat-subtitle">Today's count</small>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="employee-content-grid">
        <!-- Work Time Chart Section -->
        <div class="employee-chart-section">
            <div class="employee-card">
                <div class="employee-card-header">
                    <div class="employee-card-title">
                        <i class="bi bi-bar-chart-line"></i>
                        Work Time Distribution
                    </div>
                    <div class="employee-chart-controls">
                        <button type="button" class="employee-chart-btn" data-period="day">Last 7 Days</button>
                        <button type="button" class="employee-chart-btn active" data-period="week">Last 4 Weeks</button>
                        <button type="button" class="employee-chart-btn" data-period="month">Month View</button>
                        <button type="button" class="employee-chart-btn" data-period="year">Year View</button>
                    </div>
                </div>
                <div class="employee-card-body">
                    <div id="chart-loading" class="employee-loading d-none">
                        <div class="employee-spinner"></div>
                        <p>Loading chart...</p>
                    </div>
                    <div class="employee-chart-container" id="chart-content">
                        <canvas id="clockInChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Goal Progress Section -->
        <div class="employee-progress-section">
            <div class="employee-card">
                <div class="employee-card-header">
                    <div class="employee-card-title">
                        <i class="bi bi-target"></i>
                        Daily Goal Target
                    </div>
                </div>
                <div class="employee-card-body">
                    <div class="employee-progress-content">
                        <div class="employee-progress-percentage" id="progressPercentage">0%</div>
                        <p class="employee-progress-label">Daily Goal Completion</p>
                        <div class="employee-progress-bar">
                            <div class="employee-progress-fill" id="progressBar" style="width: 0%;"></div>
                        </div>
                        <div class="employee-progress-details">
                            <span id="currentHoursLabel" class="employee-progress-current">0 hours</span>
                            <span id="targetHoursLabel" class="employee-progress-target">8 hours Target</span>
                        </div>
                        <div class="employee-progress-footer">
                            <small>Keep track of your daily work target. 🎯</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for messages (replaces native alerts) -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content employee-modal-content">
            <div class="modal-header employee-modal-header">
                <h5 class="modal-title employee-modal-title" id="messageModalLabel">Notification</h5>
                <button type="button" class="btn-close employee-btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body employee-modal-body" id="messageModalBody">
                ...
            </div>
            <div class="modal-footer employee-modal-footer">
                <button type="button" class="btn-employee-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection


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
    document.querySelectorAll('.employee-chart-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Update active state
            document.querySelectorAll('.employee-chart-btn').forEach(btn => {
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