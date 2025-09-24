@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">User Management</h1>
            <p class="text-muted mb-0">Manage users, roles, and access</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="bi bi-person-plus me-2"></i>Create New User
            </button>
            <a href="{{ route('admin.employees.time.view') }}" class="btn btn-outline-primary">
                <i class="bi bi-clock-history me-2"></i>Employee Time Overview
            </a>
        </div>
    </div>

<!-- Search and Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchQuery" class="form-control" placeholder="Search by name or email">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Role</label>
                <select id="roleFilter" class="form-select">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100" onclick="loadUsers()">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0 align-middle" id="usersTable">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <!-- Populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-light d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing <span id="userCount">0</span> users</small>
        <nav aria-label="User pagination">
            <ul class="pagination pagination-sm mb-0" id="usersPagination"></ul>
        </nav>
    </div>
</div>

<!-- Role Distribution Chart -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Role Distribution</h5>
    </div>
    <div class="card-body">
        <canvas id="roleDistributionChart" height="100"></canvas>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(90deg, #4e54c8, #8f94fb);">
                <h5 class="modal-title text-white"><i class="bi bi-person-plus me-2"></i>Create New User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createUserForm" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Role</label>
                        <select class="form-select" id="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirmation" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createUser()">
                    <i class="bi bi-check2-circle me-1"></i>Create User
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentUserPage = 1;

// Load users
function loadUsers() {
    console.log('Loading users data...');
    const q = document.getElementById('searchQuery').value;
    const role = document.getElementById('roleFilter').value;
    const status = document.getElementById('statusFilter').value;

    // Show loading state
    const tbody = document.getElementById('usersTableBody');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';

    fetch(`/admin/users/data?q=${encodeURIComponent(q)}&role=${role}&status=${status}&page=${currentUserPage}`, {
        method: 'GET', 
        headers: { 
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        console.log('Users response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Users data received:', data);
        
        tbody.innerHTML = '';
        
        if (data.success && data.users && data.users.length > 0) {
            data.users.forEach(u => {
                const row = `<tr>
                    <td class="fw-medium">${u.name}</td>
                    <td>${u.email}</td>
                    <td><span class="badge ${u.role === 'admin' ? 'bg-danger' : 'bg-secondary'}">${u.role}</span></td>
                    <td>${u.status === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-2 flex-wrap justify-content-center align-items-center">
                            <button class="btn btn-outline-primary" onclick="editUser(${u.id})"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-outline-warning" onclick="resetPassword(${u.id})"><i class="bi bi-key"></i></button>
                            <button class="btn btn-outline-danger" onclick="deleteUser(${u.id})"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>`;
                tbody.innerHTML += row;
            });
            
            document.getElementById('userCount').textContent = data.total || data.users.length;
            renderUserPagination(data.current_page || 1, data.total_pages || 1);
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No users found</td></tr>';
            document.getElementById('userCount').textContent = '0';
        }
    })
        .catch(() => {
            // Demo data fallback
            const demo = [
                {id:1,name:'Alice Johnson',email:'alice@example.com',role:'admin',status:'active'},
                {id:2,name:'Bob Smith',email:'bob@example.com',role:'user',status:'active'},
                {id:3,name:'Carol Lee',email:'carol@example.com',role:'user',status:'inactive'}
            ];
            const tbody = document.getElementById('usersTableBody');
            tbody.innerHTML = '';
            demo.forEach(u => {
                const row = `<tr>
                    <td class="fw-medium">${u.name}</td>
                    <td>${u.email}</td>
                    <td><span class="badge ${u.role === 'admin' ? 'bg-danger' : 'bg-secondary'}">${u.role}</span></td>
                    <td>${u.status === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-2 flex-wrap justify-content-center align-items-center">
                            <button class="btn btn-outline-primary"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-outline-warning"><i class="bi bi-key"></i></button>
                            <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>`;
                tbody.innerHTML += row;
            });
            document.getElementById('userCount').textContent = demo.length;
            renderUserPagination(1,1);
        });
}

function renderUserPagination(current, total) {
    const el = document.getElementById('usersPagination');
    el.innerHTML = '';
    if (total <= 1) return;
    if (current > 1) el.innerHTML += `<li class="page-item"><a class="page-link" href="#" onclick="goUserPage(${current-1})">Prev</a></li>`;
    for (let i = 1; i <= total; i++) {
        el.innerHTML += `<li class="page-item ${i===current?'active':''}"><a class="page-link" href="#" onclick="goUserPage(${i})">${i}</a></li>`;
    }
    if (current < total) el.innerHTML += `<li class="page-item"><a class="page-link" href="#" onclick="goUserPage(${current+1})">Next</a></li>`;
}

function goUserPage(page) { currentUserPage = page; loadUsers(); }

function createUser() {
    const payload = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        role: document.getElementById('role').value,
        password: document.getElementById('password').value,
        password_confirmation: document.getElementById('password_confirmation').value
    };
    
    // Basic validation
    if (!payload.name || !payload.email || !payload.password || !payload.password_confirmation) {
        alert('Please fill in all required fields');
        return;
    }
    
    if (payload.password !== payload.password_confirmation) {
        alert('Passwords do not match');
        return;
    }
    
    const formData = new FormData();
    Object.keys(payload).forEach(key => formData.append(key, payload[key]));
    
    fetch('/admin/users', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('createUserModal'));
            if (modal) modal.hide();
            document.getElementById('createUserForm').reset();
            loadUsers();
            showToast('User created successfully!', 'success');
        } else {
            showToast(data.message || 'Failed to create user', 'error');
        }
    })
    .catch(error => {
        console.error('Error creating user:', error);
        showToast('An error occurred while creating the user', 'error');
    });
}

function editUser(id) {
    // For now, just show a placeholder - could implement a full edit modal
    showToast(`Edit user ${id} - Feature coming soon`, 'info');
}

function resetPassword(id) {
    const newPassword = prompt('Enter new password (min 8 characters):');
    if (!newPassword || newPassword.length < 8) {
        alert('Password must be at least 8 characters long');
        return;
    }
    
    const confirmPassword = prompt('Confirm new password:');
    if (newPassword !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    const formData = new FormData();
    formData.append('password', newPassword);
    formData.append('password_confirmation', confirmPassword);
    
    fetch(`/admin/users/${id}/reset-password`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Password reset successfully!', 'success');
        } else {
            showToast(data.message || 'Failed to reset password', 'error');
        }
    })
    .catch(error => {
        console.error('Error resetting password:', error);
        showToast('An error occurred while resetting password', 'error');
    });
}

function deleteUser(id) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        return;
    }
    
    fetch(`/admin/users/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadUsers();
            showToast('User deleted successfully!', 'success');
        } else {
            showToast(data.message || 'Failed to delete user', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting user:', error);
        showToast('An error occurred while deleting user', 'error');
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    new bootstrap.Toast(toast).show();
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}

function loadRoleDistribution() {
    fetch('/admin/users/role-distribution')
        .then(r => r.json())
        .then(data => {
            const ctx = document.getElementById('roleDistributionChart');
            if (!ctx) return;
            if (window.roleChart) window.roleChart.destroy();
            window.roleChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels || ['Admin', 'User'],
                    datasets: [{
                        data: data.data || [0, 0],
                        backgroundColor: ['#4e54c8', '#8f94fb']
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        })
        .catch(() => {
            // Demo data fallback
            const ctx = document.getElementById('roleDistributionChart');
            if (!ctx) return;
            if (window.roleChart) window.roleChart.destroy();
            window.roleChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Admin', 'User'],
                    datasets: [{
                        data: [1, 2],
                        backgroundColor: ['#4e54c8', '#8f94fb']
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        });
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadUsers();
    loadRoleDistribution();
    // Auto-open Create modal when navigated from Quick Action
    try {
        const params = new URLSearchParams(window.location.search);
        if (params.get('open') === 'create') {
            const m = new bootstrap.Modal(document.getElementById('createUserModal'));
            m.show();
        }
    } catch (e) {}
});
</script>
@endpush
</div>
@endsection

