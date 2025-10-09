@extends('layouts.app')

@section('content')
<!-- Modern Profile Page Header -->
<div class="container-fluid px-0">
    <div class="profile-header bg-gradient-primary text-white py-5 mb-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="profile-avatar me-4">
                            <img src="{{ $user->getProfilePhotoUrl() }}" 
                                 alt="{{ $user->name }}" 
                                 class="avatar-circle bg-white text-primary d-flex align-items-center justify-content-center"
                                 style="object-fit: cover; width: 80px; height: 80px; border-radius: 50%;"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="avatar-fallback" style="display: none; width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold;">
                                {{ substr($user->name, 0, 2) }}
                            </div>
                        </div>
                        <div>
                            <h1 class="h3 mb-1 fw-bold">{{ $user->name }}</h1>
                            <p class="mb-2 opacity-75">{{ $user->email }}</p>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-light text-dark me-2">
                                    <i class="bi bi-shield-check me-1"></i>{{ $user->getDisplayRole() }}
                                </span>
                                <span class="badge bg-light text-dark me-2">
                                    <i class="bi bi-building me-1"></i>{{ $user->getDepartment() }}
                                </span>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-calendar me-1"></i>Member since {{ $user->created_at->format('M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-number">{{ $attendanceSummary['total_attendance_days'] }}</div>
                            <div class="stat-label">Attendance Days</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row g-4">
        <!-- Profile Information Card -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-primary text-white me-3">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Employee Information</h5>
                            <p class="text-muted mb-0 small">Complete employee profile details</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-item d-flex align-items-center">
                                <div class="info-icon bg-primary text-white rounded-circle me-3">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">Full Name</div>
                                    <div class="text-muted">{{ $user->name }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-item d-flex align-items-center">
                                <div class="info-icon bg-info text-white rounded-circle me-3">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">Email Address</div>
                                    <div class="text-muted">{{ $user->email }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-item d-flex align-items-center">
                                <div class="info-icon bg-warning text-white rounded-circle me-3">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">Role</div>
                                    <div class="text-muted">
                                        <span class="badge {{ $user->role && $user->role->name === 'admin' ? 'bg-danger' : 'bg-secondary' }}">
                                            {{ $user->role ? strtoupper($user->role->name) : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-item d-flex align-items-center">
                                <div class="info-icon bg-success text-white rounded-circle me-3">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">Department</div>
                                    <div class="text-muted">
                                        <span class="badge bg-info">{{ strtoupper($user->getDepartment()) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-item d-flex align-items-center">
                                <div class="info-icon bg-{{ $user->email_verified_at ? 'success' : 'warning' }} text-white rounded-circle me-3">
                                    <i class="bi bi-{{ $user->email_verified_at ? 'check-circle' : 'exclamation-triangle' }}"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">Account Status</div>
                                    <div class="text-muted">
                                        <span class="badge {{ $user->email_verified_at ? 'bg-success' : 'bg-warning' }}">
                                            {{ $user->email_verified_at ? 'ACTIVE' : 'INACTIVE' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-item d-flex align-items-center">
                                <div class="info-icon bg-secondary text-white rounded-circle me-3">
                                    <i class="bi bi-calendar-plus"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">Date of Joining</div>
                                    <div class="text-muted">{{ $user->created_at ? $user->created_at->format('F j, Y') : 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profile Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-graph-up me-2 text-info"></i>Quick Stats
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="stat-number text-primary fw-bold">{{ $attendanceSummary['total_attendance_days'] }}</div>
                                <div class="stat-label text-muted small">Attendance Days</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="stat-number text-success fw-bold">{{ $attendanceSummary['complete_sessions'] }}</div>
                                <div class="stat-label text-muted small">Complete Sessions</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="stat-number text-warning fw-bold">{{ number_format($attendanceSummary['avg_hours_per_day'], 2) }}</div>
                                <div class="stat-label text-muted small">Avg Hours/Day</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="stat-number text-info fw-bold">{{ number_format($attendanceSummary['total_hours'], 0) }}</div>
                                <div class="stat-label text-muted small">Total Hours (Year)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Summary Section -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-success text-white me-3">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Attendance & Working Hours Summary</h5>
                            <p class="text-muted mb-0 small">Comprehensive working hours analysis</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Working Hours Projections -->
                        <div class="col-12">
                            <h6 class="fw-bold mb-3 text-muted">Actual Working Hours (From Timesheets)</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="stat-card bg-primary text-white">
                                        <div class="stat-icon">
                                            <i class="bi bi-calendar-day"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-number">{{ number_format($attendanceSummary['days_hours'], 0) }}</div>
                                            <div class="stat-label">Today's Hours</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stat-card bg-success text-white">
                                        <div class="stat-icon">
                                            <i class="bi bi-calendar-week"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-number">{{ number_format($attendanceSummary['weeks_hours'], 0) }}</div>
                                            <div class="stat-label">This Week's Hours</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stat-card bg-warning text-white">
                                        <div class="stat-icon">
                                            <i class="bi bi-calendar-month"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-number">{{ number_format($attendanceSummary['months_hours'], 0) }}</div>
                                            <div class="stat-label">This Month's Hours</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stat-card bg-danger text-white">
                                        <div class="stat-icon">
                                            <i class="bi bi-calendar-year"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-number">{{ number_format($attendanceSummary['years_hours'], 0) }}</div>
                                            <div class="stat-label">This Year's Hours</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    font-size: 2rem;
}

.profile-photo {
    width: 120px;
    height: 120px;
}

.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.info-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #667eea;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
}

.stat-box {
    padding: 1rem;
    border-radius: 0.5rem;
    background: #f8f9fa;
}

.profile-stats .stat-item {
    text-align: right;
}

.profile-stats .stat-number {
    font-size: 1.5rem;
    color: white;
}

.profile-stats .stat-label {
    color: rgba(255, 255, 255, 0.8);
}

.stat-card {
    border-radius: 0.75rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-card .stat-icon {
    font-size: 2rem;
    margin-right: 1rem;
    opacity: 0.8;
}

.stat-card .stat-content {
    flex: 1;
}

.stat-card .stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0.25rem;
}

.stat-card .stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
}

@media (max-width: 768px) {
    .profile-header .row {
        text-align: center;
    }
    
    .profile-stats {
        margin-top: 1rem;
    }
    
    .profile-stats .stat-item {
        text-align: center;
    }
    
    .stat-card {
        margin-bottom: 1rem;
    }
}
</style>
@endsection
