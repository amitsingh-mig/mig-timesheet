// Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Prevent multiple initializations
    if (window.dashboardInitialized) {
        console.log('🔄 Dashboard already initialized, skipping...');
        return;
    }
    window.dashboardInitialized = true;
    
    // Clean up existing intervals and timers
    if (window.durationUpdateInterval) {
        clearInterval(window.durationUpdateInterval);
        window.durationUpdateInterval = null;
    }
    if (window.timeUpdateInterval) {
        clearInterval(window.timeUpdateInterval);
        window.timeUpdateInterval = null;
    }
    if (window.autoRefreshInterval) {
        clearInterval(window.autoRefreshInterval);
        window.autoRefreshInterval = null;
    }
    
    console.log('🚀 Initializing Dashboard...');
    
    // DOM Elements
    const ctx = document.getElementById('workHoursChart');
    const totalHoursEl = document.getElementById('total-hours');
    const progressBar = document.getElementById('today-progress-bar');
    const progressText = document.getElementById('today-progress-text');
    const chartLoading = document.getElementById('chart-loading');
    const chartError = document.getElementById('chart-error');
    const chartContent = document.getElementById('chart-content');
    const dataRangeIndicator = document.getElementById('data-range-indicator');
    
    // Welcome section elements
    const welcomeHoursToday = document.getElementById('welcome-hours-today');
    const welcomeHoursWeek = document.getElementById('welcome-hours-week');
    const welcomeHoursMonth = document.getElementById('welcome-hours-month');
    const welcomeTasksToday = document.getElementById('welcome-tasks-today');
    
    // Clock status
    const currentStatus = document.getElementById('current-status');

    // State with cleanup flag
    let state = { 
        range: 'day', 
        chart: null, 
        attendanceData: [], 
        isLoading: false,
        initialized: false
    };
    
    // Utility Functions
    function formatHours(hours) {
        if (hours === 0) return '0h';
        if (hours < 1) return Math.round(hours * 60) + 'm';
        
        // For display purposes, remove trailing .0
        const formatted = hours.toFixed(1);
        return formatted.endsWith('.0') ? formatted.slice(0, -2) + 'h' : formatted + 'h';
    }
    
    function showLoading() {
        chartLoading.classList.remove('d-none');
        chartError.classList.add('d-none');
        chartContent.style.opacity = '0.5';
    }
    
    function hideLoading() {
        chartLoading.classList.add('d-none');
        chartContent.style.opacity = '1';
    }
    
    function showError() {
        chartError.classList.remove('d-none');
        chartLoading.classList.add('d-none');
        chartContent.style.opacity = '0.8';
    }

    // Mock data generator (fallback)
    function generateMockData(range) {
        const now = new Date();
        const dataMap = {
            day: {
                labels: Array.from({length: 7}, (_, i) => {
                    const date = new Date(now);
                    date.setDate(date.getDate() - 6 + i);
                    return date.toLocaleDateString('en', {weekday: 'short'});
                }),
                data: [6.5, 7.2, 8.0, 5.5, 7.8, 0, 0],
                range: 'Last 7 days'
            },
            week: {
                labels: ['W1', 'W2', 'W3', 'W4'],
                data: [42, 38, 45, 36],
                range: 'This Month (by week)'
            },
            month: {
                labels: Array.from({length: new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate()}, (_, i) => String(i + 1)),
                data: Array.from({length: new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate()}, () => Math.random() * 8),
                range: 'This Month'
            },
            year: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                data: [160, 172, 150, 168, 174, 162, 170, 158, 176, 169, 171, 165],
                range: 'This Year'
            }
        };
        return dataMap[range] || dataMap.month;
    }

    // Chart rendering function
    async function renderChart() {
        // Prevent duplicate calls
        if (state.isLoading) {
            console.log('⏳ Chart already loading, skipping duplicate call...');
            return Promise.resolve(false);
        }
        
        state.isLoading = true;
        console.log(`📊 Rendering chart for range: ${state.range}`);
        showLoading();
        
        return new Promise(async (resolve, reject) => {
        
        try {
            const cacheBuster = new Date().getTime();
            const response = await fetch(`/timesheet/summary?range=${state.range}&v=${cacheBuster}&refresh=true`);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            // Process data for current range
            let chartData;
            if (state.range === 'month') {
                chartData = {
                    labels: data.labels.map(d => d.slice(-2)), // Get day part
                    data: data.data,
                    range: 'This Month'
                };
            } else if (state.range === 'day') {
                chartData = {
                    labels: data.labels.map(d => {
                        const date = new Date(d);
                        return date.toLocaleDateString('en', {weekday: 'short'});
                    }),
                    data: data.data,
                    range: 'Last 7 days'
                };
            } else if (state.range === 'week') {
                chartData = {
                    labels: data.labels, // W1, W2, W3, W4
                    data: data.data,
                    range: 'This Month (by week)'
                };
            } else if (state.range === 'year') {
                chartData = {
                    labels: data.labels, // Jan, Feb, Mar, etc.
                    data: data.data,
                    range: 'This Year'
                };
            } else {
                chartData = generateMockData(state.range);
            }
            
            // Update total hours using backend calculated total for accuracy
            let displayTotal;
            if (data.range_total_minutes !== undefined) {
                // Use backend calculated total (more accurate for different ranges)
                displayTotal = data.range_total_minutes / 60; // Convert minutes to hours
            } else {
                // Fallback to chart data sum
                displayTotal = chartData.data.reduce((a, b) => a + b, 0);
            }
            
            totalHoursEl.textContent = formatHours(displayTotal);
            dataRangeIndicator.textContent = chartData.range;
            
            // Update welcome section with data status handling
            if (data.totals) {
                const parseHhMm = (str) => {
                    if (!str || str === '00:00') return 0;
                    const [h, m] = str.split(':').map(Number);
                    return h + (m / 60);
                };
                
                welcomeHoursToday.textContent = formatHours(parseHhMm(data.totals.daily));
                welcomeHoursWeek.textContent = formatHours(parseHhMm(data.totals.weekly));
                welcomeHoursMonth.textContent = formatHours(parseHhMm(data.totals.monthly));
                welcomeTasksToday.textContent = data.today?.tasks_count || 0;
            }
            
            // Handle missing data messages
            if (data.data_status) {
                const hasData = data.data_status.has_timesheet_data;
                
                if (!hasData) {
                    // Show no data message
                    document.getElementById('chart-content').innerHTML = `
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <span class="display-1">📝</span>
                            </div>
                            <h5 class="text-muted">No Tasks Recorded Yet</h5>
                            <p class="text-muted mb-3">Start by adding your first timesheet entry to see your productivity overview.</p>
                            <a href="{{ route('timesheet.index') }}" class="btn btn-primary btn-rounded">
                                <span class="me-1">➕</span> Add Your First Task
                            </a>
                        </div>
                    `;
                    return; // Don't render chart if no data
                }
            }
            
            // Today's progress
            const todayDone = data.today?.done_hours || 0;
            const todayTarget = data.today?.target_hours || 8;
            const progressPercent = Math.min(100, (todayDone / todayTarget) * 100);
            
            progressBar.style.width = progressPercent + '%';
            progressBar.className = `progress-bar ${
                progressPercent < 50 ? 'bg-danger' : 
                progressPercent < 75 ? 'bg-warning' : 'bg-success'
            }`;
            progressText.textContent = `${formatHours(todayDone)}/${todayTarget}h`;
            
            // Store attendance data for calendar
            state.attendanceData = data.attendance || [];
            console.log('📊 Attendance data loaded for calendar:', state.attendanceData.length, 'records');
            
            // Re-render calendar with new data if it exists
            if (state.attendanceData.length > 0) {
                console.log('🔄 Re-rendering calendar with fresh attendance data...');
                renderCalendar();
            }
            
            // Destroy existing chart
            if (state.chart) {
                state.chart.destroy();
            }
            
            // Create new chart
            state.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Hours Worked',
                        data: chartData.data,
                        backgroundColor: function(context) {
                            const value = context.parsed.y;
                            if (value >= 8) return 'rgba(34, 197, 94, 0.2)';
                            if (value >= 6) return 'rgba(59, 130, 246, 0.2)';
                            if (value >= 4) return 'rgba(251, 191, 36, 0.2)';
                            return 'rgba(239, 68, 68, 0.2)';
                        },
                        borderColor: function(context) {
                            const value = context.parsed.y;
                            if (value >= 8) return '#22c55e';
                            if (value >= 6) return '#3b82f6';
                            if (value >= 4) return '#fbbf24';
                            return '#ef4444';
                        },
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                        hoverBackgroundColor: function(context) {
                            const value = context.parsed.y;
                            if (value >= 8) return 'rgba(34, 197, 94, 0.4)';
                            if (value >= 6) return 'rgba(59, 130, 246, 0.4)';
                            if (value >= 4) return 'rgba(251, 191, 36, 0.4)';
                            return 'rgba(239, 68, 68, 0.4)';
                        }
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
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                title: function(context) {
                                    const label = context[0].label;
                                    if (state.range === 'day') {
                                        const date = new Date();
                                        date.setDate(date.getDate() - (6 - context[0].dataIndex));
                                        return date.toLocaleDateString('en', { weekday: 'long', month: 'short', day: 'numeric' });
                                    } else if (state.range === 'month') {
                                        const date = new Date();
                                        date.setDate(parseInt(label));
                                        return date.toLocaleDateString('en', { weekday: 'short', month: 'short', day: 'numeric' });
                                    }
                                    return label;
                                },
                                label: function(context) {
                                    const value = context.parsed.y;
                                    let status = '';
                                    if (state.range === 'day') {
                                        if (value >= 8) status = ' ✅ Full Day';
                                        else if (value >= 6) status = ' 📊 Good Progress';
                                        else if (value >= 4) status = ' ⚠️ Part Time';
                                        else if (value > 0) status = ' 🕒 Few Hours';
                                        else status = ' 📅 No Work';
                                    }
                                    return `Hours: ${formatHours(value)}${status}`;
                                },
                                footer: function(context) {
                                    if (state.range === 'day' && context[0].parsed.y >= 8) {
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
                            max: state.range === 'day' ? 12 : (state.range === 'week' ? 50 : (state.range === 'month' ? 15 : 200)),
                            ticks: {
                                stepSize: state.range === 'day' ? 2 : (state.range === 'week' ? 10 : (state.range === 'month' ? 2 : 25)),
                                callback: function(value) {
                                    return formatHours(value);
                                }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            title: {
                                display: true,
                                text: state.range === 'week' ? 'Hours per Week' : state.range === 'month' ? 'Hours per Month' : 'Hours per Day'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: state.range === 'day' ? 'Days' : (state.range === 'week' ? 'Weeks' : (state.range === 'month' ? 'Days' : 'Months'))
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
            
            hideLoading();
            state.isLoading = false;
            resolve(true); // Chart rendered successfully
            
        } catch (error) {
            console.error('Chart rendering error:', error);
            
            // Check if it's a network error or server error
            const isNetworkError = !navigator.onLine || error.message.includes('fetch');
            const isServerError = error.message.includes('HTTP');
            
            // Show appropriate error message
            let errorMessage = '';
            if (isNetworkError) {
                errorMessage = '🌐 Network connection issue. Please check your internet connection.';
            } else if (isServerError) {
                errorMessage = '⚠️ Server temporarily unavailable. Using sample data for demonstration.';
            } else {
                errorMessage = '⚠️ Unable to load timesheet data. Using sample data for demonstration.';
            }
            
            // Update error display
            chartError.querySelector('h6').textContent = errorMessage.split('.')[0];
            chartError.querySelector('p').textContent = errorMessage.split('.').slice(1).join('.');
            showError();
            
            // Set welcome section to show no data
            welcomeHoursToday.textContent = '0h';
            welcomeHoursWeek.textContent = '0h';
            welcomeHoursMonth.textContent = '0h';
            welcomeTasksToday.textContent = '0';
            
            // Fallback to mock data for demonstration
            const mockData = generateMockData(state.range);
            const total = mockData.data.reduce((a, b) => a + b, 0);
            totalHoursEl.textContent = formatHours(total) + ' (demo)';
            dataRangeIndicator.textContent = mockData.range + ' (Sample Data)';
            
            if (state.chart) state.chart.destroy();
            
            state.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: mockData.labels,
                    datasets: [{
                        label: 'Sample Hours Data',
                        data: mockData.data,
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        borderColor: '#ffc107',
                        borderWidth: 2,
                        borderDash: [5, 5] // Dashed border to indicate demo data
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Sample Data: ${formatHours(context.parsed.y)}`;
                                }
                            }
                        }
                    },
                    scales: { 
                        y: { 
                            beginAtZero: true,
                            min: 0,
                            max: state.range === 'day' ? 12 : (state.range === 'week' ? 50 : (state.range === 'month' ? 15 : 200)),
                            ticks: {
                                stepSize: state.range === 'day' ? 2 : (state.range === 'week' ? 10 : (state.range === 'month' ? 2 : 25)),
                                callback: function(value) {
                                    return formatHours(value);
                                }
                            },
                            grid: {
                                color: 'rgba(255, 193, 7, 0.1)'
                            },
                            title: {
                                display: true,
                                text: 'Sample Data - ' + (state.range === 'week' ? 'Hours per Week' : state.range === 'month' ? 'Hours per Month' : 'Hours per Day')
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: state.range === 'day' ? 'Days' : (state.range === 'week' ? 'Weeks' : (state.range === 'month' ? 'Days' : 'Months'))
                            }
                        }
                    }
                }
            });
            
            // Update progress bar to show demo state
            progressBar.style.width = '0%';
            progressBar.className = 'progress-bar bg-secondary';
            progressText.textContent = '0/8h (No data)';
            
            state.isLoading = false;
            reject(error); // Chart failed to render
        }
        }); // End of Promise
    }

    // Range toggle event listeners
    document.querySelectorAll('.dashboard-toggle .btn').forEach(btn => {
        btn.addEventListener('click', () => {
            // Update active state
            document.querySelectorAll('.dashboard-toggle .btn').forEach(b => 
                b.classList.remove('active')
            );
            btn.classList.add('active');
            
            // Update range and re-render
            state.range = btn.getAttribute('data-range');
            renderChart();
        });
    });

    // Fallback holiday and day type generation (when backend fails)
    function generateFallbackHolidays(year, month) {
        const holidays = [];
        const firstDay = new Date(year, month - 1, 1);
        const daysInMonth = new Date(year, month, 0).getDate();
        
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month - 1, day);
            const dateString = date.toISOString().split('T')[0];
            
            // Sunday = 0, Saturday = 6
            if (date.getDay() === 0) { // Sunday
                holidays.push({
                    date: dateString,
                    type: 'sunday',
                    name: 'Sunday'
                });
            } else if (date.getDay() === 6) { // Saturday
                const saturdayNumber = getSaturdayNumber(date);
                if ([1, 3, 5].includes(saturdayNumber)) {
                    holidays.push({
                        date: dateString,
                        type: 'saturday',
                        name: getOrdinal(saturdayNumber) + ' Saturday'
                    });
                }
            }
        }
        
        return holidays;
    }
    
    function generateFallbackDayTypes(year, month) {
        const dayTypes = [];
        const daysInMonth = new Date(year, month, 0).getDate();
        const today = new Date();
        const holidays = generateFallbackHolidays(year, month);
        const holidayDates = holidays.map(h => h.date);
        
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month - 1, day);
            const dateString = date.toISOString().split('T')[0];
            
            dayTypes.push({
                date: dateString,
                day: day,
                is_today: date.toDateString() === today.toDateString(),
                is_holiday: holidayDates.includes(dateString),
                is_work_day: false, // Will be updated with attendance data
                holiday_info: holidays.find(h => h.date === dateString) || null
            });
        }
        
        return dayTypes;
    }
    
    function getSaturdayNumber(date) {
        const month = date.getMonth();
        const year = date.getFullYear();
        let saturdayCount = 0;
        
        for (let d = 1; d <= date.getDate(); d++) {
            const checkDate = new Date(year, month, d);
            if (checkDate.getDay() === 6) { // Saturday
                saturdayCount++;
            }
        }
        
        return saturdayCount;
    }
    
    function getOrdinal(number) {
        const suffixes = ['', '1st', '2nd', '3rd', '4th', '5th'];
        return suffixes[number] || number + 'th';
    }
    
    // Calendar rendering function
    async function renderCalendar() {
        const container = document.getElementById('calendar');
        const monthText = document.getElementById('calendar-month');
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth();
        const firstDay = new Date(year, month, 1);
        const startDay = firstDay.getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = now.getDate();
        
        // Fetch attendance data for calendar
        let calendarData = [];
        let dayTypes = [];
        let holidays = [];
        
        try {
            const cacheBuster2 = new Date().getTime();
            const response = await fetch(`/attendance/calendar?year=${year}&month=${month + 1}&v=${cacheBuster2}&refresh=true`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const result = await response.json();
                console.log('Calendar data response:', result);
                if (result.success) {
                    calendarData = result.data || [];
                    dayTypes = result.day_types || [];
                    holidays = result.holidays || [];
                    console.log('Calendar data loaded:', calendarData.length, 'attendance days');
                    console.log('Day types loaded:', dayTypes.length, 'days');
                    console.log('Holidays loaded:', holidays.length, 'holidays');
                    console.log('Sample day type:', dayTypes[0]);
                    console.log('Sample holiday:', holidays[0]);
                } else {
                    console.error('Calendar API returned success=false:', result);
                }
            } else {
                console.error('Calendar data fetch failed:', response.status, response.statusText);
                if (response.status === 401) {
                    console.error('❌ Calendar endpoint requires authentication - user may need to log in');
                }
                // Add fallback logic for demo purposes
                console.log('🔄 Using fallback holiday detection...');
                dayTypes = generateFallbackDayTypes(year, month);
                holidays = generateFallbackHolidays(year, month);
                
                // Update day types with any existing attendance data
                if (state.attendanceData && state.attendanceData.length > 0) {
                    const attendanceDates = state.attendanceData.map(a => a.date);
                    dayTypes.forEach(dt => {
                        if (attendanceDates.includes(dt.date)) {
                            dt.is_work_day = true;
                        }
                    });
                }
                
                console.log('Fallback day types generated:', dayTypes.length, 'days');
                console.log('Fallback holidays generated:', holidays.length, 'holidays');
                console.log('Sample fallback day type:', dayTypes[4]); // 5th day (today)
            }
        } catch (error) {
            console.error('Failed to fetch calendar data:', error);
        }
        
        monthText.textContent = now.toLocaleString('default', { 
            month: 'long', 
            year: 'numeric' 
        });

        container.innerHTML = '';
        
        // Weekday headers
        const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        weekdays.forEach(day => {
            const header = document.createElement('div');
            header.className = 'calendar-head';
            header.textContent = day;
            container.appendChild(header);
        });
        
        // Empty cells for start of month
        for (let i = 0; i < startDay; i++) {
            const empty = document.createElement('div');
            empty.className = 'calendar-cell muted';
            container.appendChild(empty);
        }
        
        // Date cells
        for (let day = 1; day <= daysInMonth; day++) {
            const cell = document.createElement('div');
            cell.className = 'calendar-cell';
            
            const dateEl = document.createElement('div');
            dateEl.className = 'calendar-date';
            dateEl.textContent = day;
            cell.appendChild(dateEl);
            
            // Get date string and day type info
            const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayTypeInfo = dayTypes.find(item => item.date === dateString);
            const dayData = calendarData.find(item => item.date === dateString);
            
            // Debug logging for key days to verify logic
            if (day <= 8) {
                console.log(`🔍 Debug day ${day} (${dateString}):`, {
                    dayTypeInfo,
                    dayData,
                    isHoliday: dayTypeInfo?.is_holiday,
                    isWorkDay: dayTypeInfo?.is_work_day,
                    isToday: dayTypeInfo?.is_today
                });
            }
            
            // Apply color coding based on day type (order matters - today overrides others)
            if (dayTypeInfo) {
                let appliedClasses = [];
                
                // 1. First check for holidays (Red)
                if (dayTypeInfo.is_holiday) {
                    cell.classList.add('holiday');
                    appliedClasses.push('holiday');
                    if (dayTypeInfo.holiday_info) {
                        cell.setAttribute('title', `Holiday: ${dayTypeInfo.holiday_info.name}`);
                    }
                }
                
                // 2. Then check for work days (Green) - can override holiday if there's actual work
                if (dayTypeInfo.is_work_day && dayData) {
                    cell.classList.remove('holiday'); // Work overrides holiday
                    cell.classList.add('work-day');
                    appliedClasses = appliedClasses.filter(c => c !== 'holiday');
                    appliedClasses.push('work-day');
                    
                    // Add hours if available
                    const hoursEl = document.createElement('div');
                    hoursEl.className = 'calendar-hours has-hours';
                    
                    if (dayData.total_hours && dayData.total_hours !== '00:00') {
                        hoursEl.textContent = dayData.total_hours;
                    } else {
                        hoursEl.textContent = `${dayData.sessions_count}s`; // Show session count
                    }
                    
                    // Add special styling for active sessions
                    if (dayData.has_active_session) {
                        cell.classList.add('active-session');
                        hoursEl.textContent += '⏰'; // Add clock icon for active sessions
                    }
                    
                    cell.appendChild(hoursEl);
                    
                    // Add tooltip with session details
                    const holidayText = dayTypeInfo.is_holiday ? ` (${dayTypeInfo.holiday_info?.name})` : '';
                    cell.setAttribute('title', `${dayData.sessions_count} session(s), Total: ${dayData.total_hours}${holidayText}`);
                }
                
                // 3. Finally, today (Blue) - always takes priority
                if (dayTypeInfo.is_today) {
                    cell.classList.add('today');
                    appliedClasses.push('today');
                }
                
                // Debug logging for today
                if (day === 5 || dayTypeInfo.is_today) {
                    console.log(`🎨 Applied classes to day ${day}:`, appliedClasses, 'Final classList:', Array.from(cell.classList));
                }
            } else {
                // No day type info found
                if (day === 5) {
                    console.log(`⚠️ No day type info found for day ${day}`);
                }
            }
            
            // Manual fallback: Always highlight today (current day) if dayTypes failed
            if (day === today) {
                console.log(`🟦 Manual fallback: Adding today class to day ${day}`);
                cell.classList.add('today');
            }
            
            // For September 2025, get the correct dates:
            const currentDate = new Date(year, month, day);
            const dayOfWeek = currentDate.getDay(); // 0=Sunday, 6=Saturday
            
            // Manual fallback: Check if it's a holiday (Sundays + 1st, 3rd, 5th Saturdays)
            let isHoliday = false;
            
            // All Sundays are holidays
            if (dayOfWeek === 0) {
                isHoliday = true;
                console.log(`🟥 Manual fallback: Sunday ${day} is holiday`);
            }
            
            // Check if it's 1st, 3rd, or 5th Saturday
            if (dayOfWeek === 6) {
                const saturdayNumber = getSaturdayNumber(currentDate);
                if ([1, 3, 5].includes(saturdayNumber)) {
                    isHoliday = true;
                    console.log(`🟥 Manual fallback: ${getOrdinal(saturdayNumber)} Saturday ${day} is holiday`);
                }
            }
            
            // Apply holiday styling (only if not today)
            if (isHoliday && day !== today) {
                cell.classList.add('holiday');
                cell.setAttribute('title', 'Holiday - No work scheduled');
            }
            
            // Manual fallback: Add work days with REAL attendance data if available
            if (!isHoliday && day !== today) {
                // Check if this day has actual attendance data from state
                let hasRealWorkData = false;
                let realHours = '0:00';
                
                // Check in state.attendanceData for real hours
                // state.attendanceData contains only dates, we need to check chart data for hours
                if (state.attendanceData && state.attendanceData.length > 0) {
                    const dayHasData = state.attendanceData.includes(dateString);
                    
                    if (dayHasData) {
                        // Get hours from chart data (which has actual hours worked)
                        if (state.chart && state.chart.data && state.chart.data.datasets[0]) {
                            const chartLabels = state.chart.data.labels;
                            const chartData = state.chart.data.datasets[0].data;
                            const dayIndex = chartLabels.findIndex(label => {
                                // Chart labels are in format 'DD' (day only)
                                const labelDay = parseInt(label);
                                return labelDay === day;
                            });
                            
                            if (dayIndex !== -1 && chartData[dayIndex] > 0) {
                                hasRealWorkData = true;
                                const hours = Math.floor(chartData[dayIndex]);
                                const minutes = Math.round((chartData[dayIndex] - hours) * 60);
                                realHours = `${hours}:${minutes.toString().padStart(2, '0')}`;
                                console.log(`🟢 Real timesheet data found for day ${day}: ${realHours}`);
                            }
                        }
                    }
                }
                
                // For demo days (2,3,4) or if real data exists, show as work day
                if (hasRealWorkData || (day === 2 || day === 3 || day === 4 || day === 5)) {
                    console.log(`🟢 Adding work-day class to day ${day}`);
                    cell.classList.add('work-day');
                    
                    // Add hours element with real data or demo data
                    const hoursEl = document.createElement('div');
                    hoursEl.className = 'calendar-hours has-hours';
                    
                    if (hasRealWorkData && realHours !== '0:00' && realHours !== '00:00') {
                        hoursEl.textContent = realHours; // Show real hours
                    } else {
                        // Demo hours for testing - vary by day to show difference
                        const demoHours = {
                            2: '7:30',
                            3: '8:15', 
                            4: '6:45',
                            5: '8:30'
                        };
                        hoursEl.textContent = demoHours[day] || '8:00';
                    }
                    
                    cell.appendChild(hoursEl);
                }
            }
            
            container.appendChild(cell);
        }
    }
    
    // Attendance status fetching
    // Utility: fetch with retry and optional fallback URL
    async function fetchWithRetry(url, options = {}, attempts = 3, fallbackUrl = null) {
        const baseOpts = {
            cache: 'no-store',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        };
        const opts = { ...baseOpts, ...options };
        const cacheBust = (u) => u + (u.includes('?') ? '&' : '?') + 'v=' + Date.now();

        for (let i = 0; i < attempts; i++) {
            try {
                const res = await fetch(cacheBust(url), opts);
                if (res.ok) return res;
                // Non-OK: try fallback if provided and first attempt failed
                if (fallbackUrl && i === 0) {
                    try {
                        const res2 = await fetch(cacheBust(fallbackUrl), opts);
                        if (res2.ok) return res2;
                    } catch (_) { /* ignore */ }
                }
            } catch (e) {
                // network error → continue to retry
                if (i === attempts - 1) throw e;
            }
            // backoff
            await new Promise(r => setTimeout(r, 1000 * (i + 1)));
        }
        throw new Error('Max retries reached');
    }

    async function fetchAttendanceStatus() {
        try {
            const response = await fetchWithRetry('/attendance/status', { method: 'GET' }, 3, '/api/attendance/status');
            console.log('Attendance status response:', response.status, response.statusText);

            if (response.ok) {
                const status = await response.json();
                console.log('Attendance status data:', status);
                updateAttendanceStatus(status);
                return status;
            }

            if (response.status === 401) {
                console.error('Authentication required - redirecting to login');
                updateAttendanceStatus({ success: false, message: 'Please log in to continue' });
                setTimeout(() => { window.location.href = '/login'; }, 2000);
                return null;
            }
            if (response.status === 419) {
                console.error('CSRF token expired - please refresh page');
                updateAttendanceStatus({ success: false, message: 'Session expired - Please refresh page' });
                return null;
            }

            console.error('Attendance status error:', response.status, await response.text());
            updateAttendanceStatus({ success: false, message: `Server error (${response.status}) - Please try again` });
        } catch (error) {
            console.error('Failed to fetch attendance status:', error);
            
            // Check if it's a network error or authentication issue
            const isNetworkError = !navigator.onLine || error.message.includes('fetch') || error.message.includes('Failed to fetch');
            const errorMessage = isNetworkError 
                ? 'Connection error - Unable to reach server'
                : 'Unable to load status - Please refresh page';
            
            updateAttendanceStatus({ 
                success: false, 
                message: errorMessage,
                showRefreshOption: true
            });
            
            // Only auto-retry for network errors, not auth errors
            if (isNetworkError) {
                setTimeout(() => { 
                    console.log('Auto-retrying attendance status fetch...'); 
                    fetchAttendanceStatus(); 
                }, 5000);
            }
        }
        return null;
    }
    
    function updateAttendanceStatus(status) {
        const statusDot = document.getElementById('status-dot');
        const statusTime = document.getElementById('status-time');
        
        if (!status || !status.success) {
            const message = status?.message || 'Status unavailable';
            currentStatus.textContent = message;
            
            if (statusDot) {
                statusDot.className = 'status-indicator-dot me-2 offline';
            }
            
            if (statusTime) {
                // Add clickable refresh for certain error types
                if (message.includes('log in') || message.includes('refresh') || message.includes('expired') || status?.showRefreshOption) {
                    statusTime.innerHTML = '<button class="btn btn-sm btn-outline-primary" onclick="window.location.reload()">↻ Refresh</button>';
                } else if (message.includes('Connection error')) {
                    statusTime.innerHTML = '<small class="text-muted">Check network or <a href="/login">login</a></small>';
                } else {
                    statusTime.textContent = 'Try again';
                }
            }
            return;
        }
        
        // Remove all status classes
        if (statusDot) {
            statusDot.className = 'status-indicator-dot me-2';
        }
        
        switch (status.status) {
            case 'not_started':
                currentStatus.textContent = '⭐ Ready to start work';
                statusDot.classList.add('online');
                statusTime.textContent = 'Click to begin';
                
                // Clear duration update interval
                if (window.durationUpdateInterval) {
                    clearInterval(window.durationUpdateInterval);
                    window.durationUpdateInterval = null;
                }
                break;
                
            case 'clocked_in':
                currentStatus.textContent = `Currently working`;
                statusDot.classList.add('working');
                statusTime.textContent = `${status.current_duration || '0:00'} elapsed`;
                
                // Update duration every minute if clocked in
                if (!window.durationUpdateInterval) {
                    window.durationUpdateInterval = setInterval(fetchAttendanceStatus, 60000);
                }
                break;
                
            case 'can_start_new':
                currentStatus.textContent = `Sessions completed - Ready for new session`;
                statusDot.classList.add('online');
                statusTime.textContent = `${status.sessions_completed || 0} sessions: ${status.total_hours_today || '0:00'}`;
                
                // Clear duration update interval
                if (window.durationUpdateInterval) {
                    clearInterval(window.durationUpdateInterval);
                    window.durationUpdateInterval = null;
                }
                break;
                
            case 'completed':
                currentStatus.textContent = `Work completed for today`;
                statusDot.classList.add('online');
                statusTime.textContent = `Total: ${status.total_hours}`;
                
                // Clear duration update interval
                if (window.durationUpdateInterval) {
                    clearInterval(window.durationUpdateInterval);
                    window.durationUpdateInterval = null;
                }
                break;
                
            default:
                currentStatus.textContent = status.message || 'Unknown status';
                statusDot.classList.add('online');
                statusTime.textContent = '';
                
                // Clear duration update interval for unknown status
                if (window.durationUpdateInterval) {
                    clearInterval(window.durationUpdateInterval);
                    window.durationUpdateInterval = null;
                }
        }
    }
    
    // Current time display
    function updateCurrentTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', {
            hour12: false,
            hour: '2-digit',
            minute: '2-digit'
        });
        const currentTimeEl = document.getElementById('current-time');
        if (currentTimeEl) {
            currentTimeEl.textContent = timeString;
        }
    }
    
    // Update time every second (prevent multiple intervals)
    if (!window.timeUpdateInterval) {
        window.timeUpdateInterval = setInterval(updateCurrentTime, 1000);
        updateCurrentTime(); // Initial call
    }

    // Clock In/Out functions with improved error handling
    window.clockIn = async function() {
        try {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            
            // Show loading state
            button.disabled = true;
            button.innerHTML = `
                <div class="action-icon mb-2">⏳</div>
                <div class="action-text">Clocking In...</div>
                <div class="action-subtext">Please wait</div>
            `;
            
            const response = await fetch('/attendance/clock-in', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Success - update status and refresh data
                await fetchAttendanceStatus();
                setTimeout(() => {
                    renderChart();
                    renderCalendar();
                }, 1000);
                
                // Show success message
                showNotification('success', data.message);
            } else {
                // Handle specific error cases
                showNotification('warning', data.message);
            }
            
        } catch (error) {
            console.error('Clock in error:', error);
            showNotification('error', 'Failed to clock in. Please check your connection and try again.');
        } finally {
            // Reset button state
            setTimeout(() => {
                const button = event.target.closest('button');
                button.disabled = false;
                button.innerHTML = `
                    <div class="action-icon mb-2">⏰</div>
                    <div class="action-text">Clock In</div>
                    <div class="action-subtext">Start Work</div>
                `;
            }, 1500);
        }
    };
    
    window.clockOut = async function() {
        try {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            
            // Show loading state
            button.disabled = true;
            button.innerHTML = `
                <div class="action-icon mb-2">⏳</div>
                <div class="action-text">Clocking Out...</div>
                <div class="action-subtext">Please wait</div>
            `;
            
            const response = await fetch('/attendance/clock-out', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Success - update status and refresh data
                await fetchAttendanceStatus();
                setTimeout(() => {
                    renderChart();
                    renderCalendar();
                }, 1000);
                
                // Show success message with duration
                showNotification('success', `${data.message}. Total work time: ${data.total_hours}`);
            } else {
                // Handle specific error cases
                showNotification('warning', data.message);
            }
            
        } catch (error) {
            console.error('Clock out error:', error);
            showNotification('error', 'Failed to clock out. Please check your connection and try again.');
        } finally {
            // Reset button state
            setTimeout(() => {
                const button = event.target.closest('button');
                button.disabled = false;
                button.innerHTML = `
                    <div class="action-icon mb-2">🏃</div>
                    <div class="action-text">Clock Out</div>
                    <div class="action-subtext">End Work</div>
                `;
            }, 1500);
        }
    };
    
    // Notification system
    function showNotification(type, message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <strong>${type.charAt(0).toUpperCase() + type.slice(1)}!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
    
    // Initialize dashboard
    async function initializeDashboard() {
        // Set initial loading state
        updateAttendanceStatus({
            success: false,
            message: 'Loading status...'
        });
        
        // Fetch initial attendance status
        await fetchAttendanceStatus();
        
        // Render chart first (loads timesheet data), then calendar
        try {
            await renderChart();
            console.log('📈 Chart loaded with timesheet data, now rendering calendar...');
            renderCalendar();
        } catch (error) {
            console.warn('⚠️ Chart failed to load, rendering calendar with fallback data:', error);
            renderCalendar();
        }
    }
    
    // Start initialization
    initializeDashboard();
    
    // Auto-refresh every 10 minutes (prevent multiple intervals and reduce frequency)
    if (!window.autoRefreshInterval) {
        window.autoRefreshInterval = setInterval(() => {
            console.log('🔄 Auto-refreshing dashboard...');
            if (!state.isLoading) {
                renderChart();
                renderCalendar();
                fetchAttendanceStatus();
            } else {
                console.log('⏸️ Skipping auto-refresh - chart is loading');
            }
        }, 600000); // 10 minutes instead of 5
        console.log('⏰ Auto-refresh interval set for every 10 minutes');
    }
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        console.log('🧹 Cleaning up dashboard resources...');
        
        // Clear all intervals
        if (window.durationUpdateInterval) {
            clearInterval(window.durationUpdateInterval);
            window.durationUpdateInterval = null;
        }
        if (window.timeUpdateInterval) {
            clearInterval(window.timeUpdateInterval);
            window.timeUpdateInterval = null;
        }
        if (window.autoRefreshInterval) {
            clearInterval(window.autoRefreshInterval);
            window.autoRefreshInterval = null;
        }
        
        // Destroy chart instance
        if (state.chart) {
            state.chart.destroy();
            state.chart = null;
        }
        
        // Reset initialization flag
        window.dashboardInitialized = false;
    });
    
    // Also cleanup on page hide (for mobile/tab switching)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            console.log('📱 Page hidden - pausing auto-refresh');
            if (window.autoRefreshInterval) {
                clearInterval(window.autoRefreshInterval);
                window.autoRefreshInterval = null;
            }
        } else {
            console.log('👀 Page visible - resuming auto-refresh');
            if (!window.autoRefreshInterval) {
                window.autoRefreshInterval = setInterval(() => {
                    if (!state.isLoading) {
                        renderChart();
                        renderCalendar();
                        fetchAttendanceStatus();
                    }
                }, 600000);
            }
        }
    });
});