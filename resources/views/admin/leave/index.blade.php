@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">🌴 Leave Requests</h2>
        <div>
            <a href="#" class="btn btn-primary" onclick="showComingSoon('Bulk Actions')"><i class="bi bi-hammer"></i> Bulk Actions</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <input type="text" id="leaveUserId" class="form-control" placeholder="User ID (optional)">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select id="leaveStatus" class="form-select">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary" onclick="loadLeaves()"><i class="bi bi-funnel"></i> Apply Filters</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Dates</th>
                            <th>Type</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="leaveTableBody">
                        <tr><td colspan="6" class="text-center py-4">Loading...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">Showing <span id="leaveCount">0</span> requests</small>
                <ul class="pagination pagination-sm mb-0" id="leavePagination"></ul>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
let leavePage = 1;

function loadLeaves(page = 1) {
    leavePage = page;
    const status = document.getElementById('leaveStatus').value;
    const userId = document.getElementById('leaveUserId').value;
    const params = new URLSearchParams({ page, status, user_id: userId });

    const tbody = document.getElementById('leaveTableBody');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

    fetch('{{ route('admin.leave.list') }}?' + params.toString(), { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error('Failed');
            if (!data.leaves || data.leaves.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No leave requests found</td></tr>';
                document.getElementById('leaveCount').textContent = '0';
                document.getElementById('leavePagination').innerHTML = '';
                return;
            }

            tbody.innerHTML = data.leaves.map(l => `
                <tr>
                    <td class="fw-medium">${l.user_name ?? 'User #'+l.user_id}<br><small class="text-muted">${l.user_email ?? ''}</small></td>
                    <td>${l.start_date ?? '-'} → ${l.end_date ?? '-'}</td>
                    <td>${l.type ?? 'N/A'}</td>
                    <td class="text-truncate" style="max-width:240px" title="${l.reason ?? ''}">${l.reason ?? ''}</td>
                    <td>${renderLeaveStatus(l.status)}</td>
                    <td class="text-center">
                        ${l.status === 'pending' ? `
                        <div class="d-inline-flex gap-2">
                            <button class="btn btn-sm btn-success" onclick="approveLeave(${l.id})"><i class="bi bi-check"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="rejectLeave(${l.id})"><i class="bi bi-x"></i></button>
                        </div>` : '<span class="text-muted">—</span>'}
                    </td>
                </tr>
            `).join('');

            document.getElementById('leaveCount').textContent = data.total ?? data.leaves.length;
            renderLeavePagination(data.current_page || 1, data.total_pages || 1);
        })
        .catch(() => {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Failed to load data</td></tr>';
        });
}

function renderLeaveStatus(status) {
    switch (status) {
        case 'approved': return '<span class="badge bg-success">Approved</span>';
        case 'rejected': return '<span class="badge bg-danger">Rejected</span>';
        default: return '<span class="badge bg-warning text-dark">Pending</span>';
    }
}

function renderLeavePagination(current, total) {
    const el = document.getElementById('leavePagination');
    el.innerHTML = '';
    if (total <= 1) return;
    if (current > 1) el.innerHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadLeaves(${current-1})">Prev</a></li>`;
    for (let i=1;i<=total;i++) el.innerHTML += `<li class="page-item ${i===current?'active':''}"><a class="page-link" href="#" onclick="loadLeaves(${i})">${i}</a></li>`;
    if (current < total) el.innerHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadLeaves(${current+1})">Next</a></li>`;
}

function approveLeave(id) {
    fetch(`{{ url('/admin/leave') }}/${id}/approve`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } })
        .then(r => r.json()).then(() => loadLeaves(leavePage));
}

function rejectLeave(id) {
    const reason = prompt('Reason (optional)');
    fetch(`{{ url('/admin/leave') }}/${id}/reject`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type':'application/json' }, body: JSON.stringify({ reason }) })
        .then(r => r.json()).then(() => loadLeaves(leavePage));
}

document.addEventListener('DOMContentLoaded', () => loadLeaves());
</script>
@endpush

@endsection
