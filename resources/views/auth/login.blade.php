<x-guest-layout>
    <div class="card border-0 shadow-lg auth-card p-4 p-md-5 transition-all">
        <div class="text-center mb-4">
            <div class="brand-badge mb-3">
                <i class="bi bi-clock-fill text-white text-2_5xl"></i>
            </div>
            <h4 class="mb-2 fw-bold text-dark">Welcome Back</h4>
            <p class="text-muted mb-0">Sign in to your MiG-HRM account</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-3" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
            @csrf

            <!-- Email Address -->
            <div class="mb-3">
                <x-input-label for="email" :value="__('Email Address')" class="form-label fw-semibold text-dark" />
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-envelope text-muted"></i>
                    </span>
                    <x-text-input id="email" 
                                  class="form-control rounded-input" 
                                  type="email" 
                                  name="email" 
                                  :value="old('email')" 
                                  required 
                                  autofocus 
                                  autocomplete="username"
                                  placeholder="Enter your email address" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger small" />
            </div>

            <!-- Password -->
            <div class="mb-3">
                <x-input-label for="password" :value="__('Password')" class="form-label fw-semibold text-dark" />
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock text-muted"></i>
                    </span>
                    <x-text-input id="password" 
                                  class="form-control rounded-input"
                                  type="password"
                                  name="password"
                                  required 
                                  autocomplete="current-password"
                                  placeholder="Enter your password" />
                    <button class="btn" type="button" id="togglePassword">
                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger small" />
            </div>

            <!-- Remember + Forgot -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                    <label for="remember_me" class="form-check-label text-muted">{{ __('Remember me') }}</label>
                </div>
                @if (Route::has('password.request'))
                    <a class="small text-decoration-none text-primary fw-medium" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-rounded py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                {{ __('Sign In') }}
            </button>
        </form>

        <!-- Additional Info -->
        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="bi bi-shield-check me-1"></i>
                Secure login with SSL encryption
            </small>
        </div>
    </div>

    <!-- Enhanced Login Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle functionality
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const togglePasswordIcon = document.getElementById('togglePasswordIcon');

            if (togglePassword && passwordInput && togglePasswordIcon) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    if (type === 'password') {
                        togglePasswordIcon.classList.remove('bi-eye-slash');
                        togglePasswordIcon.classList.add('bi-eye');
                    } else {
                        togglePasswordIcon.classList.remove('bi-eye');
                        togglePasswordIcon.classList.add('bi-eye-slash');
                    }
                });
            }

            // Form validation and submission
            const form = document.querySelector('form.needs-validation');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (form && submitBtn) {
                // Real-time validation
                const emailInput = document.getElementById('email');
                const passwordInput = document.getElementById('password');
                
                function validateEmail(email) {
                    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return re.test(email);
                }
                
                function validateField(input, validator) {
                    const isValid = validator(input.value);
                    input.classList.toggle('is-valid', isValid && input.value.length > 0);
                    input.classList.toggle('is-invalid', !isValid && input.value.length > 0);
                    return isValid;
                }
                
                // Email validation
                if (emailInput) {
                    emailInput.addEventListener('input', function() {
                        validateField(this, validateEmail);
                    });
                    
                    emailInput.addEventListener('blur', function() {
                        validateField(this, validateEmail);
                    });
                }
                
                // Password validation
                if (passwordInput) {
                    passwordInput.addEventListener('input', function() {
                        validateField(this, (value) => value.length >= 6);
                    });
                    
                    passwordInput.addEventListener('blur', function() {
                        validateField(this, (value) => value.length >= 6);
                    });
                }
                
                // Form submission
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Validate all fields
                    const isEmailValid = validateField(emailInput, validateEmail);
                    const isPasswordValid = validateField(passwordInput, (value) => value.length >= 6);
                    
                    if (isEmailValid && isPasswordValid) {
                        // Show loading state
                        submitBtn.classList.add('btn-loading');
                        submitBtn.disabled = true;
                        
                        // Add fade-in animation to card
                        const card = document.querySelector('.auth-card');
                        card.classList.add('fade-in');
                        
                        // Submit the form
                        setTimeout(() => {
                            form.submit();
                        }, 500);
                    } else {
                        // Show validation errors
                        if (!isEmailValid) {
                            emailInput.classList.add('is-invalid');
                        }
                        if (!isPasswordValid) {
                            passwordInput.classList.add('is-invalid');
                        }
                        
                        // Shake animation for invalid form
                        const card = document.querySelector('.auth-card');
                        card.style.animation = 'shake 0.5s ease-in-out';
                        setTimeout(() => {
                            card.style.animation = '';
                        }, 500);
                    }
                });
            }
            
            // Add shake animation CSS
            const style = document.createElement('style');
            style.textContent = `
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    25% { transform: translateX(-5px); }
                    75% { transform: translateX(5px); }
                }
            `;
            document.head.appendChild(style);
            
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert && alert.parentNode) {
                        alert.classList.remove('show');
                        setTimeout(() => {
                            if (alert.parentNode) {
                                alert.parentNode.removeChild(alert);
                            }
                        }, 150);
                    }
                }, 5000);
            });
        });
    </script>
</x-guest-layout>
