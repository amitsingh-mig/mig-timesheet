@extends('layouts.app')

@section('content')
<div class="dashboard-employee bg-gradient-to-br from-green-50 to-emerald-100 min-h-screen" x-data="employeeDashboard()">
    {{-- Employee Header --}}
    <div class="bg-white shadow-sm border-b border-green-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="bi bi-person-check text-white text-xl" aria-hidden="true"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}!</h1>
                            <p class="text-sm text-green-600 font-medium">{{ Auth::user()->getDisplayRole() }} Dashboard</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">{{ now()->format('l, F j, Y') }}</p>
                            <p class="text-xs text-gray-400" x-text="currentTime">{{ now()->format('g:i A') }}</p>
                        </div>
                        <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="bi bi-calendar3 text-green-600" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions Section --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            {{-- Clock In/Out Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-clock text-green-600" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Quick Clock</h3>
                        <p class="text-sm text-gray-500">Manage your work time</p>
                    </div>
                </div>
                <div class="mt-4 flex space-x-3" x-data="clockingSystem()">
                    <button 
                        @click="clockIn()"
                        x-show="!isClockedIn"
                        class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium"
                    >
                        <i class="bi bi-play-circle mr-2"></i>Clock In
                    </button>
                    <button 
                        @click="clockOut()"
                        x-show="isClockedIn"
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium"
                    >
                        <i class="bi bi-stop-circle mr-2"></i>Clock Out
                    </button>
                </div>
                <div x-show="currentSession" class="mt-3 p-3 bg-green-50 rounded-lg">
                    <p class="text-xs text-green-700">
                        <i class="bi bi-clock-fill mr-1"></i>
                        Current session: <span x-text="sessionDuration">00:00</span>
                    </p>
                </div>
            </div>

            {{-- Today's Summary Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-graph-up text-blue-600" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Today's Hours</h3>
                        <p class="text-sm text-gray-500">Work progress</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-3xl font-bold text-gray-900" x-text="todayHours">0.0h</p>
                    <div class="mt-2 bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full transition-all duration-300" :style="`width: ${Math.min((parseFloat(todayHours) / 8) * 100, 100)}%`"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Target: 8.0 hours</p>
                </div>
            </div>

            {{-- Weekly Summary Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-calendar-week text-purple-600" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">This Week</h3>
                        <p class="text-sm text-gray-500">Weekly total</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-3xl font-bold text-gray-900" x-text="weeklyHours">0.0h</p>
                    <p class="text-sm text-gray-500">
                        <span class="text-green-600 font-medium" x-text="weeklyDays">0</span> days worked
                    </p>
                </div>
            </div>

            {{-- Pending Tasks Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-list-check text-yellow-600" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Tasks</h3>
                        <p class="text-sm text-gray-500">Pending items</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-3xl font-bold text-gray-900" x-text="pendingTasks">0</p>
                    <p class="text-sm text-gray-500">Timesheet entries</p>
                </div>
                <div class="mt-3">
                    <a href="{{ route('timesheet.index') }}" class="text-sm text-green-600 hover:text-green-700 font-medium">
                        View all timesheets →
                    </a>
                </div>
            </div>
        </div>

        {{-- Recent Activities & Quick Links --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Recent Activities --}}
            <div class="bg-white rounded-xl shadow-sm border border-green-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Activities</h3>
                        <button class="text-sm text-green-600 hover:text-green-700 font-medium">
                            View all
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4" x-data="{ activities: [] }" x-init="loadActivities()">
                        <template x-for="activity in activities" :key="activity.id">
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full flex items-center justify-center"
                                         :class="activity.type === 'clock_in' ? 'bg-green-100 text-green-600' : 
                                                 activity.type === 'clock_out' ? 'bg-red-100 text-red-600' : 
                                                 'bg-blue-100 text-blue-600'">
                                        <i :class="activity.icon" class="text-sm" aria-hidden="true"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900" x-text="activity.description"></p>
                                    <p class="text-xs text-gray-500" x-text="activity.time_ago"></p>
                                </div>
                            </div>
                        </template>
                        
                        {{-- Placeholder if no activities --}}
                        <div x-show="activities.length === 0" class="text-center py-8">
                            <i class="bi bi-clock-history text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No recent activities</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="bg-white rounded-xl shadow-sm border border-green-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Quick Access</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <a href="{{ route('timesheet.index') }}" 
                           class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors group">
                            <div class="flex-shrink-0">
                                <i class="bi bi-journal-text text-green-600 text-xl group-hover:scale-110 transition-transform"></i>
                            </div>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Timesheets</p>
                                <p class="text-sm text-gray-500">Log your work</p>
                            </div>
                        </a>

                        <a href="{{ route('attendance.index') }}" 
                           class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors group">
                            <div class="flex-shrink-0">
                                <i class="bi bi-calendar-check text-blue-600 text-xl group-hover:scale-110 transition-transform"></i>
                            </div>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Attendance</p>
                                <p class="text-sm text-gray-500">View records</p>
                            </div>
                        </a>

                        <a href="{{ route('daily-update.index') }}" 
                           class="flex items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors group">
                            <div class="flex-shrink-0">
                                <i class="bi bi-chat-dots text-purple-600 text-xl group-hover:scale-110 transition-transform"></i>
                            </div>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Updates</p>
                                <p class="text-sm text-gray-500">Daily reports</p>
                            </div>
                        </a>

                        <a href="{{ route('profile.edit') }}" 
                           class="flex items-center p-4 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition-colors group">
                            <div class="flex-shrink-0">
                                <i class="bi bi-person-gear text-yellow-600 text-xl group-hover:scale-110 transition-transform"></i>
                            </div>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Profile</p>
                                <p class="text-sm text-gray-500">Settings</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Alpine.js Dashboard Logic --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('employeeDashboard', () => ({
        currentTime: '{{ now()->format("g:i A") }}',
        todayHours: '0.0',
        weeklyHours: '0.0',
        weeklyDays: 0,
        pendingTasks: 0,
        
        init() {
            this.loadDashboardData();
            this.updateClock();
            setInterval(() => this.updateClock(), 60000); // Update every minute
        },

        updateClock() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
        },

        async loadDashboardData() {
            try {
                const response = await fetch('/dashboard/data');
                const data = await response.json();
                
                if (data.success) {
                    this.todayHours = data.today ? data.today + 'h' : '0.0h';
                    this.weeklyHours = data.weekly ? data.weekly + 'h' : '0.0h';
                    this.pendingTasks = data.tasks || 0;
                    this.weeklyDays = Math.floor(parseFloat(data.weekly || 0) / 8); // Estimate days
                }
            } catch (error) {
                console.error('Failed to load dashboard data:', error);
            }
        }
    }));

    Alpine.data('clockingSystem', () => ({
        isClockedIn: false,
        currentSession: null,
        sessionDuration: '00:00',

        init() {
            this.checkClockStatus();
        },

        async checkClockStatus() {
            try {
                const response = await fetch('/attendance/status');
                const data = await response.json();
                
                if (data.success) {
                    this.isClockedIn = data.status === 'clocked_in';
                    if (this.isClockedIn) {
                        this.currentSession = data.attendance;
                        this.updateSessionDuration();
                        setInterval(() => this.updateSessionDuration(), 60000);
                    }
                }
            } catch (error) {
                console.error('Failed to check clock status:', error);
            }
        },

        updateSessionDuration() {
            if (!this.currentSession || !this.currentSession.clock_in) return;
            
            const clockIn = new Date(this.currentSession.clock_in);
            const now = new Date();
            const diff = now - clockIn;
            
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            
            this.sessionDuration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
        },

        async clockIn() {
            try {
                const response = await fetch('/attendance/clock-in', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.isClockedIn = true;
                    this.currentSession = data.attendance;
                    this.showNotification('Clocked in successfully!', 'success');
                    this.updateSessionDuration();
                    setInterval(() => this.updateSessionDuration(), 60000);
                } else {
                    this.showNotification(data.message || 'Failed to clock in', 'error');
                }
            } catch (error) {
                this.showNotification('Network error occurred', 'error');
            }
        },

        async clockOut() {
            try {
                const response = await fetch('/attendance/clock-out', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.isClockedIn = false;
                    this.currentSession = null;
                    this.sessionDuration = '00:00';
                    this.showNotification(`Clocked out successfully! Total: ${data.total_hours}`, 'success');
                    this.$dispatch('dashboard-refresh'); // Refresh dashboard data
                } else {
                    this.showNotification(data.message || 'Failed to clock out', 'error');
                }
            } catch (error) {
                this.showNotification('Network error occurred', 'error');
            }
        },

        showNotification(message, type) {
            // Simple notification - can be enhanced with a proper notification system
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
    }));

    Alpine.data('activities', () => ({
        activities: [],

        async loadActivities() {
            try {
                const response = await fetch('/attendance/data?limit=5');
                const data = await response.json();
                
                if (data.success && data.attendance) {
                    this.activities = data.attendance.map(item => ({
                        id: item.id,
                        type: item.status === 'present' ? 'clock_in' : 'attendance',
                        icon: item.status === 'present' ? 'bi-clock' : 'bi-calendar',
                        description: item.status === 'present' ? 
                            `Worked ${item.total_hours || '0:00'} hours` : 
                            'Attendance recorded',
                        time_ago: this.formatTimeAgo(item.date)
                    }));
                }
            } catch (error) {
                console.error('Failed to load activities:', error);
            }
        },

        formatTimeAgo(date) {
            const now = new Date();
            const itemDate = new Date(date);
            const diffDays = Math.floor((now - itemDate) / (1000 * 60 * 60 * 24));
            
            if (diffDays === 0) return 'Today';
            if (diffDays === 1) return 'Yesterday';
            return `${diffDays} days ago`;
        }
    }));
});
</script>
@endsection