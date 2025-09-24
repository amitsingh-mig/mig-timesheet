@extends('layouts.app-ACCESSIBLE')

@section('title', 'Register - MIG-HRM')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-admin-green py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="mx-auto h-16 w-16 rounded-full bg-white flex items-center justify-center shadow-lg">
                <i class="bi bi-person-plus text-2xl text-green-600" aria-hidden="true"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-white">
                Create Your Account
            </h2>
            <p class="mt-2 text-sm text-green-100">
                Join the MIG-HRM time tracking system
            </p>
        </div>

        <!-- Enhanced Form with Validation -->
        <x-form-validator 
            form-id="registerForm"
            :rules="[
                'name' => ['required', ['min' => ['length' => 2]], ['max' => ['length' => 50]]],
                'email' => ['required', 'email'],
                'password' => ['required', ['min' => ['length' => 8]]],
                'password_confirmation' => ['required', 'confirmed']
            ]"
            :messages="[
                'name.required' => 'Please enter your full name',
                'name.min' => 'Name must be at least 2 characters long',
                'name.max' => 'Name cannot exceed 50 characters',
                'email.required' => 'Email address is required',
                'email.email' => 'Please enter a valid email address',
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 8 characters long',
                'password_confirmation.required' => 'Please confirm your password',
                'password_confirmation.confirmed' => 'Passwords do not match'
            ]"
            submit-endpoint="{{ route('register') }}"
            redirect-after-success="{{ route('dashboard') }}"
            :show-progress-bar="true">

            <div class="card-elevated bg-white shadow-xl rounded-lg">
                <div class="px-8 py-6">
                    <form id="registerForm" class="space-y-6">
                        @csrf
                        
                        <!-- Full Name -->
                        <fieldset>
                            <legend class="sr-only">Personal Information</legend>
                            
                            <div class="form-group">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="bi bi-person mr-1 text-gray-500" aria-hidden="true"></i>
                                    Full Name <span class="text-red-500" aria-label="required">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       class="form-input block w-full"
                                       value="{{ old('name') }}"
                                       placeholder="Enter your full name"
                                       aria-describedby="name-help"
                                       autocomplete="name"
                                       autofocus
                                       required>
                                <p id="name-help" class="text-xs text-gray-500 mt-1">Enter your first and last name</p>
                            </div>

                            <div class="form-group">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="bi bi-envelope mr-1 text-gray-500" aria-hidden="true"></i>
                                    Email Address <span class="text-red-500" aria-label="required">*</span>
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-input block w-full"
                                       value="{{ old('email') }}"
                                       placeholder="your.email@example.com"
                                       aria-describedby="email-help"
                                       autocomplete="username"
                                       required>
                                <p id="email-help" class="text-xs text-gray-500 mt-1">We'll use this for your login and notifications</p>
                            </div>
                        </fieldset>

                        <!-- Password Fields -->
                        <fieldset>
                            <legend class="sr-only">Password Information</legend>
                            
                            <div class="form-group">
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="bi bi-lock mr-1 text-gray-500" aria-hidden="true"></i>
                                    Password <span class="text-red-500" aria-label="required">*</span>
                                </label>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-input block w-full"
                                       placeholder="Create a strong password"
                                       aria-describedby="password-help"
                                       autocomplete="new-password"
                                       required>
                                <p id="password-help" class="text-xs text-gray-500 mt-1">Minimum 8 characters with letters and numbers</p>
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="bi bi-lock-fill mr-1 text-gray-500" aria-hidden="true"></i>
                                    Confirm Password <span class="text-red-500" aria-label="required">*</span>
                                </label>
                                <input type="password" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       class="form-input block w-full"
                                       placeholder="Repeat your password"
                                       aria-describedby="confirm-password-help"
                                       autocomplete="new-password"
                                       required>
                                <p id="confirm-password-help" class="text-xs text-gray-500 mt-1">Must match your password exactly</p>
                            </div>
                        </fieldset>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button type="submit" 
                                    class="btn-primary w-full py-3 text-center bg-green-600 hover:bg-green-700"
                                    :disabled="isSubmitting"
                                    x-bind:aria-label="isSubmitting ? 'Creating your account...' : 'Create account'">
                                <span x-show="!isSubmitting" class="flex items-center justify-center">
                                    <i class="bi bi-person-plus mr-2" aria-hidden="true"></i>
                                    Create Account
                                </span>
                                <span x-show="isSubmitting" class="flex items-center justify-center">
                                    <i class="bi bi-arrow-clockwise animate-spin mr-2" aria-hidden="true"></i>
                                    Creating Account...
                                </span>
                            </button>
                        </div>
                    </form>

                    <!-- Login Link -->
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">
                            Already have an account?
                            <a href="{{ route('login') }}" 
                               class="font-medium text-green-600 hover:text-green-500 transition-colors duration-200">
                                Sign in here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </x-form-validator>

        <!-- Help Text -->
        <div class="text-center">
            <p class="text-xs text-green-100 opacity-75">
                By creating an account, you agree to our terms of service and privacy policy.
            </p>
        </div>
    </div>
</div>

<!-- Password Strength Indicator -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            updatePasswordFeedback(password, strength);
        });
    }
    
    function calculatePasswordStrength(password) {
        let score = 0;
        if (password.length >= 8) score += 25;
        if (password.match(/[a-z]/)) score += 25;
        if (password.match(/[A-Z]/)) score += 25;
        if (password.match(/[0-9]/)) score += 25;
        return score;
    }
    
    function updatePasswordFeedback(password, strength) {
        const helpText = document.getElementById('password-help');
        if (!helpText) return;
        
        let message = 'Minimum 8 characters with letters and numbers';
        let className = 'text-xs text-gray-500 mt-1';
        
        if (password.length > 0) {
            if (strength < 50) {
                message = 'Password is weak - add more characters, numbers, and mixed case';
                className = 'text-xs text-red-600 mt-1';
            } else if (strength < 75) {
                message = 'Password is moderate - consider adding more complexity';
                className = 'text-xs text-yellow-600 mt-1';
            } else {
                message = 'Password looks strong!';
                className = 'text-xs text-green-600 mt-1';
            }
        }
        
        helpText.textContent = message;
        helpText.className = className;
    }
});
</script>
@endsection