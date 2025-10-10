@extends('layouts.app')

@section('content')
<!-- Modern Clean Profile Page -->
<div class="container-fluid px-4">
    <!-- Modern Profile Header -->
    <div class="profile-header">
        <div class="profile-info">
            <img src="{{ $user->getProfilePhotoUrl() }}" 
                 alt="{{ $user->name }}" 
                 class="profile-img"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'; this.nextElementSibling.style.alignItems='center'; this.nextElementSibling.style.justifyContent='center';">
            <div class="profile-img-fallback">
                {{ substr($user->name, 0, 2) }}
            </div>

            <div class="profile-details">
                <h2 class="profile-name">{{ $user->name }}</h2>
                <p class="profile-email">{{ $user->email }}</p>

                <div class="badges">
                    <div class="badge">
                        <i class="bi bi-shield-check"></i>
                        {{ $user->getDisplayRole() }}
                    </div>
                    <div class="badge">
                        <i class="bi bi-building"></i>
                        {{ $user->getDepartment() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="{{ route('profile.edit') }}" class="btn-profile">
                <i class="bi bi-pencil"></i>
                Edit Profile
            </a>
            <a href="{{ url()->previous() }}" class="btn-profile">
                <i class="bi bi-arrow-left"></i>
                Back
            </a>
        </div>
    </div>
</div>

<div class="container">
    <div class="row g-4">
        <!-- Main Profile Card -->
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h3 class="card-title-modern">Profile Information</h3>
                </div>
                <div class="card-body-modern">
                    <div class="info-grid-modern">
                        <!-- Full Name -->
                        <div class="info-item-modern">
                            <div class="info-icon-modern name-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="info-content-modern">
                                <label class="info-label-modern">Full Name</label>
                                <span class="info-value-modern">{{ $user->name }}</span>
                            </div>
                        </div>
                        
                        <!-- Email -->
                        <div class="info-item-modern">
                            <div class="info-icon-modern email-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="info-content-modern">
                                <label class="info-label-modern">Email Address</label>
                                <span class="info-value-modern">{{ $user->email }}</span>
                            </div>
                        </div>
                        
                        <!-- Role -->
                        <div class="info-item-modern">
                            <div class="info-icon-modern role-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="info-content-modern">
                                <label class="info-label-modern">Role</label>
                                <span class="info-value-modern">
                                    <span class="status-badge-modern {{ $user->role && $user->role->name === 'admin' ? 'admin-status' : 'employee-status' }}">
                                        {{ $user->role ? strtoupper($user->role->name) : 'N/A' }}
                                    </span>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Department -->
                        <div class="info-item-modern">
                            <div class="info-icon-modern dept-icon">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="info-content-modern">
                                <label class="info-label-modern">Department</label>
                                <span class="info-value-modern">
                                    <span class="status-badge-modern dept-status">
                                        {{ strtoupper($user->getDepartment()) }}
                                    </span>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Account Status -->
                        <div class="info-item-modern">
                            <div class="info-icon-modern status-icon">
                                <i class="bi bi-{{ $user->email_verified_at ? 'check-circle' : 'exclamation-triangle' }}"></i>
                            </div>
                            <div class="info-content-modern">
                                <label class="info-label-modern">Account Status</label>
                                <span class="info-value-modern">
                                    <span class="status-badge-modern {{ $user->email_verified_at ? 'active-status' : 'inactive-status' }}">
                                        {{ $user->email_verified_at ? 'ACTIVE' : 'INACTIVE' }}
                                    </span>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Join Date -->
                        <div class="info-item-modern">
                            <div class="info-icon-modern date-icon">
                                <i class="bi bi-calendar-plus"></i>
                            </div>
                            <div class="info-content-modern">
                                <label class="info-label-modern">Date of Joining</label>
                                <span class="info-value-modern">{{ $user->created_at ? $user->created_at->format('F j, Y') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="stats-card-modern">
                <h4 class="stats-title-modern">Quick Stats</h4>
                <div class="stats-grid-modern">
                    <div class="stat-item-modern">
                        <div class="stat-number-modern primary-stat">{{ $attendanceSummary['total_attendance_days'] }}</div>
                        <div class="stat-label-modern">Attendance Days</div>
                    </div>
                    <div class="stat-item-modern">
                        <div class="stat-number-modern success-stat">{{ $attendanceSummary['complete_sessions'] }}</div>
                        <div class="stat-label-modern">Complete Sessions</div>
                    </div>
                    <div class="stat-item-modern">
                        <div class="stat-number-modern warning-stat">{{ number_format($attendanceSummary['avg_hours_per_day'], 1) }}</div>
                        <div class="stat-label-modern">Avg Hours/Day</div>
                    </div>
                    <div class="stat-item-modern">
                        <div class="stat-number-modern info-stat">{{ number_format($attendanceSummary['total_hours'], 0) }}</div>
                        <div class="stat-label-modern">Total Hours (Year)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Working Hours Summary -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h3 class="card-title-modern">Working Hours Summary</h3>
                    <p class="card-subtitle-modern">Actual working hours from timesheets</p>
                </div>
                <div class="card-body-modern">
                    <div class="hours-grid-modern">
                        <div class="hours-card-modern today-hours">
                            <div class="hours-icon-modern">
                                <i class="bi bi-calendar-day"></i>
                            </div>
                            <div class="hours-content-modern">
                                <div class="hours-number-modern">{{ number_format($attendanceSummary['days_hours'], 0) }}</div>
                                <div class="hours-label-modern">Today's Hours</div>
                            </div>
                        </div>
                        
                        <div class="hours-card-modern week-hours">
                            <div class="hours-icon-modern">
                                <i class="bi bi-calendar-week"></i>
                            </div>
                            <div class="hours-content-modern">
                                <div class="hours-number-modern">{{ number_format($attendanceSummary['weeks_hours'], 0) }}</div>
                                <div class="hours-label-modern">This Week's Hours</div>
                            </div>
                        </div>
                        
                        <div class="hours-card-modern month-hours">
                            <div class="hours-icon-modern">
                                <i class="bi bi-calendar-month"></i>
                            </div>
                            <div class="hours-content-modern">
                                <div class="hours-number-modern">{{ number_format($attendanceSummary['months_hours'], 0) }}</div>
                                <div class="hours-label-modern">This Month's Hours</div>
                            </div>
                        </div>
                        
                        <div class="hours-card-modern year-hours">
                            <div class="hours-icon-modern">
                                <i class="bi bi-calendar3"></i>
                            </div>
                            <div class="hours-content-modern">
                                <div class="hours-number-modern">{{ number_format($attendanceSummary['years_hours'], 0) }}</div>
                                <div class="hours-label-modern">This Year's Hours</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection