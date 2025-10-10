@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Admin Header -->
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-title-section">
                <h1 class="admin-title">Timesheet Management</h1>
                <p class="admin-subtitle">Review and manage all employee timesheet entries</p>
            </div>
            <div class="admin-actions">
                <a href="{{ route('admin.dashboard') }}" class="btn-admin-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Modern Filters Section -->
    <div class="admin-filters-section">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-funnel"></i>
                    Filters & Search
                </h3>
            </div>
            <div class="admin-card-body">
                <form method="GET" action="{{ route('timesheet.admin.index') }}">
                    <div class="admin-filters-grid">
                        <div class="admin-filter-group">
                            <label class="admin-form-label">User ID</label>
                            <input type="number" name="user_id" value="{{ request('user_id') }}" class="admin-form-input" placeholder="Enter User ID">
                        </div>
                        <div class="admin-filter-group">
                            <label class="admin-form-label">Department</label>
                            <select name="department" class="admin-form-select">
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
                        <div class="admin-filter-group">
                            <label class="admin-form-label">Task</label>
                            <input type="text" name="task" value="{{ request('task') }}" class="admin-form-input" placeholder="Search task">
                        </div>
                        <div class="admin-filter-group">
                            <label class="admin-form-label">Start Date</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="admin-form-input">
                        </div>
                        <div class="admin-filter-group">
                            <label class="admin-form-label">End Date</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="admin-form-input">
                        </div>
                        <div class="admin-filter-group">
                            <label class="admin-form-label">&nbsp;</label>
                            <button class="btn-admin-filter" type="submit">
                                <i class="bi bi-funnel"></i>
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Timesheet Entries Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="bi bi-table"></i>
                Timesheet Entries
            </h3>
            <div class="admin-actions">
                <a href="{{ route('timesheet.admin.index', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn-admin-primary">
                    <i class="bi bi-download"></i>
                    Export CSV
                </a>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="admin-table-container">
                <table class="admin-table">
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
                                    <div class="admin-employee-info">
                                        @php
                                            $avatarColors = ['admin-avatar-blue', 'admin-avatar-green', 'admin-avatar-orange', 'admin-avatar-pink', 'admin-avatar-indigo'];
                                            $colorIndex = $e->user_id % count($avatarColors);
                                            $avatarClass = $avatarColors[$colorIndex];
                                        @endphp
                                        <div class="admin-employee-avatar {{ $avatarClass }}">
                                            {{ $e->user?->name ? substr($e->user->name, 0, 2) : 'U' }}
                                        </div>
                                        <div class="admin-employee-details">
                                            <h6 class="admin-employee-name">{{ $e->user?->name ?? 'Unknown User' }}</h6>
                                            <small class="admin-employee-id">ID: {{ $e->user_id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="admin-date-info">
                                        <div class="admin-date-main">{{ \Carbon\Carbon::parse($e->date)->format('M d, Y') }}</div>
                                        <div class="admin-date-sub">{{ \Carbon\Carbon::parse($e->date)->format('l') }}</div>
                                    </div>
                                </td>
                                <td class="admin-task-text">{{ $e->task }}</td>
                                <td class="text-end">
                                    <span class="admin-badge admin-badge-success">{{ $e->hours }} hrs</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="admin-empty-state">
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
            <div class="admin-pagination">
                <div class="admin-pagination-info">
                    Showing {{ $entries->firstItem() ?? 0 }} to {{ $entries->lastItem() ?? 0 }} of {{ $entries->total() }} entries
                </div>
                <div class="admin-per-page-selector">
                    <label class="admin-form-label">Per page:</label>
                    <select class="admin-form-select admin-form-select-sm" onchange="changePerPage(this.value)">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                @if($entries->hasPages())
                <nav aria-label="Timesheet entries pagination">
                    {{ $entries->appends(request()->query())->links('pagination::bootstrap-4') }}
                </nav>
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


