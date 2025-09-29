@extends('layouts.app')

@section('content')
{{-- Include CSRF token meta tag if it's not in layouts.app --}}
@if(!View::hasSection('csrf-meta'))
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endif

<div 
    class="dashboard-employee bg-gradient-to-br from-green-50 to-emerald-100 min-h-screen" 
    x-data="employeeDashboard()"
    @dashboard-refresh.window="loadDashboardData()" {{-- Listener for refreshing dashboard data after clock-out --}}
>
    {{-- Employee Header --}}
    <div class="bg-white shadow-lg border-b border-green-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            {{-- Avatar/Role Icon --}}
                            <div class="h-12 w-12 bg-green-500 rounded-full flex items-center justify-center shadow-lg">
                                <i class="bi bi-person-check text-white text-xl" aria-hidden="true"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}!</h1>
                            <p class="text-sm text-emerald-600 font-medium">{{ Auth::user()->getDisplayRole() }} Dashboard</p>
                        </div>
                    </div>
                    {{-- Clock Display --}}
                    <div class="flex items-center space-x-3 p-2 bg-gray-100 rounded-xl shadow-inner">
                        <div class="text-right">
                            <p class="text-sm text-gray-600 font-semibold">{{ now()->format('l, F j, Y') }}</p>
                            {{-- Now updating every second --}}
                            <p class="text-xl font-mono text-gray-900" x-text="currentTime"></p>
                        </div>
                        <div class="h-10 w-10 bg-emerald-100 rounded-full flex items-center justify-center">
                            <i class="bi bi-calendar3 text-emerald-600 text-lg" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions Section --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            {{-- Clock In/Out Card --}}
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6 hover:shadow-xl transition-shadow" x-data="clockingSystem()">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-clock text-green-600 text-xl" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-gray-900">Quick Clock</h3>
                        <p class="text-sm text-gray-500">Manage your work time</p>
                    </div>
                </div>
                
                {{-- Clock Buttons --}}
                <div class="mt-4 flex space-x-3">
                    <button 
                        @click="clockIn()"
                        x-show="!isClockedIn"
                        class="flex-1 px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors text-base font-semibold shadow-md hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500 focus:ring-opacity-50"
                    >
                        <i class="bi bi-play-circle mr-2"></i>Clock In
                    </button>
                    <button 
                        @click="clockOut()"
                        x-show="isClockedIn"
                        class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors text-base font-semibold shadow-md hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-red-500 focus:ring-opacity-50"
                    >
                        <i class="bi bi-stop-circle mr-2"></i>Clock Out
                    </button>
                </div>
                
                {{-- Active Session Display (Styled for emphasis) --}}
                <div x-show="isClockedIn" class="mt-4 p-4 bg-green-50 border-2 border-green-400 rounded-xl shadow-inner text-center animate-pulse-light">
                    <p class="text-sm font-semibold text-green-700">
                        <i class="bi bi-clock-fill mr-2 animate-spin-slow"></i>
                        SESSION ACTIVE:
                    </p>
                    <p class="text-3xl font-extrabold text-green-800 font-mono" x-text="sessionDuration">00:00:00</p>
                </div>
            </div>

            {{-- Today's Summary Card --}}
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-graph-up text-blue-600 text-xl" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-gray-900">Today's Hours</h3>
                        <p class="text-sm text-gray-500">Total logged time</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-4xl font-extrabold text-gray-900" x-text="todayHours">0.0h</p>
                    <div class="mt-3 bg-gray-200 rounded-full h-2">
                        <div 
                            class="bg-emerald-500 h-2 rounded-full transition-all duration-500" 
                            :style="`width: ${Math.min((parseFloat(todayHours) / 8) * 100, 100)}%`"
                        ></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 font-medium">Target: 8.0 hours</p>
                </div>
            </div>

            {{-- Weekly Summary Card --}}
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-calendar-week text-purple-600 text-xl" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-gray-900">This Week</h3>
                        <p class="text-sm text-gray-500">Running total</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-4xl font-extrabold text-gray-900" x-text="weeklyHours">0.0h</p>
                    <p class="text-sm text-gray-500 mt-1">
                        <span class="text-emerald-600 font-bold" x-text="weeklyDays">0</span> days worked
                    </p>
                </div>
            </div>

            {{-- Pending Tasks Card --}}
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-list-check text-yellow-600 text-xl" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-gray-900">Pending Tasks</h3>
                        <p class="text-sm text-gray-500">Timesheet entries</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-4xl font-extrabold text-gray-900" x-text="pendingTasks">0</p>
                    <p class="text-sm text-gray-500 mt-1">Awaiting submission</p>
                </div>
                <div class="mt-3">
                    <a href="{{ route('timesheet.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-semibold flex items-center">
                        Go to Timesheets <i class="bi bi-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Recent Activities & Quick Links --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Recent Activities --}}
            <div class="bg-white rounded-xl shadow-lg border border-green-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900">Recent Log History</h3>
                        <button class="text-sm text-emerald-600 hover:text-emerald-700 font-semibold">
                            View all
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4" x-data="activitiesSystem()">
                        <template x-for="activity in activities" :key="activity.id">
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg transition-shadow hover:shadow-sm">
                                <div class="flex-shrink-0">
                                    {{-- Dynamic Icon based on activity type --}}
                                    <div class="h-8 w-8 rounded-full flex items-center justify-center"
                                        :class="{
                                            'bg-green-100 text-green-600': activity.type === 'clock_out', 
                                            'bg-blue-100 text-blue-600': activity.type === 'day'
                                        }">
                                        <i :class="activity.icon" class="text-sm" aria-hidden="true"></i>
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900" x-text="activity.description"></p>
                                    <p class="text-xs text-gray-500" x-text="activity.time_ago"></p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-semibold text-emerald-700" x-text="activity.hours"></span>
                                </div>
                            </div>
                        </template>
                        
                        {{-- Placeholder if no activities --}}
                        <div x-show="activities.length === 0" class="text-center py-8">
                            <i class="bi bi-clock-history text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500 font-medium">No recent activities to show</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="bg-white rounded-xl shadow-lg border border-green-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900">Quick Access</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Timesheets Link --}}
                        <a href="{{ route('timesheet.index') }}" 
                           class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-xl transition-all shadow-sm hover:shadow-md group">
                            <div class="flex-shrink-0">
                                <i class="bi bi-journal-text text-green-600 text-xl group-hover:scale-110 transition-transform"></i>
                            </div>
                            <div class="ml-3">
                                <p class="font-semibold text-gray-900">Timesheets</p>
                                <p class="text-sm text-gray-500">Log your work</p>
                            </div>
                        </a>

                        {{-- Attendance Link --}}
                        <a href="{{ route('attendance.index') }}" 
                           class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-xl transition-all shadow-sm hover:shadow-md group">
                            <div class="flex-shrink-0">
                                <i class="bi bi-calendar-check text-blue-600 text-xl group-hover:scale-110 transition-transform"></i>
                            </div>
                            <div class="ml-3">
                                <p class="font-semibold text-gray-900">Attendance</p>
                                <p class="text-sm text-gray-500">View records</p>
                            </div>
                        </a>

                        {{-- Daily Updates Link --}}
                        <a href="{{ route('daily-update.index') }}" 
                           class="flex items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-xl transition-all shadow-sm hover:shadow-md group">
                            <div class="flex-shrink-0">
                                <i class="bi bi-chat-dots text-purple-600 text-xl group-hover:scale-110 transition-transform"></i>
                            </div>
                            <div class="ml-3">
                                <p class="font-semibold text-gray-900">Daily Updates</p>
                                <p class="text-sm text-gray-500">Submit reports</p>
                            </div>
                        </a>

                        {{-- Profile Link --}}
                        <a href="{{ route('profile.edit') }}" 
                           class="flex items-center p-4 bg-yellow-50 hover:bg-yellow-100 rounded-xl transition-all shadow-sm hover:shadow-md group">
                            <div class="flex-shrink-0">
                                <i class="bi bi-person-gear text-yellow-600 text-xl group-hover:scale-110 transition-transform"></i>
                            </div>
                            <div class="ml-3">
                                <p class="font-semibold text-gray-900">Profile</p>
                                <p class="text-sm text-gray-500">Manage settings</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom utility for the active session pulsing effect */
@keyframes pulse-light {
    0% { background-color: #ecfdf5; border-color: #a7f3d0; } /* green-50, green-200 */
    50% { background-color: #d1fae5; border-color: #34d399; } /* green-100, green-400 */
    100% { background-color: #ecfdf5; border-color: #a7f3d0; }
}

.animate-pulse-light {
    animation: pulse-light 3s infinite ease-in-out;
}

.animate-spin-slow {
    animation: spin 3s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/dashboard-employee.js') }}"></script>



@endpush