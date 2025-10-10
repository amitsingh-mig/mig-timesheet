@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Admin Users Header -->
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-title-section">
                <h1 class="admin-title">User Management</h1>
                <p class="admin-subtitle">Manage users, roles, and access permissions</p>
            </div>
            <div class="admin-actions">
                <button class="btn-admin-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                    <i class="bi bi-person-plus"></i>
                    Create New User
                </button>
                <a href="{{ route('admin.employees.time.view') }}" class="btn-admin-secondary">
                    <i class="bi bi-clock-history"></i>
                    Time Overview
                </a>
            </div>
        </div>
    </div>

    <!-- Modern Filters Section -->
    <div class="admin-filters-section">
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title">
                    <i class="bi bi-funnel"></i>
                    Filters & Search
                </div>
            </div>
            <div class="admin-card-body">
                <div class="admin-filters-grid">
                    <div class="admin-filter-group">
                        <label class="admin-form-label">Search</label>
                        <div class="admin-input-group">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchQuery" class="admin-form-input" placeholder="Search by name or email">
                        </div>
                    </div>
                    
                    <div class="admin-filter-group">
                        <label class="admin-form-label">Role</label>
                        <select id="roleFilter" class="admin-form-select">
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <label class="admin-form-label">Department</label>
                        <select id="departmentFilter" class="admin-form-select">
                            <option value="">All Departments</option>
                            <option value="Admin">Admin</option>
                            <option value="Web">Web Development</option>
                            <option value="Graphic">Graphic Design</option>
                            <option value="Editorial">Editorial</option>
                            <option value="Multimedia">Multimedia</option>
                            <option value="Sales">Sales</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Intern">Internship</option>
                            <option value="General">General</option>
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <label class="admin-form-label">Status</label>
                        <select id="statusFilter" class="admin-form-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <label class="admin-form-label">&nbsp;</label>
                        <button class="btn-admin-filter" onclick="loadUsers()">
                            <i class="bi bi-funnel"></i>
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Users Table -->
    <div class="admin-table-section">
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title">
                    <i class="bi bi-people"></i>
                    Users List
                </div>
                <div class="admin-table-count">
                    <span class="admin-count-badge">
                        <span id="userCount">0</span> USERS
                    </span>
                </div>
            </div>
            <div class="admin-card-body">
                <div class="admin-table-container">
                    <table class="admin-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <!-- Populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <div class="admin-pagination">
                    <div class="admin-pagination-info">
                        Showing <span id="userCountFooter">0</span> users
                    </div>
                    <nav aria-label="User pagination">
                        <ul class="admin-pagination-list" id="usersPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

<!-- Role Distribution Chart -->
{{-- <div class="card border-0 shadow-sm mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Role Distribution</h5>
    </div>
    <div class="card-body">
        <canvas id="roleDistributionChart" height="100"></canvas>
    </div>
</div> --}}

    <!-- Modern Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content admin-modal">
                <div class="admin-modal-header">
                    <div class="admin-modal-title">
                        <i class="bi bi-person-plus"></i>
                        Create New User
                    </div>
                    <button type="button" class="admin-modal-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="admin-modal-body">
                    <form id="createUserForm">
                        <div class="admin-form-grid">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Name <span class="required">*</span></label>
                                <input type="text" class="admin-form-input" id="name" required>
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Email <span class="required">*</span></label>
                                <input type="email" class="admin-form-input" id="email" required>
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Role <span class="required">*</span></label>
                                <select class="admin-form-select" id="role" required>
                                    <option value="employee">Employee</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Department</label>
                                <select class="admin-form-select" id="department">
                                    <option value="">Select Department</option>
                                    <option value="Web">Web Development</option>
                                    <option value="Graphic">Graphic Design</option>
                                    <option value="Editorial">Editorial</option>
                                    <option value="Multimedia">Multimedia</option>
                                    <option value="Sales">Sales</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Intern">Internship</option>
                                    <option value="General">General</option>
                                </select>
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Password <span class="required">*</span></label>
                                <input type="password" class="admin-form-input" id="password" required>
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Confirm Password <span class="required">*</span></label>
                                <input type="password" class="admin-form-input" id="password_confirmation" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="btn-admin-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-admin-submit" onclick="createUser()">
                        <i class="bi bi-check2-circle"></i>
                        Create User
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content admin-modal">
                <div class="admin-modal-header" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <div class="admin-modal-title">
                        <i class="bi bi-person-gear"></i>
                        Edit User
                    </div>
                    <button type="button" class="admin-modal-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="admin-modal-body">
                    <form id="editUserForm">
                        <input type="hidden" id="editUserId">
                        <div class="admin-form-grid">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Name <span class="required">*</span></label>
                                <input type="text" class="admin-form-input" id="editName" required>
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Email <span class="required">*</span></label>
                                <input type="email" class="admin-form-input" id="editEmail" required>
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Role <span class="required">*</span></label>
                                <select class="admin-form-select" id="editRole" required>
                                    <option value="employee">Employee</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Department</label>
                                <select class="admin-form-select" id="editDepartment">
                                    <option value="">Select Department</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Web">Web Development</option>
                                    <option value="Graphic">Graphic Design</option>
                                    <option value="Editorial">Editorial</option>
                                    <option value="Multimedia">Multimedia</option>
                                    <option value="Sales">Sales</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Intern">Internship</option>
                                    <option value="General">General</option>
                                </select>
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Status</label>
                                <select class="admin-form-select" id="editStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            
                            <div class="admin-form-group admin-checkbox-group">
                                <div class="admin-checkbox">
                                    <input type="checkbox" id="editChangePassword" class="admin-checkbox-input">
                                    <label for="editChangePassword" class="admin-checkbox-label">
                                        Change Password
                                    </label>
                                </div>
                            </div>
                            
                            <div class="admin-form-group" id="editPasswordField" style="display: none;">
                                <label class="admin-form-label">New Password</label>
                                <input type="password" class="admin-form-input" id="editPassword">
                            </div>
                            
                            <div class="admin-form-group" id="editPasswordConfirmField" style="display: none;">
                                <label class="admin-form-label">Confirm New Password</label>
                                <input type="password" class="admin-form-input" id="editPasswordConfirmation">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="btn-admin-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-admin-submit" onclick="updateUser()" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="bi bi-check2-circle"></i>
                        Update User
                    </button>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
let currentUserPage = 1;

// Handle password change toggle
document.addEventListener('DOMContentLoaded', function() {
    const changePasswordCheckbox = document.getElementById('editChangePassword');
    const passwordField = document.getElementById('editPasswordField');
    const passwordConfirmField = document.getElementById('editPasswordConfirmField');
    
    if (changePasswordCheckbox) {
        changePasswordCheckbox.addEventListener('change', function() {
            if (this.checked) {
                passwordField.style.display = 'block';
                passwordConfirmField.style.display = 'block';
                document.getElementById('editPassword').required = true;
                document.getElementById('editPasswordConfirmation').required = true;
            } else {
                passwordField.style.display = 'none';
                passwordConfirmField.style.display = 'none';
                document.getElementById('editPassword').required = false;
                document.getElementById('editPasswordConfirmation').required = false;
                document.getElementById('editPassword').value = '';
                document.getElementById('editPasswordConfirmation').value = '';
            }
        });
    }
});

// Load users
function loadUsers() {
    console.log('Loading users data...');
    const q = document.getElementById('searchQuery').value;
    const role = document.getElementById('roleFilter').value;
    const department = document.getElementById('departmentFilter').value;
    const status = document.getElementById('statusFilter').value;

    // Show loading state
    const tbody = document.getElementById('usersTableBody');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5"><div class="loading-spinner"></div><div class="mt-2 text-muted">Loading users...</div></td></tr>';

    fetch(`/admin/users/data?q=${encodeURIComponent(q)}&role=${role}&department=${department}&status=${status}&page=${currentUserPage}`, {
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
                    <td class="fw-medium">
                        <a href="/profile/${u.id}" class="text-decoration-none text-primary fw-medium profile-link" title="View Profile">
                            <i class="bi bi-person-circle me-1"></i>${u.name}
                        </a>
                    </td>
                    <td>${u.email}</td>
                    <td><span class="badge-modern ${u.role === 'admin' ? 'badge-admin' : 'badge-employee'}">${u.role.toUpperCase()}</span></td>
                    <td><span class="badge-modern badge-department">${(u.department || 'General').toUpperCase()}</span></td>
                    <td><span class="badge-modern ${u.status === 'active' ? 'badge-active' : 'badge-inactive'}">${u.status.toUpperCase()}</span></td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-2 flex-wrap justify-content-center align-items-center">
                            <button class="btn btn-table-action btn-edit" onclick="editUser(${u.id})" title="Edit User"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-table-action btn-reset" onclick="resetPassword(${u.id})" title="Reset Password"><i class="bi bi-key"></i></button>
                            <button class="btn btn-table-action btn-delete" onclick="deleteUser(${u.id})" title="Delete User"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>`;
                tbody.innerHTML += row;
            });
            
            const userCount = data.total || data.users.length;
            document.getElementById('userCount').textContent = userCount;
            document.getElementById('userCountFooter').textContent = userCount;
            renderUserPagination(data.current_page || 1, data.total_pages || 1);
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="empty-state-modern"><i class="bi bi-people"></i><h5>No users found</h5><p>Try adjusting your search criteria or create a new user.</p></td></tr>';
            document.getElementById('userCount').textContent = '0';
            document.getElementById('userCountFooter').textContent = '0';
        }
    })
        .catch(() => {
            // Demo data fallback
            const demo = [
                {id:1,name:'Alice Johnson',email:'alice@example.com',role:'admin',department:'Admin',status:'active'},
                {id:2,name:'Bob Smith',email:'bob@example.com',role:'employee',department:'Web',status:'active'},
                {id:3,name:'Carol Lee',email:'carol@example.com',role:'employee',department:'Intern',status:'inactive'}
            ];
            const tbody = document.getElementById('usersTableBody');
            tbody.innerHTML = '';
            demo.forEach(u => {
                const row = `<tr>
                    <td class="fw-medium">
                        <a href="/profile/${u.id}" class="text-decoration-none text-primary fw-medium profile-link" title="View Profile">
                            <i class="bi bi-person-circle me-1"></i>${u.name}
                        </a>
                    </td>
                    <td>${u.email}</td>
                    <td><span class="badge-modern ${u.role === 'admin' ? 'badge-admin' : 'badge-employee'}">${u.role}</span></td>
                    <td><span class="badge-modern badge-department">${u.department || 'General'}</span></td>
                    <td><span class="badge-modern ${u.status === 'active' ? 'badge-active' : 'badge-inactive'}">${u.status}</span></td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-2 flex-wrap justify-content-center align-items-center">
                            <button class="btn btn-table-action btn-edit" onclick="editUser(${u.id})" title="Edit User"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-table-action btn-reset" onclick="resetPassword(${u.id})" title="Reset Password"><i class="bi bi-key"></i></button>
                            <button class="btn btn-table-action btn-delete" onclick="deleteUser(${u.id})" title="Delete User"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>`;
                tbody.innerHTML += row;
            });
            document.getElementById('userCount').textContent = demo.length;
            document.getElementById('userCountFooter').textContent = demo.length;
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
        department: document.getElementById('department').value,
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
    // Find the user data from the current table
    const tbody = document.getElementById('usersTableBody');
    const rows = tbody.querySelectorAll('tr');
    let userData = null;
    
    // Try to find user data from the table
    for (let row of rows) {
        const editButton = row.querySelector('button[onclick*="editUser"]');
        if (editButton && editButton.getAttribute('onclick').includes(id)) {
            const cells = row.querySelectorAll('td');
            userData = {
                id: id,
                name: cells[0].textContent.trim(),
                email: cells[1].textContent.trim(),
                role: cells[2].querySelector('.badge').textContent.trim(),
                department: cells[3].querySelector('.badge').textContent.trim(),
                status: cells[4].querySelector('.badge').textContent.trim().toLowerCase()
            };
            break;
        }
    }
    
    // Debug: Log the extracted user data
    console.log('Extracted user data:', userData);
    
    if (userData) {
        // Populate the edit form
        document.getElementById('editUserId').value = userData.id;
        document.getElementById('editName').value = userData.name;
        document.getElementById('editEmail').value = userData.email;
        document.getElementById('editRole').value = userData.role;
        document.getElementById('editDepartment').value = userData.department;
        document.getElementById('editStatus').value = userData.status;
        
        // Reset password fields
        document.getElementById('editChangePassword').checked = false;
        document.getElementById('editPassword').value = '';
        document.getElementById('editPasswordConfirmation').value = '';
        document.getElementById('editPasswordField').style.display = 'none';
        document.getElementById('editPasswordConfirmField').style.display = 'none';
        
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
        modal.show();
    } else {
        // Fallback: Try to get user data from server
        fetch(`/admin/users/data?user_id=${id}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.users && data.users.length > 0) {
                const user = data.users[0];
                // Populate the edit form
                document.getElementById('editUserId').value = user.id;
                document.getElementById('editName').value = user.name;
                document.getElementById('editEmail').value = user.email;
                document.getElementById('editRole').value = user.role;
                document.getElementById('editDepartment').value = user.department || 'General';
                document.getElementById('editStatus').value = user.status;
                
                // Reset password fields
                document.getElementById('editChangePassword').checked = false;
                document.getElementById('editPassword').value = '';
                document.getElementById('editPasswordConfirmation').value = '';
                document.getElementById('editPasswordField').style.display = 'none';
                document.getElementById('editPasswordConfirmField').style.display = 'none';
                
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                modal.show();
            } else {
                showToast('User data not found', 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching user data:', error);
            showToast('Failed to load user data', 'error');
        });
    }
}

function updateUser() {
    const userId = document.getElementById('editUserId').value;
    const name = document.getElementById('editName').value;
    const email = document.getElementById('editEmail').value;
    const role = document.getElementById('editRole').value;
    const department = document.getElementById('editDepartment').value;
    const status = document.getElementById('editStatus').value;
    const changePassword = document.getElementById('editChangePassword').checked;
    const password = document.getElementById('editPassword').value;
    const passwordConfirmation = document.getElementById('editPasswordConfirmation').value;
    
    // Debug: Log all form values
    console.log('Form values:', {
        userId, name, email, role, department, status, changePassword, password, passwordConfirmation
    });
    
    // Basic validation
    if (!name || !email) {
        showToast('Please fill in all required fields', 'error');
        return;
    }
    
    if (changePassword) {
        if (!password || !passwordConfirmation) {
            showToast('Please fill in password fields', 'error');
            return;
        }
        if (password !== passwordConfirmation) {
            showToast('Passwords do not match', 'error');
            return;
        }
        if (password.length < 8) {
            showToast('Password must be at least 8 characters long', 'error');
            return;
        }
    }
    
    const payload = {
        name: name,
        email: email,
        role: role,
        department: department,
        status: status
    };
    
    if (changePassword) {
        payload.password = password;
        payload.password_confirmation = passwordConfirmation;
    }
    
    // Debug: Log the payload being sent
    console.log('Update user payload:', payload);
    console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.content);
    
    // Add _method field for Laravel method spoofing
    payload._method = 'PUT';
    
    // Send as FormData for better Laravel compatibility
    const formData = new FormData();
    Object.keys(payload).forEach(key => {
        if (payload[key] !== null && payload[key] !== undefined) {
            formData.append(key, payload[key]);
        }
    });
    
    fetch(`/admin/users/${userId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(JSON.stringify(errorData));
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
            if (modal) modal.hide();
            document.getElementById('editUserForm').reset();
            loadUsers();
            showToast('User updated successfully!', 'success');
        } else {
            let errorMessage = data.message || 'Failed to update user';
            if (data.errors) {
                const errorList = Object.values(data.errors).flat().join(', ');
                errorMessage += ': ' + errorList;
            }
            showToast(errorMessage, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating user:', error);
        try {
            const errorData = JSON.parse(error.message);
            let errorMessage = errorData.message || 'An error occurred while updating the user';
            if (errorData.errors) {
                const errorList = Object.values(errorData.errors).flat().join(', ');
                errorMessage += ': ' + errorList;
            }
            showToast(errorMessage, 'error');
        } catch (e) {
            showToast('An error occurred while updating the user', 'error');
        }
    });
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
            const labels = data.labels || ['Admin', 'User'];
            const values = data.data || [0, 0];
            window.roleChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data: values,
                        backgroundColor: ['#dc3545', '#0d6efd'], // Admin red, User blue
                        hoverOffset: 6,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: { callbacks: { label: function(ctx){
                            const total = ctx.dataset.data.reduce((a,b)=>a+b,0) || 0;
                            const val = ctx.parsed || 0;
                            const pct = total ? ((val/total)*100).toFixed(1) : 0;
                            return `${ctx.label}: ${val} (${pct}%)`;
                        }}}
                    }
                }
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
                        backgroundColor: ['#dc3545', '#0d6efd'],
                        hoverOffset: 6,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: { responsive: true, cutout: '60%', plugins: { legend: { position: 'bottom' } } }
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

<style>
.profile-link {
    transition: all 0.2s ease;
    border-radius: 4px;
    padding: 2px 4px;
}

.profile-link:hover {
    background-color: rgba(13, 110, 253, 0.1);
    transform: translateY(-1px);
    text-decoration: none !important;
}

.profile-link i {
    opacity: 0.7;
}

.profile-link:hover i {
    opacity: 1;
}
</style>
@endpush
</div>
@endsection

