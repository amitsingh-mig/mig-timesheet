<section class="card-elevated bg-white p-6">
    <!-- Header -->
    <header class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 flex items-center">
            <i class="bi bi-person-circle text-blue-600 mr-2" aria-hidden="true"></i>
            Profile Information
        </h2>
        <p class="mt-2 text-sm text-gray-600">
            Update your account's profile information and email address. Keep your information current for better account security.
        </p>
    </header>

    <!-- Email Verification Form (Hidden) -->
    <form id="send-verification" method="post" action="{{ route('verification.send') }}" style="display: none;">
        @csrf
    </form>

    <!-- Enhanced Profile Update Form with Validation -->
    <x-form-validator 
        form-id="profileUpdateForm"
        :rules="[
            'name' => ['required', ['min' => ['length' => 2]], ['max' => ['length' => 50]]],
            'email' => ['required', 'email']
        ]"
        :messages="[
            'name.required' => 'Please enter your full name',
            'name.min' => 'Name must be at least 2 characters long',
            'name.max' => 'Name cannot exceed 50 characters',
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address'
        ]"
        submit-endpoint="{{ route('profile.update') }}"
        :show-progress-bar="true">

        <form id="profileUpdateForm" class="space-y-6" method="POST">
            @csrf
            @method('PATCH')
            
            <!-- Personal Information -->
            <fieldset class="border border-gray-200 rounded-lg p-4">
                <legend class="text-lg font-medium text-gray-900 px-2">Personal Information</legend>
                
                <div class="space-y-4">
                    <!-- Full Name -->
                    <div class="form-group">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="bi bi-person mr-1 text-gray-500" aria-hidden="true"></i>
                            Full Name <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               class="form-input block w-full"
                               value="{{ old('name', $user->name) }}"
                               placeholder="Enter your full name"
                               aria-describedby="name-help"
                               autocomplete="name"
                               autofocus
                               required>
                        <p id="name-help" class="text-xs text-gray-500 mt-1">This name will be displayed throughout the application</p>
                    </div>

                    <!-- Email Address -->
                    <div class="form-group">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="bi bi-envelope mr-1 text-gray-500" aria-hidden="true"></i>
                            Email Address <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input block w-full"
                               value="{{ old('email', $user->email) }}"
                               placeholder="your.email@example.com"
                               aria-describedby="email-help"
                               autocomplete="username"
                               required>
                        <p id="email-help" class="text-xs text-gray-500 mt-1">Used for login and important notifications</p>
                        
                        <!-- Email Verification Notice -->
                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-md">
                                <div class="flex items-start">
                                    <i class="bi bi-exclamation-triangle-fill text-amber-500 mr-2 mt-0.5 flex-shrink-0" aria-hidden="true"></i>
                                    <div>
                                        <p class="text-sm text-amber-800 font-medium">Email verification required</p>
                                        <p class="text-sm text-amber-700 mt-1">
                                            Your email address is unverified. 
                                            <button type="button" 
                                                    onclick="document.getElementById('send-verification').submit();"
                                                    class="font-medium text-amber-600 hover:text-amber-500 underline focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1 rounded">
                                                Click here to resend verification email
                                            </button>
                                        </p>
                                    </div>
                                </div>
                                
                                @if (session('status') === 'verification-link-sent')
                                    <div class="mt-2 p-2 bg-green-50 border border-green-200 rounded">
                                        <p class="text-sm text-green-700 flex items-center">
                                            <i class="bi bi-check-circle-fill text-green-500 mr-1" aria-hidden="true"></i>
                                            Verification link sent to your email address
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </fieldset>

            <!-- Additional Profile Information -->
            <fieldset class="border border-gray-200 rounded-lg p-4">
                <legend class="text-lg font-medium text-gray-900 px-2">Account Details</legend>
                
                <div class="space-y-4">
                    <!-- Role Information (Read-only) -->
                    <div class="form-group">
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="bi bi-shield mr-1 text-gray-500" aria-hidden="true"></i>
                            Account Role
                        </label>
                        <input type="text" 
                               id="role" 
                               class="form-input block w-full bg-gray-50 cursor-not-allowed"
                               value="{{ ucfirst($user->role ?? 'User') }}"
                               readonly
                               aria-describedby="role-help">
                        <p id="role-help" class="text-xs text-gray-500 mt-1">Contact your administrator to change account role</p>
                    </div>

                    <!-- Account Created -->
                    <div class="form-group">
                        <label for="created_at" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="bi bi-calendar-plus mr-1 text-gray-500" aria-hidden="true"></i>
                            Account Created
                        </label>
                        <input type="text" 
                               id="created_at" 
                               class="form-input block w-full bg-gray-50 cursor-not-allowed"
                               value="{{ $user->created_at->format('F j, Y') }}"
                               readonly
                               aria-describedby="created-help">
                        <p id="created-help" class="text-xs text-gray-500 mt-1">When your account was first created</p>
                    </div>
                </div>
            </fieldset>

            <!-- Submit Section -->
            <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
                <button type="submit" 
                        class="btn-primary flex-1 sm:flex-none px-6 py-3 text-center"
                        :disabled="isSubmitting"
                        x-bind:aria-label="isSubmitting ? 'Saving changes...' : 'Save profile changes'">
                    <span x-show="!isSubmitting" class="flex items-center justify-center">
                        <i class="bi bi-check-circle mr-2" aria-hidden="true"></i>
                        Save Changes
                    </span>
                    <span x-show="isSubmitting" class="flex items-center justify-center">
                        <i class="bi bi-arrow-clockwise animate-spin mr-2" aria-hidden="true"></i>
                        Saving...
                    </span>
                </button>
                
                <button type="button" 
                        onclick="location.reload();" 
                        class="btn-secondary px-6 py-3"
                        aria-label="Cancel changes and reload original values">
                    <i class="bi bi-x-circle mr-2" aria-hidden="true"></i>
                    Cancel
                </button>
            </div>
        </form>
    </x-form-validator>

    <!-- Success Message -->
    @if (session('status') === 'profile-updated')
        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-md animate-fade-in"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center">
                <i class="bi bi-check-circle-fill text-green-500 mr-2" aria-hidden="true"></i>
                <p class="text-sm font-medium text-green-800">
                    Profile updated successfully!
                </p>
            </div>
        </div>
    @endif
</section>

<!-- Enhanced Profile Update Feedback -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add visual feedback for email changes
    const emailInput = document.getElementById('email');
    const originalEmail = '{{ $user->email }}';
    
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const helpText = document.getElementById('email-help');
            if (this.value !== originalEmail && this.value.length > 0) {
                helpText.textContent = 'Email change will require verification';
                helpText.className = 'text-xs text-amber-600 mt-1';
            } else {
                helpText.textContent = 'Used for login and important notifications';
                helpText.className = 'text-xs text-gray-500 mt-1';
            }
        });
    }
    
    // Add name formatting
    const nameInput = document.getElementById('name');
    if (nameInput) {
        nameInput.addEventListener('blur', function() {
            // Basic name formatting - capitalize first letter of each word
            this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
        });
    }
});
</script>