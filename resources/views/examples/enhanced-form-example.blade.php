@extends('layouts.app-ACCESSIBLE')

@section('title', 'Enhanced Form Example')
@section('page-title', 'Enhanced Form with Validation')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Form Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Employee Registration Form</h1>
        <p class="text-gray-600">Please fill out all required fields. Validation occurs in real-time as you complete each field.</p>
    </div>
    
    <!-- Enhanced Form with Validation -->
    <x-form-validator 
        formId="employeeForm"
        :rules="[
            'first_name' => ['required', ['type' => 'min', 'length' => 2]],
            'last_name' => ['required', ['type' => 'min', 'length' => 2]],
            'email' => ['required', 'email'],
            'phone' => [['type' => 'regex', 'pattern' => '^[\+]?[0-9\s\-\(\)]{10,}$', 'message' => 'Please enter a valid phone number']],
            'hire_date' => ['required', 'date', ['type' => 'after', 'date' => '2020-01-01']],
            'salary' => ['required', 'numeric'],
            'password' => ['required', ['type' => 'min', 'length' => 8]],
            'password_confirmation' => ['required', 'confirmed'],
            'department' => ['required'],
            'terms' => ['required']
        ]"
        :messages="[
            'first_name.required' => 'First name is required',
            'first_name.min' => 'First name must be at least 2 characters long',
            'last_name.required' => 'Last name is required',
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'hire_date.after' => 'Hire date must be after January 1, 2020',
            'salary.numeric' => 'Salary must be a valid number',
            'password.min' => 'Password must be at least 8 characters long',
            'terms.required' => 'You must accept the terms and conditions'
        ]"
        submitEndpoint="/api/employees"
        redirectAfterSuccess="/employees"
        :showProgressBar="true">
        
        <form id="employeeForm" 
              class="space-y-6 bg-white shadow-lg rounded-lg p-6" 
              novalidate>
            
            @csrf
            
            <!-- Personal Information Section -->
            <fieldset class="border border-gray-200 rounded-lg p-4">
                <legend class="text-lg font-semibold text-gray-900 px-2">Personal Information</legend>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <!-- First Name -->
                    <div class="form-group">
                        <label for="first_name" 
                               class="block text-sm font-medium text-gray-700 mb-1">
                            First Name <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter your first name"
                               required
                               autocomplete="given-name"
                               aria-describedby="first_name_help">
                        <small id="first_name_help" class="text-xs text-gray-500 mt-1 block">
                            Minimum 2 characters required
                        </small>
                    </div>
                    
                    <!-- Last Name -->
                    <div class="form-group">
                        <label for="last_name" 
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Last Name <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <input type="text" 
                               id="last_name" 
                               name="last_name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter your last name"
                               required
                               autocomplete="family-name">
                    </div>
                </div>
                
                <!-- Email -->
                <div class="form-group mt-4">
                    <label for="email" 
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Email Address <span class="text-red-500" aria-label="required">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="you@example.com"
                           required
                           autocomplete="email"
                           aria-describedby="email_help">
                    <small id="email_help" class="text-xs text-gray-500 mt-1 block">
                        We'll use this for account notifications
                    </small>
                </div>
                
                <!-- Phone -->
                <div class="form-group mt-4">
                    <label for="phone" 
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Phone Number
                    </label>
                    <input type="tel" 
                           id="phone" 
                           name="phone"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="+1 (555) 123-4567"
                           autocomplete="tel">
                </div>
            </fieldset>
            
            <!-- Employment Information Section -->
            <fieldset class="border border-gray-200 rounded-lg p-4">
                <legend class="text-lg font-semibold text-gray-900 px-2">Employment Information</legend>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <!-- Department -->
                    <div class="form-group">
                        <label for="department" 
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Department <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <select id="department" 
                                name="department"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Choose a department</option>
                            <option value="engineering">Engineering</option>
                            <option value="marketing">Marketing</option>
                            <option value="sales">Sales</option>
                            <option value="hr">Human Resources</option>
                            <option value="finance">Finance</option>
                        </select>
                    </div>
                    
                    <!-- Hire Date -->
                    <div class="form-group">
                        <label for="hire_date" 
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Hire Date <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <input type="date" 
                               id="hire_date" 
                               name="hire_date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required
                               min="2020-01-01">
                    </div>
                </div>
                
                <!-- Salary -->
                <div class="form-group mt-4">
                    <label for="salary" 
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Annual Salary (USD) <span class="text-red-500" aria-label="required">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                        <input type="number" 
                               id="salary" 
                               name="salary"
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="50000"
                               min="0"
                               step="1000"
                               required>
                    </div>
                </div>
            </fieldset>
            
            <!-- Security Section -->
            <fieldset class="border border-gray-200 rounded-lg p-4">
                <legend class="text-lg font-semibold text-gray-900 px-2">Account Security</legend>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" 
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Password <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="••••••••"
                               required
                               autocomplete="new-password"
                               aria-describedby="password_help">
                        <small id="password_help" class="text-xs text-gray-500 mt-1 block">
                            Minimum 8 characters with letters, numbers, and symbols
                        </small>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="password_confirmation" 
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Confirm Password <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="••••••••"
                               required
                               autocomplete="new-password">
                    </div>
                </div>
            </fieldset>
            
            <!-- Terms and Conditions -->
            <div class="form-group">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="terms" 
                               name="terms" 
                               type="checkbox" 
                               value="1"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                               required>
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="terms" class="font-medium text-gray-700">
                            I accept the 
                            <a href="#" class="text-blue-600 hover:text-blue-500 underline focus:ring-enhanced">
                                terms and conditions
                            </a> 
                            and 
                            <a href="#" class="text-blue-600 hover:text-blue-500 underline focus:ring-enhanced">
                                privacy policy
                            </a>
                            <span class="text-red-500" aria-label="required">*</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="pt-4">
                <button type="submit" 
                        class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                        x-bind:disabled="isSubmitting"
                        aria-describedby="submit_help">
                    
                    <span x-show="!isSubmitting" class="flex items-center">
                        <i class="bi bi-person-plus mr-2" aria-hidden="true"></i>
                        Create Employee Account
                    </span>
                    
                    <span x-show="isSubmitting" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Creating Account...
                    </span>
                </button>
                
                <small id="submit_help" class="text-xs text-gray-500 mt-2 block text-center">
                    Form will be validated before submission
                </small>
            </div>
        </form>
    </x-form-validator>
    
    <!-- Demo Information -->
    <div class="mt-8 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="bi bi-info-circle-fill h-5 w-5 text-blue-400" aria-hidden="true"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Demo Form Features</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Real-time client-side validation with accessibility support</li>
                        <li>Progress bar during form submission</li>
                        <li>Screen reader announcements for validation states</li>
                        <li>Keyboard navigation support (Tab, Arrow keys)</li>
                        <li>ARIA attributes for form accessibility</li>
                        <li>High contrast mode support</li>
                        <li>Reduced motion preferences respected</li>
                    </ul>
                </div>
                <div class="mt-4 text-xs text-blue-600">
                    <p><strong>Try these accessibility features:</strong></p>
                    <ul class="list-disc list-inside mt-1 space-y-1">
                        <li>Use Tab key to navigate between fields</li>
                        <li>Try submitting with empty required fields</li>
                        <li>Enter invalid email or phone formats</li>
                        <li>Test password confirmation matching</li>
                        <li>Use screen reader to hear validation announcements</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Additional form-specific styles */
    .form-group:focus-within label {
        color: #3b82f6;
    }
    
    /* Enhance fieldset legends */
    fieldset legend {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        margin-bottom: 0.5rem;
    }
    
    /* Custom checkbox styling */
    input[type="checkbox"]:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }
    
    /* Loading animation for submit button */
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    /* Focus styles for better accessibility */
    .focus\:ring-enhanced:focus {
        outline: 2px solid #3b82f6;
        outline-offset: 2px;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    /* High contrast mode enhancements */
    @media (prefers-contrast: high) {
        fieldset {
            border-width: 2px;
            border-color: #000;
        }
        
        .bg-blue-50 {
            background-color: #e6f3ff;
            border-color: #0066cc;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Additional form enhancements
    document.addEventListener('DOMContentLoaded', function() {
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt + S: Focus submit button
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                document.querySelector('button[type="submit"]')?.focus();
            }
        });
        
        // Add password strength indicator
        const passwordField = document.getElementById('password');
        if (passwordField) {
            passwordField.addEventListener('input', function() {
                // This could be expanded with a full password strength component
                const value = this.value;
                const hasLowercase = /[a-z]/.test(value);
                const hasUppercase = /[A-Z]/.test(value);
                const hasNumbers = /\d/.test(value);
                const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(value);
                const isLongEnough = value.length >= 8;
                
                const strengthScore = [hasLowercase, hasUppercase, hasNumbers, hasSpecialChar, isLongEnough].filter(Boolean).length;
                
                // Update visual indicator (this could be enhanced with a progress bar)
                console.log(`Password strength: ${strengthScore}/5`);
            });
        }
    });
</script>
@endpush