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
                                 style="object-fit: cover; width: 80px; height: 80px; border-radius: 50%;">
                        </div>
                        <div>
                            <h1 class="h3 mb-1 fw-bold">{{ $user->name }}</h1>
                            <p class="mb-2 opacity-75">{{ $user->email }}</p>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-light text-dark me-2">
                                    <i class="fas fa-shield-alt me-1"></i>{{ $user->getDisplayRole() }}
                                </span>
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-calendar me-1"></i>Member since {{ $user->created_at->format('M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-number">{{ $user->timesheets()->count() }}</div>
                            <div class="stat-label">Timesheets</div>
                        </div>
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
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Profile Information</h5>
                            <p class="text-muted mb-0 small">Update your basic profile information</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="post" action="{{ route('profile.update') }}" class="needs-validation" novalidate>
                        @csrf
                        @method('patch')
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $user->name) }}" 
                                           placeholder="Full Name"
                                           required>
                                    <label for="name">
                                        <i class="fas fa-user me-2 text-muted"></i>Full Name
                                    </label>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $user->email) }}" 
                                           placeholder="Email Address"
                                           required>
                                    <label for="email">
                                        <i class="fas fa-envelope me-2 text-muted"></i>Email Address
                                    </label>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Profile Sidebar -->
        <div class="col-lg-4">
            <!-- Profile Avatar Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-camera me-2 text-primary"></i>Profile Photo
                    </h6>
                </div>
                <div class="card-body text-center p-4">
                    <div class="profile-photo-container mb-3">
                        <img src="{{ $user->getProfilePhotoUrl() }}" 
                             alt="{{ $user->name }}" 
                             class="profile-photo border rounded-circle mx-auto"
                             style="width: 120px; height: 120px; object-fit: cover;"
                             id="profile-photo-preview">
                    </div>
                    
                    <!-- Photo Upload Form -->
                    <form id="photo-upload-form" method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" style="display: none;">
                        @csrf
                        <input type="file" id="photo-input" name="photo" accept="image/*" required>
                    </form>
                    
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('photo-input').click()">
                            <i class="fas fa-upload me-2"></i>Upload Photo
                        </button>
                        @if($user->profile_photo)
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deletePhoto()">
                            <i class="fas fa-trash me-2"></i>Remove
                        </button>
                        @endif
                    </div>
                    <p class="text-muted small mt-2 mb-0">JPG, PNG or GIF. Max Size 1080x1080px, 2MB.</p>
                </div>
            </div>
            
            <!-- Account Security Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-shield-alt me-2 text-warning"></i>Account Security
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="security-item d-flex align-items-center mb-3">
                        <div class="security-icon bg-success text-white rounded-circle me-3">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <div class="fw-bold small">Email Verified</div>
                            <div class="text-muted small">{{ $user->email_verified_at ? 'Verified' : 'Not verified' }}</div>
                        </div>
                    </div>
                    
                    <div class="security-item d-flex align-items-center mb-3">
                        <div class="security-icon bg-info text-white rounded-circle me-3">
                            <i class="fas fa-key"></i>
                        </div>
                        <div>
                            <div class="fw-bold small">Password</div>
                            <div class="text-muted small">Last changed: Recently</div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-outline-warning btn-sm w-100" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="fas fa-edit me-2"></i>Change Password
                    </button>
                </div>
            </div>
            
            <!-- Quick Stats Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-chart-bar me-2 text-info"></i>Quick Stats
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="stat-number text-primary fw-bold">{{ $user->timesheets()->count() }}</div>
                                <div class="stat-label text-muted small">Timesheets</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="stat-number text-success fw-bold">{{ $user->attendances()->count() }}</div>
                                <div class="stat-label text-muted small">Attendances</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Additional Profile Sections -->
    <div class="row g-4 mt-2">
        <!-- Change Password Section -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-warning text-white me-3">
                            <i class="fas fa-key"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Change Password</h5>
                            <p class="text-muted mb-0 small">Update your account password for better security</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="post" action="{{ route('password.update') }}" class="needs-validation" novalidate>
                        @csrf
                        @method('put')
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="password" 
                                           class="form-control @error('current_password') is-invalid @enderror" 
                                           id="current_password" 
                                           name="current_password" 
                                           placeholder="Current Password"
                                           required>
                                    <label for="current_password">
                                        <i class="fas fa-lock me-2 text-muted"></i>Current Password
                                    </label>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           placeholder="New Password"
                                           required>
                                    <label for="password">
                                        <i class="fas fa-key me-2 text-muted"></i>New Password
                                    </label>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           placeholder="Confirm New Password"
                                           required>
                                    <label for="password_confirmation">
                                        <i class="fas fa-key me-2 text-muted"></i>Confirm New Password
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-warning btn-lg px-4">
                                <i class="fas fa-key me-2"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Account Details (Read-Only) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-info-circle me-2 text-info"></i>Account Details
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="info-item d-flex align-items-center mb-3">
                        <div class="info-icon bg-primary text-white rounded-circle me-3">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div class="fw-bold small">Account Type</div>
                            <div class="text-muted small">{{ $user->getDisplayRole() }}</div>
                        </div>
                    </div>
                    
                    <div class="info-item d-flex align-items-center mb-3">
                        <div class="info-icon bg-info text-white rounded-circle me-3">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <div class="fw-bold small">Phone Number</div>
                            <div class="text-muted small">{{ $user->phone ?? 'Not provided' }}</div>
                        </div>
                    </div>
                    
                    <div class="info-item d-flex align-items-center mb-3">
                        <div class="info-icon bg-secondary text-white rounded-circle me-3">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <div class="fw-bold small">Department</div>
                            <div class="text-muted small">{{ $user->department ?? 'Not assigned' }}</div>
                        </div>
                    </div>
                    
                    <div class="info-item d-flex align-items-center mb-3">
                        <div class="info-icon bg-success text-white rounded-circle me-3">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div>
                            <div class="fw-bold small">Member Since</div>
                            <div class="text-muted small">{{ $user->created_at->format('F j, Y') }}</div>
                        </div>
                    </div>
                    
                    <div class="info-item d-flex align-items-center">
                        <div class="info-icon bg-warning text-white rounded-circle me-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <div class="fw-bold small">Last Updated</div>
                            <div class="text-muted small">{{ $user->updated_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    
                    @if($user->bio)
                    <div class="info-item mt-3 pt-3 border-top">
                        <div class="d-flex align-items-start">
                            <div class="info-icon bg-dark text-white rounded-circle me-3">
                                <i class="fas fa-info"></i>
                            </div>
                            <div>
                                <div class="fw-bold small mb-1">Bio</div>
                                <div class="text-muted small">{{ $user->bio }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">
                    <i class="fas fa-key me-2"></i>Change Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('password.update') }}">
                @csrf
                @method('put')
                <div class="modal-body">
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="modal_current_password" name="current_password" placeholder="Current Password" required>
                        <label for="modal_current_password">Current Password</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="modal_password" name="password" placeholder="New Password" required>
                        <label for="modal_password">New Password</label>
                    </div>
                    <div class="form-floating">
                        <input type="password" class="form-control" id="modal_password_confirmation" name="password_confirmation" placeholder="Confirm New Password" required>
                        <label for="modal_password_confirmation">Confirm New Password</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key me-2"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Success/Error Messages -->
@if(session('status') === 'profile-updated')
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Profile updated successfully!
            </div>
        </div>
    </div>
@endif

@if(session('status') === 'password-updated')
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Password updated successfully!
            </div>
        </div>
    </div>
@endif

@if(session('status') === 'photo-updated')
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Profile photo updated successfully!
            </div>
        </div>
    </div>
@endif

@if(session('status') === 'photo-deleted')
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Profile photo removed successfully!
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session('error') }}
            </div>
        </div>
    </div>
@endif

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

.security-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
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

.form-floating > label {
    padding-left: 2.5rem;
}

.form-floating > .form-control:focus ~ label,
.form-floating > .form-control:not(:placeholder-shown) ~ label {
    opacity: 0.65;
    transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
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
}
</style>

<!-- JavaScript for enhanced functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Auto-hide toasts
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toast => {
        setTimeout(() => {
            const bsToast = new bootstrap.Toast(toast);
            bsToast.hide();
        }, 5000);
    });
    
    // Clear any stuck loading states
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(button => {
        button.disabled = false;
        const originalText = button.innerHTML;
        button.innerHTML = originalText.replace('Saving...', 'Save Changes');
    });
    
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const strength = getPasswordStrength(this.value);
            updatePasswordStrengthIndicator(strength);
        });
    }
    
    // Clear form validation on input
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
    
    // Photo upload functionality
    const photoInput = document.getElementById('photo-input');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    return;
                }
                
                // Validate file type
                if (!file.type.match('image.*')) {
                    alert('Please select an image file');
                    return;
                }
                
                // Validate image dimensions
                const img = new Image();
                img.onload = function() {
                    if (this.width > 1080 || this.height > 1080) {
                        alert(`Image must be maximum 1080x1080 pixels. Current size: ${this.width}x${this.height} pixels.`);
                        return;
                    }
                    
                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('profile-photo-preview');
                        const headerAvatar = document.querySelector('.profile-avatar img');
                        if (preview) preview.src = e.target.result;
                        if (headerAvatar) headerAvatar.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                    
                    // Submit form
                    document.getElementById('photo-upload-form').submit();
                };
                img.src = URL.createObjectURL(file);
            }
        });
    }
});

function getPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    return strength;
}

function updatePasswordStrengthIndicator(strength) {
    // This would update a password strength indicator if implemented
    console.log('Password strength:', strength);
}

function deletePhoto() {
    if (confirm('Are you sure you want to remove your profile photo?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("profile.photo.delete") }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
