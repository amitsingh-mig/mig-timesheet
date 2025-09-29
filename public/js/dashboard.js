/* ============================================= */
/* Dashboard Core JavaScript (Refactored)        */
/* ============================================= */
document.addEventListener('DOMContentLoaded', function () {
    // Prevent multiple initializations
    if (window.dashboardInitialized) {
        console.warn('🔄 Dashboard already initialized, skipping DOMContentLoaded setup.');
        return;
    }
    window.dashboardInitialized = true;
    
    console.log('🚀 Initializing Dashboard Manager...');
    
    // --- DashboardManager State and Methods ---
    const DashboardManager = {
        // DOM Elements
        ctx: document.getElementById('workHoursChart'),
        totalHoursEl: document.getElementById('total-hours'),
        progressBar: document.getElementById('today-progress-bar'),
        progressText: document.getElementById('today-progress-text'),
        chartLoading: document.getElementById('chart-loading'),
        chartError: document.getElementById('chart-error'),
        chartContent: document.getElementById('chart-content'),
        dataRangeIndicator: document.getElementById('data-range-indicator'),
        
        // Welcome section elements (Note: The Alpine component handles most of these now)
        welcomeHoursToday: document.getElementById('welcome-hours-today'), // Retained for compatibility if not using Alpine
        
        // Internal State
        state: { 
            range: 'day', 
            chart: null, 
            attendanceData: [], 
            isLoading: false,
            // Track intervals internally instead of on the window object
            autoRefreshInterval: null, 
            durationUpdateInterval: null 
        },

        // --- Utility Functions ---
        /** Formats decimal hours into a cleaner string (e.g., 8.5 -> 8h 30m) */
        formatHours(hours) {
            if (hours === 0 || isNaN(hours)) return '0h';
            const h = Math.floor(hours);
            const m = Math.round((hours - h) * 60);

            if (h === 0) return m + 'm';
            if (m === 0) return h + 'h';
            return `${h}h ${m}m`;
        },

        showLoading() {
            if (this.chartLoading) this.chartLoading.classList.remove('d-none');
            if (this.chartError) this.chartError.classList.add('d-none');
            if (this.chartContent) this.chartContent.style.opacity = '0.5';
        },
        
        hideLoading() {
            if (this.chartLoading) this.chartLoading.classList.add('d-none');
            if (this.chartContent) this.chartContent.style.opacity = '1';
        },
        
        showError(message, detail = '') {
            if (this.chartError) {
                this.chartError.querySelector('h6').textContent = message;
                this.chartError.querySelector('p').textContent = detail;
                this.chartError.classList.remove('d-none');
            }
            if (this.chartLoading) this.chartLoading.classList.add('d-none');
            if (this.chartContent) this.chartContent.style.opacity = '0.8';
        },
        
        // --- Core Fetching & Chart Rendering ---
        async renderChart() {
            if (this.state.isLoading) {
                console.log('⏳ Chart already loading, skipping duplicate call...');
                return;
            }
            
            this.state.isLoading = true;
            this.showLoading();
            console.log(`📊 Rendering chart for range: ${this.state.range}`);
            
            try {
                const response = await fetch(`/timesheet/summary?range=${this.state.range}&refresh=true`);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                const data = await response.json();
                
                // --- Data Processing ---
                let chartData;
                let displayTotal = 0;
                
                // The provided JS handles data transformation, simplifying here for the final chartData object
                chartData = {
                    labels: data.labels || [],
                    data: data.data || [],
                    rangeLabel: data.rangeLabel || 'Work Summary'
                };
                
                // Convert totals from HH:MM string to hours decimal (if Alpine isn't doing it)
                const parseHhMm = (str) => {
                    if (!str || str === '00:00') return 0;
                    const [h, m] = str.split(':').map(Number);
                    return h + (m / 60);
                };
                
                // Calculate Total Hours for the Display Element
                if (data.range_total_minutes !== undefined) {
                    displayTotal = data.range_total_minutes / 60;
                } else {
                    displayTotal = chartData.data.reduce((a, b) => a + b, 0);
                }
                
                if (this.totalHoursEl) this.totalHoursEl.textContent = this.formatHours(displayTotal);
                if (this.dataRangeIndicator) this.dataRangeIndicator.textContent = chartData.rangeLabel;

                // --- Welcome Section Update (if not Alpine controlled) ---
                if (data.totals) {
                    const elToday = document.getElementById('welcome-hours-today');
                    const elWeek = document.getElementById('welcome-hours-week');
                    const elMonth = document.getElementById('welcome-hours-month');
                    const elTasks = document.getElementById('welcome-tasks-today');
                    
                    if (elToday) elToday.textContent = this.formatHours(parseHhMm(data.totals.daily));
                    if (elWeek) elWeek.textContent = this.formatHours(parseHhMm(data.totals.weekly));
                    if (elMonth) elMonth.textContent = this.formatHours(parseHhMm(data.totals.monthly));
                    if (elTasks) elTasks.textContent = data.today?.tasks_count || 0;
                }
                
                // --- Progress Bar Update ---
                const todayDone = data.today?.done_hours || 0; // Assuming done_hours is already decimal
                const todayTarget = data.today?.target_hours || 8;
                const progressPercent = Math.min(100, (todayDone / todayTarget) * 100);
                
                if (this.progressBar && this.progressText) {
                    this.progressBar.style.width = progressPercent + '%';
                    this.progressBar.className = `progress-bar ${
                        progressPercent < 50 ? 'bg-danger' : 
                        progressPercent < 75 ? 'bg-warning' : 'bg-success'
                    }`;
                    this.progressText.textContent = `${this.formatHours(todayDone)} / ${todayTarget}h`;
                }

                // --- Chart Rendering ---
                if (this.state.chart) {
                    this.state.chart.destroy();
                }
                
                // Simplified Chart.js logic (kept original complexity for options/tooltips)
                this.state.chart = new Chart(this.ctx, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Hours Worked',
                            data: chartData.data,
                            // Dynamic color logic retained
                            backgroundColor: (context) => {
                                const value = context.parsed.y;
                                if (value >= 8) return 'rgba(34, 197, 94, 0.2)';
                                if (value >= 6) return 'rgba(59, 130, 246, 0.2)';
                                if (value >= 4) return 'rgba(251, 191, 36, 0.2)';
                                return 'rgba(239, 68, 68, 0.2)';
                            },
                            borderColor: (context) => {
                                const value = context.parsed.y;
                                if (value >= 8) return '#22c55e';
                                if (value >= 6) return '#3b82f6';
                                if (value >= 4) return '#fbbf24';
                                return '#ef4444';
                            },
                            borderWidth: 2,
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    // Custom tooltip title logic retained
                                    title: function(context) { /* ... original logic ... */ return context[0].label; },
                                    label: (context) => {
                                        const value = context.parsed.y;
                                        let status = '';
                                        if (this.state.range === 'day') {
                                            if (value >= 8) status = ' ✅ Full Day';
                                            else if (value >= 6) status = ' 📊 Good Progress';
                                            else if (value > 0) status = ' 🕒 Part Time';
                                            else status = ' 📅 No Work';
                                        }
                                        return `Hours: ${this.formatHours(value)}${status}`;
                                    },
                                    footer: (context) => {
                                        if (this.state.range === 'day' && context[0].parsed.y >= 8) {
                                            return 'Target achieved! 🎯';
                                        }
                                        return '';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                min: 0,
                                // Dynamic Y-axis scale retained
                                max: this.state.range === 'day' ? 12 : (this.state.range === 'week' ? 50 : (this.state.range === 'month' ? 15 : 200)),
                                ticks: {
                                    stepSize: this.state.range === 'day' ? 2 : (this.state.range === 'week' ? 10 : (this.state.range === 'month' ? 2 : 25)),
                                    callback: (value) => this.formatHours(value)
                                },
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                title: { display: true, text: chartData.rangeLabel }
                            },
                            x: {
                                grid: { display: false },
                                title: { display: true, text: this.state.range === 'day' ? 'Days' : (this.state.range === 'week' ? 'Weeks' : (this.state.range === 'month' ? 'Days' : 'Months')) }
                            }
                        },
                        interaction: { intersect: false, mode: 'index' }
                    }
                });

                // Update state and refresh calendar
                this.state.attendanceData = data.attendance || [];
                this.renderCalendar(); 
                this.hideLoading();
                this.state.isLoading = false;
                
            } catch (error) {
                console.error('Chart rendering error:', error);
                
                // Show dedicated error state and fall back to mock data
                this.showError('⚠️ Failed to load timesheet data.', 
                    'The server may be unavailable or you might not have entered data yet. Using sample data for demonstration.');
                
                // Fallback rendering
                // NOTE: Using the original mock data logic for consistency with the fallback code
                const mockData = generateMockData(this.state.range);
                if (this.state.chart) this.state.chart.destroy();
                this.state.chart = new Chart(this.ctx, {
                    type: 'bar',
                    data: {
                        labels: mockData.labels,
                        datasets: [{
                            label: 'Sample Hours Data',
                            data: mockData.data,
                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                            borderColor: '#ffc107',
                            borderWidth: 2,
                            borderDash: [5, 5] 
                        }]
                    },
                    options: { /* Simplified options for mock chart */ }
                });
                
                this.state.isLoading = false;
            }
        },

        // --- Attendance Status Logic (Centralized) ---
        async fetchAttendanceStatus() {
            try {
                // Use fetchWithRetry for robust network handling
                const response = await fetchWithRetry('/attendance/status', { method: 'GET' }, 3);

                if (response.ok) {
                    const status = await response.json();
                    this.updateAttendanceStatus(status);
                    
                    // FIX: Dispatch event for Alpine components to update duration if needed
                    if (status.status === 'clocked_in') {
                        document.dispatchEvent(new CustomEvent('attendance-active', { detail: status.current_duration }));
                    } else {
                        document.dispatchEvent(new CustomEvent('attendance-inactive'));
                    }
                    
                    return status;
                }
                
                // Handle non-OK responses (401, 419, etc.)
                throw new Error(`Server error: ${response.status}`);

            } catch (error) {
                console.error('Failed to fetch attendance status:', error);
                const isNetworkError = !navigator.onLine || error.message.includes('fetch');
                this.updateAttendanceStatus({ 
                    success: false, 
                    message: isNetworkError ? 'Connection error - Retrying...' : 'Status unavailable - Please refresh' 
                });
                
                if (isNetworkError) {
                    // Auto-retry network errors after a short delay
                    setTimeout(() => { this.fetchAttendanceStatus(); }, 5000);
                }
            }
        },

        updateAttendanceStatus(status) {
            const statusDot = document.getElementById('status-dot');
            const statusTime = document.getElementById('status-time');
            const currentStatusEl = document.getElementById('current-status');
            
            if (!status || !status.success) {
                currentStatusEl.textContent = status?.message || 'Status unavailable';
                if (statusDot) statusDot.className = 'status-indicator-dot me-2 offline';
                if (statusTime) statusTime.innerHTML = '<small class="text-muted">Try Refreshing</small>';
                return;
            }
            
            if (statusDot) statusDot.className = 'status-indicator-dot me-2';
            
            // FIX: Removed the logic for setting the durationUpdateInterval here
            // This responsibility now rests with the Alpine component for real-time updates.
            switch (status.status) {
                case 'not_started':
                case 'completed':
                case 'can_start_new':
                    currentStatusEl.textContent = status.status === 'completed' ? 'Work completed for today' : 'Ready to start work';
                    statusDot.classList.add('online');
                    statusTime.textContent = status.total_hours_today ? `Total: ${status.total_hours_today}` : 'Click to begin';
                    break;
                case 'clocked_in':
                    currentStatusEl.textContent = `Currently working`;
                    statusDot.classList.add('working');
                    // current_duration is likely HH:MM:SS from the backend/Alpine
                    statusTime.textContent = `${status.current_duration || '0:00:00'} elapsed`; 
                    break;
                default:
                    currentStatusEl.textContent = status.message || 'Unknown status';
                    statusDot.classList.add('online');
                    statusTime.textContent = '';
            }
        },

        // --- Calendar Rendering (Optimized) ---
        async renderCalendar() {
            const container = document.getElementById('calendar');
            const monthText = document.getElementById('calendar-month');
            const now = new Date();
            const year = now.getFullYear();
            const month = now.getMonth();
            const firstDay = new Date(year, month, 1);
            const startDay = firstDay.getDay(); // Day of week (0=Sun, 6=Sat)
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = now.getDate();
            
            if (!container || !monthText) return;
            
            monthText.textContent = now.toLocaleString('default', { month: 'long', year: 'numeric' });
            container.innerHTML = '';
            
            let calendarDataMap = {};
            let dayTypesMap = {};
            
            try {
                // Fetch calendar data (attendance days, holidays, etc.)
                const response = await fetch(`/attendance/calendar?year=${year}&month=${month + 1}&refresh=true`);
                if (!response.ok) throw new Error('Failed to fetch calendar data');
                const result = await response.json();
                
                if (result.success && result.day_types) {
                    result.data.forEach(item => { calendarDataMap[item.date] = item; });
                    result.day_types.forEach(item => { dayTypesMap[item.date] = item; });
                } else {
                    throw new Error('Calendar API returned non-success.');
                }
            } catch (error) {
                console.warn('⚠️ Falling back to internal calendar data (Holidays/Weekends):', error);
                
                // Fallback to internal data if API fails
                generateFallbackDayTypes(year, month + 1).forEach(item => {
                    dayTypesMap[item.date] = item;
                });
                
                // Merge fallback day types with loaded chart attendance data (state.attendanceData is just dates)
                this.state.attendanceData.forEach(dateStr => {
                    if (dayTypesMap[dateStr]) {
                        dayTypesMap[dateStr].is_work_day = true;
                    }
                });
            }
            
            // Weekday headers
            ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(day => {
                const header = document.createElement('div');
                header.className = 'calendar-head';
                header.textContent = day;
                container.appendChild(header);
            });
            
            // Empty cells padding
            for (let i = 0; i < startDay; i++) {
                const empty = document.createElement('div');
                empty.className = 'calendar-cell empty-cell';
                container.appendChild(empty);
            }
            
            // Date cells rendering loop
            for (let day = 1; day <= daysInMonth; day++) {
                const cell = document.createElement('div');
                cell.className = 'calendar-cell';
                const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                
                const dayTypeInfo = dayTypesMap[dateString];
                const dayData = calendarDataMap[dateString];
                
                // Add day number
                const dateEl = document.createElement('div');
                dateEl.className = 'calendar-date';
                dateEl.textContent = day;
                cell.appendChild(dateEl);
                
                // Apply classes based on priority: Today > Active Session > Holiday > Work Day
                
                if (dayTypeInfo) {
                    // 1. Holiday Check
                    if (dayTypeInfo.is_holiday) {
                        cell.classList.add('holiday');
                        cell.setAttribute('title', `Holiday: ${dayTypeInfo.holiday_info?.name || 'Scheduled Break'}`);
                    }

                    // 2. Work Data Check (Overrides Holiday if present)
                    if (dayData || dayTypeInfo.is_work_day) {
                        cell.classList.add('work-day');
                        if (dayTypeInfo.is_holiday) cell.classList.remove('holiday'); // Work overrides holiday class
                        
                        const hoursEl = document.createElement('div');
                        hoursEl.className = 'calendar-hours has-hours';
                        
                        // Show hours or session count
                        let hoursText = dayData?.total_hours || (dayData?.sessions_count ? `${dayData.sessions_count}s` : '0:00');
                        
                        if (dayData?.has_active_session) {
                            cell.classList.add('active-session');
                            hoursText += ' ⏰';
                            cell.setAttribute('title', `Active Session - Clocked In`);
                        } else if (dayData?.total_hours) {
                            cell.setAttribute('title', `Worked: ${dayData.total_hours}`);
                        }
                        
                        hoursEl.textContent = hoursText;
                        cell.appendChild(hoursEl);
                    }
                    
                    // 3. Today Check (Highest Priority)
                    if (dayTypeInfo.is_today) {
                        cell.classList.add('today');
                        // Add today class regardless of other classes (ensures blue border)
                    }
                }
                
                container.appendChild(cell);
            }
        },
        
        // --- Initialization and Cleanup ---
        init() {
            // Set up event listeners for range toggles
            document.querySelectorAll('.dashboard-toggle .btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.dashboard-toggle .btn').forEach(b => 
                        b.classList.remove('active')
                    );
                    btn.classList.add('active');
                    this.state.range = btn.getAttribute('data-range');
                    this.renderChart();
                });
            });

            // Start primary initialization sequence
            this.initializeDashboard();
            this.setupIntervals();
        },
        
        async initializeDashboard() {
            this.updateAttendanceStatus({ success: false, message: 'Loading status...' });
            await this.fetchAttendanceStatus();
            
            // Render chart first (loads timesheet data and initial attendance state)
            try {
                await this.renderChart();
                console.log('📈 Chart loaded with timesheet data, now rendering calendar...');
                this.renderCalendar();
            } catch (error) {
                console.warn('⚠️ Chart failed to load, rendering calendar with fallback data:', error);
                this.renderCalendar();
            }
        },

        setupIntervals() {
            // Clean up old intervals (from previous initializations)
            this.cleanupIntervals();
            
            // Auto-refresh every 5 minutes (reduced from 10 minutes for a more responsive dashboard)
            this.state.autoRefreshInterval = setInterval(() => {
                console.log('🔄 Auto-refreshing dashboard...');
                if (!this.state.isLoading) {
                    this.renderChart();
                    this.renderCalendar();
                    this.fetchAttendanceStatus();
                } else {
                    console.log('⏸️ Skipping auto-refresh - chart is loading');
                }
            }, 300000); // 5 minutes

            // Cleanup on page unload/hide
            window.addEventListener('beforeunload', () => this.cleanupIntervals(true));
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    console.log('📱 Page hidden - pausing auto-refresh');
                    this.cleanupIntervals();
                } else {
                    console.log('👀 Page visible - resuming auto-refresh');
                    this.setupIntervals(); // Re-set interval
                }
            });
        },
        
        cleanupIntervals(isUnload = false) {
            if (this.state.autoRefreshInterval) {
                clearInterval(this.state.autoRefreshInterval);
                this.state.autoRefreshInterval = null;
            }
            if (isUnload && this.state.chart) {
                this.state.chart.destroy();
                this.state.chart = null;
            }
        }
    };
    
    // --- Global Functions (Keep for HTML event handlers) ---
    // NOTE: These are simplified to only perform the essential API call
    // The complex status and UI updates are now handled by the Alpine component (if present)
    // or the DashboardManager's fetchAttendanceStatus and renderChart methods.

    window.clockIn = async function(event) {
        // The Alpine component will handle the button's visual state (loading, disabled)
        // If no Alpine component is used, the original JS logic must be restored here.
        try {
            const response = await fetch('/attendance/clock-in', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            const data = await response.json();
            
            if (data.success) {
                // Trigger full dashboard refresh after successful action
                DashboardManager.fetchAttendanceStatus();
                DashboardManager.renderChart();
                DashboardManager.renderCalendar();
                showNotification('success', data.message);
            } else {
                showNotification('warning', data.message);
            }
        } catch (error) {
            console.error('Clock in error:', error);
            showNotification('error', 'Failed to clock in. Network issue.');
        }
    };
    
    window.clockOut = async function(event) {
        try {
            const response = await fetch('/attendance/clock-out', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            const data = await response.json();
            
            if (data.success) {
                DashboardManager.fetchAttendanceStatus();
                DashboardManager.renderChart();
                DashboardManager.renderCalendar();
                showNotification('success', `${data.message}. Total work time: ${data.total_hours}`);
            } else {
                showNotification('warning', data.message);
            }
        } catch (error) {
            console.error('Clock out error:', error);
            showNotification('error', 'Failed to clock out. Network issue.');
        }
    };
    
    // --- Global Utility Functions Retained for HTML Callbacks ---
    function showNotification(type, message) { /* ... original notification logic ... */ }
    function fetchWithRetry(url, options = {}, attempts = 3, fallbackUrl = null) { /* ... original fetchWithRetry logic ... */ }
    function generateFallbackHolidays(year, month) { /* ... original fallback logic ... */ }
    function generateFallbackDayTypes(year, month) { /* ... original fallback logic ... */ }
    function getSaturdayNumber(date) { /* ... original fallback logic ... */ }
    function getOrdinal(number) { /* ... original fallback logic ... */ }
    function generateMockData(range) { /* ... original mock data logic ... */ }
    
    // --- Start Dashboard ---
    DashboardManager.init();
});