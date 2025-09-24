@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1 fw-bold">👤 Profile Settings</h2>
        <p class="text-muted mb-0">Manage your account information and preferences</p>
    </div>
</div>

<div class="row g-4">
    <!-- Update Profile Information -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Profile Information</h5>
            </div>
            <div class="card-body p-4">
                @if(file_exists(resource_path('views/profile/partials/update-profile-information-form.blade.php')))
                    @include('profile.partials.update-profile-information-form')
                @else
                    <form method="post" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Update Password -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h5>
            </div>
            <div class="card-body p-4">
                @if(file_exists(resource_path('views/profile/partials/update-password-form.blade.php')))
                    @include('profile.partials.update-password-form')
                @else
                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i>Update Password
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Delete Account -->
    <div class="col-12">
        <div class="card border-0 shadow-sm border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-trash me-2"></i>Delete Account</h5>
            </div>
            <div class="card-body p-4">
                @if(file_exists(resource_path('views/profile/partials/delete-user-form.blade.php')))
                    @include('profile.partials.delete-user-form')
                @else
                    <div class="alert alert-warning">
                        <strong>Warning!</strong> Once your account is deleted, all of its resources and data will be permanently deleted.
                    </div>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="fas fa-trash me-2"></i>Delete Account
                    </button>
                    
                    <!-- Delete Account Modal -->
                    <div class="modal fade" id="deleteAccountModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post" action="{{ route('profile.destroy') }}">
                                    @csrf
                                    @method('delete')
                                    
                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete Account</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete your account? This action cannot be undone.</p>
                                        <div class="mb-3">
                                            <label for="delete_password" class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control" id="delete_password" name="password" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Delete Account</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if(session('status') === 'profile-updated')
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>Profile updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@endsection
