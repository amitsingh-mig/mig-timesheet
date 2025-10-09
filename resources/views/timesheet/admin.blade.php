@extends('layouts.app')

@push('styles')
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Timesheet Management</h1>
                    <p class="page-subtitle">Review and manage all employee timesheet entries</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">
                        <i class="bi bi-arrow-left"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Filters Section -->
        <div class="filter-section">
            <h3 class="filter-title">
                <i class="bi bi-funnel"></i>Filters
            </h3>
            <form method="GET" action="{{ route('timesheet.admin.index') }}">
                <div class="row g-3">
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label">User ID</label>
                        <input type="number" name="user_id" value="{{ request('user_id') }}" class="form-control" placeholder="Enter User ID">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            <option value="Web" {{ request('department') == 'Web' ? 'selected' : '' }}>Web Development</option>
                            <option value="Graphic" {{ request('department') == 'Graphic' ? 'selected' : '' }}>Graphic Design</option>
                            <option value="Editorial" {{ request('department') == 'Editorial' ? 'selected' : '' }}>Editorial</option>
                            <option value="Multimedia" {{ request('department') == 'Multimedia' ? 'selected' : '' }}>Multimedia</option>
                            <option value="Sales" {{ request('department') == 'Sales' ? 'selected' : '' }}>Sales</option>
                            <option value="Marketing" {{ request('department') == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                            <option value="Intern" {{ request('department') == 'Intern' ? 'selected' : '' }}>Internship</option>
                            <option value="General" {{ request('department') == 'General' ? 'selected' : '' }}>General</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label">Task</label>
                        <input type="text" name="task" value="{{ request('task') }}" class="form-control" placeholder="Search task">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-funnel"></i>Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="data-table">
            <div class="table-header">
                <h3 class="table-title">Timesheet Entries</h3>
                <div>
                    <a href="{{ route('timesheet.admin.index', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-export">
                        <i class="bi bi-download"></i>Export CSV
                    </a>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Task</th>
                            <th class="text-end">Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $e)
                            <tr>
                                <td>
                                    <div class="employee-info">
                                        @php
                                            $avatarColors = ['avatar-blue', 'avatar-green', 'avatar-orange', 'avatar-pink', 'avatar-indigo'];
                                            $colorIndex = $e->user_id % count($avatarColors);
                                            $avatarClass = $avatarColors[$colorIndex];
                                        @endphp
                                        <div class="employee-avatar {{ $avatarClass }}">
                                            {{ $e->user?->name ? substr($e->user->name, 0, 2) : 'U' }}
                                        </div>
                                        <div class="employee-details">
                                            <h6>{{ $e->user?->name ?? 'Unknown User' }}</h6>
                                            <small>ID: {{ $e->user_id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="date-info">
                                        <div class="date-main">{{ \Carbon\Carbon::parse($e->date)->format('M d, Y') }}</div>
                                        <div class="date-sub">{{ \Carbon\Carbon::parse($e->date)->format('l') }}</div>
                                    </div>
                                </td>
                                <td>{{ $e->task }}</td>
                                <td class="hours-display">{{ $e->hours }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <h5>No timesheet entries found</h5>
                                        <p>Try adjusting your filters to see more results.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($entries->hasPages() || $entries->total() > 0)
            <div class="pagination-container">
                <div class="d-flex align-items-center gap-3">
                    <div class="pagination-info">
                        Showing {{ $entries->firstItem() ?? 0 }} to {{ $entries->lastItem() ?? 0 }} of {{ $entries->total() }} entries
                    </div>
                    <div class="per-page-selector">
                        <label class="form-label mb-0 me-2" style="font-size: 0.8rem;">Per page:</label>
                        <select class="form-select form-select-sm" style="width: auto; font-size: 0.8rem;" onchange="changePerPage(this.value)">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>
                @if($entries->hasPages())
                <div class="pagination-wrapper">
                    {{ $entries->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function changePerPage(value) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', value);
    url.searchParams.delete('page'); // Reset to page 1 when changing per page
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    // Remove any duplicate pagination elements
    const paginationContainers = document.querySelectorAll('.pagination-container');
    paginationContainers.forEach((container, index) => {
        if (index > 0) {
            container.remove();
        }
    });
    
    // Fix any oversized pagination icons
    const paginationIcons = document.querySelectorAll('.pagination .page-link i, .pagination .page-link svg');
    paginationIcons.forEach(icon => {
        icon.style.fontSize = '14px';
        icon.style.width = '14px';
        icon.style.height = '14px';
        icon.style.lineHeight = '1';
    });
    
    // Ensure proper pagination styling
    const paginationLinks = document.querySelectorAll('.pagination .page-link');
    paginationLinks.forEach(link => {
        link.style.minWidth = '36px';
        link.style.height = '36px';
        link.style.padding = '6px 12px';
        link.style.fontSize = '14px';
        link.style.borderRadius = '6px';
    });
});
</script>
@endpush


