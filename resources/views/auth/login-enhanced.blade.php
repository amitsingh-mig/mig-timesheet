@extends('layouts.app-ACCESSIBLE')

@section('title', 'Login - MIG-HRM')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-admin py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="mx-auto h-16 w-16 rounded-full bg-white flex items-center justify-center shadow-lg">
                <span class="text-2xl font-bold text-red-600" aria-hidden="true">TS</span>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-white">
                Welcome Back
            </h2>
            <p class="mt-2 text-sm text-red-100">
                Sign in to your MIG-HRM account
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Enhanced Login Form with Validation -->
        <x-form-validator 
            form-id="loginForm"
            :rules="[
                'email' => ['required', 'email'],
                'password' => ['required']
            ]"
            :messages="[
                'email.required' => 'Please enter your email address',
                'email.email' => 'Please enter a valid email address',
                'password.required' => 'Please enter your password'
            ]"
            submit-endpoint="{{ route('login') }}"
            redirect-after-success="{{ route('dashboard') }}"
            :show-progress-bar="true">

            <div class="card-elevated bg-white shadow-xl rounded-lg">
                <div class="px-8 py-6">
                    <form id="loginForm" class="space-y-6">
                        @csrf
                        
                        <!-- Email Field -->
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
                                   autofocus
                                   required>
                            <p id="email-help" class="text-xs text-gray-500 mt-1">Enter the email address for your account</p>
                        </div>

                        <!-- Password Field -->
                        <div class="form-group">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="bi bi-lock mr-1 text-gray-500" aria-hidden="true"></i>
                                Password <span class="text-red-500" aria-label="required">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-input block w-full pr-10"
                                       placeholder="Enter your password"
                                       aria-describedby="password-help"
                                       autocomplete="current-password"
                                       required>
                                <button type="button" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                        onclick="togglePassword()"
                                        aria-label="Toggle password visibility"
                                        id="password-toggle">
                                    <i class="bi bi-eye text-gray-400 hover:text-gray-600" id="password-icon"></i>
                                </button>
                            </div>
                            <p id="password-help" class="text-xs text-gray-500 mt-1">Enter your account password</p>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="remember_me" 
                                       name="remember" 
                                       class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                <label for="remember_me" class="ml-2 block text-sm text-gray-700">
                                    Remember me
                                </label>
                            </div>

                            @if (Route::has('password.request'))
                                <div class="text-sm">
                                    <a href="{{ route('password.request') }}" 
                                       class="font-medium text-red-600 hover:text-red-500 transition-colors duration-200">
                                        Forgot password?
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button type="submit" 
                                    class="btn-primary w-full py-3 text-center bg-red-600 hover:bg-red-700"
                                    :disabled="isSubmitting"
                                    x-bind:aria-label="isSubmitting ? 'Signing you in...' : 'Sign in to your account'">
                                <span x-show="!isSubmitting" class="flex items-center justify-center">
                                    <i class="bi bi-box-arrow-in-right mr-2" aria-hidden="true"></i>
                                    Sign In
                                </span>
                                <span x-show="isSubmitting" class="flex items-center justify-center">
                                    <i class="bi bi-arrow-clockwise animate-spin mr-2" aria-hidden="true"></i>
                                    Signing In...
                                </span>
                            </button>
                        </div>
                    </form>

                    <!-- Register Link -->
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">
                            Don't have an account?
                            <a href="{{ route('register') }}" 
                               class="font-medium text-red-600 hover:text-red-500 transition-colors duration-200">
                                Create one here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </x-form-validator>

        <!-- Help Text -->
        <div class="text-center">
            <p class="text-xs text-red-100 opacity-75">
                Having trouble signing in? Contact your system administrator.
            </p>
        </div>
    </div>
</div>

<!-- Password Toggle Script -->
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('password-icon');
    const toggleButton = document.getElementById('password-toggle');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.className = 'bi bi-eye-slash text-gray-400 hover:text-gray-600';
        toggleButton.setAttribute('aria-label', 'Hide password');
    } else {
        passwordInput.type = 'password';
        passwordIcon.className = 'bi bi-eye text-gray-400 hover:text-gray-600';
        toggleButton.setAttribute('aria-label', 'Show password');
    }
}

// Add keyboard support for password toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('password-toggle');
    if (toggleButton) {
        toggleButton.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                togglePassword();
            }
        });
    }
});
</script>
@endsection