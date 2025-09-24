@props([
    'formId' => 'validatedForm',
    'rules' => [],
    'messages' => [],
    'submitEndpoint' => null,
    'redirectAfterSuccess' => null,
    'showProgressBar' => false
])

<div 
    x-data="formValidator(@js($rules), @js($messages), '{{ $submitEndpoint }}', '{{ $redirectAfterSuccess }}', {{ $showProgressBar ? 'true' : 'false' }})"
    x-init="initForm('{{ $formId }}')"
    class="form-validation-wrapper">
    
    <!-- Progress Bar (Optional) -->
    <div x-show="showProgressBar && isSubmitting" 
         class="mb-4 bg-gray-200 rounded-full h-2 overflow-hidden"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300 ease-out"
             :style="`width: ${submitProgress}%`"></div>
        <span class="sr-only" x-text="`Form submission ${submitProgress}% complete`"></span>
    </div>
    
    <!-- Global Form Error Messages -->
    <div x-show="globalErrors.length > 0" 
         class="mb-4 bg-red-50 border-l-4 border-red-400 p-4"
         role="alert"
         aria-live="assertive"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="bi bi-exclamation-triangle-fill h-5 w-5 text-red-400" aria-hidden="true"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                    Please correct the following <span x-text="globalErrors.length === 1 ? 'error' : 'errors'"></span>:
                </h3>
                <ul class="mt-2 text-sm text-red-700 list-disc list-inside" role="list">
                    <template x-for="error in globalErrors" :key="error.field">
                        <li x-text="error.message"></li>
                    </template>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Success Message -->
    <div x-show="successMessage" 
         class="mb-4 bg-green-50 border-l-4 border-green-400 p-4"
         role="alert"
         aria-live="polite"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="bi bi-check-circle-fill h-5 w-5 text-green-400" aria-hidden="true"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">Success</h3>
                <p class="text-sm text-green-700" x-text="successMessage"></p>
            </div>
        </div>
    </div>
    
    {{ $slot }}
    
    <!-- Live region for validation announcements -->
    <div id="validation-announcements" 
         class="sr-only" 
         aria-live="assertive" 
         aria-atomic="true"
         x-text="validationAnnouncement"></div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('formValidator', (rules = {}, messages = {}, submitEndpoint = null, redirectAfterSuccess = null, showProgressBar = false) => ({
        rules,
        messages,
        submitEndpoint,
        redirectAfterSuccess,
        showProgressBar,
        formElement: null,
        formData: {},
        errors: {},
        globalErrors: [],
        isSubmitting: false,
        submitProgress: 0,
        successMessage: '',
        validationAnnouncement: '',
        hasBeenSubmitted: false,
        
        initForm(formId) {
            this.formElement = document.getElementById(formId);
            if (!this.formElement) {
                console.error(`Form with ID "${formId}" not found`);
                return;
            }
            
            // Initialize form data from form inputs
            this.initializeFormData();
            
            // Add event listeners
            this.addFormEventListeners();
            
            // Add real-time validation
            this.addRealtimeValidation();
        },
        
        initializeFormData() {
            const formElements = this.formElement.querySelectorAll('input, select, textarea');
            formElements.forEach(element => {
                if (element.name) {
                    this.formData[element.name] = this.getElementValue(element);
                }
            });
        },
        
        addFormEventListeners() {
            // Prevent default form submission and use our validator
            this.formElement.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleSubmit();
            });
            
            // Update form data on input changes
            this.formElement.addEventListener('input', (e) => {
                if (e.target.name) {
                    this.formData[e.target.name] = this.getElementValue(e.target);
                }
            });
            
            this.formElement.addEventListener('change', (e) => {
                if (e.target.name) {
                    this.formData[e.target.name] = this.getElementValue(e.target);
                }
            });
        },
        
        addRealtimeValidation() {
            const formElements = this.formElement.querySelectorAll('input, select, textarea');
            formElements.forEach(element => {
                if (element.name && this.rules[element.name]) {
                    // Validate on blur (after user finishes typing)
                    element.addEventListener('blur', () => {
                        if (this.hasBeenSubmitted || this.errors[element.name]) {
                            this.validateField(element.name);
                            this.updateFieldUI(element);
                        }
                    });
                    
                    // Clear errors on input (while typing)
                    element.addEventListener('input', () => {
                        if (this.errors[element.name]) {
                            delete this.errors[element.name];
                            this.updateFieldUI(element);
                        }
                    });
                }
            });
        },
        
        getElementValue(element) {
            if (element.type === 'checkbox') {
                return element.checked;
            } else if (element.type === 'radio') {
                const checkedRadio = this.formElement.querySelector(`input[name="${element.name}"]:checked`);
                return checkedRadio ? checkedRadio.value : '';
            } else if (element.tagName === 'SELECT' && element.multiple) {
                return Array.from(element.selectedOptions).map(option => option.value);
            }
            return element.value;
        },
        
        validateField(fieldName) {
            const fieldRules = this.rules[fieldName];
            const value = this.formData[fieldName];
            
            if (!fieldRules) return true;
            
            // Clear previous errors for this field
            delete this.errors[fieldName];
            
            for (const rule of fieldRules) {
                const ruleResult = this.applyValidationRule(rule, value, fieldName);
                if (!ruleResult.valid) {
                    this.errors[fieldName] = ruleResult.message;
                    return false;
                }
            }
            
            return true;
        },
        
        validateAllFields() {
            this.errors = {};
            this.globalErrors = [];
            let isValid = true;
            
            for (const fieldName in this.rules) {
                if (!this.validateField(fieldName)) {
                    isValid = false;
                }
            }
            
            // Update UI for all fields
            const formElements = this.formElement.querySelectorAll('input, select, textarea');
            formElements.forEach(element => {
                if (element.name) {
                    this.updateFieldUI(element);
                }
            });
            
            // Create global errors summary
            if (!isValid) {
                this.globalErrors = Object.entries(this.errors).map(([field, message]) => ({
                    field,
                    message: `${this.getFieldLabel(field)}: ${message}`
                }));
                
                this.announceValidationErrors();
            }
            
            return isValid;
        },
        
        applyValidationRule(rule, value, fieldName) {
            const ruleType = typeof rule === 'string' ? rule : rule.type;
            const ruleOptions = typeof rule === 'object' ? rule : {};
            
            switch (ruleType) {
                case 'required':
                    if (!value || (Array.isArray(value) && value.length === 0) || 
                        (typeof value === 'string' && value.trim() === '')) {
                        return {
                            valid: false,
                            message: this.getCustomMessage(fieldName, 'required') || 'This field is required.'
                        };
                    }
                    break;
                    
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (value && !emailRegex.test(value)) {
                        return {
                            valid: false,
                            message: this.getCustomMessage(fieldName, 'email') || 'Please enter a valid email address.'
                        };
                    }
                    break;
                    
                case 'min':
                    const minLength = ruleOptions.length || rule.length;
                    if (value && value.length < minLength) {
                        return {
                            valid: false,
                            message: this.getCustomMessage(fieldName, 'min') || `This field must be at least ${minLength} characters long.`
                        };
                    }
                    break;
                    
                case 'max':
                    const maxLength = ruleOptions.length || rule.length;
                    if (value && value.length > maxLength) {
                        return {
                            valid: false,
                            message: this.getCustomMessage(fieldName, 'max') || `This field cannot be more than ${maxLength} characters long.`
                        };
                    }
                    break;
                    
                case 'numeric':
                    if (value && !/^\d+(\.\d+)?$/.test(value)) {
                        return {
                            valid: false,
                            message: this.getCustomMessage(fieldName, 'numeric') || 'This field must be a number.'
                        };
                    }
                    break;
                    
                case 'regex':
                    const pattern = new RegExp(ruleOptions.pattern || rule.pattern);
                    if (value && !pattern.test(value)) {
                        return {
                            valid: false,
                            message: this.getCustomMessage(fieldName, 'regex') || ruleOptions.message || 'This field format is invalid.'
                        };
                    }
                    break;
                    
                case 'confirmed':
                    const confirmationField = `${fieldName}_confirmation`;
                    if (value !== this.formData[confirmationField]) {
                        return {
                            valid: false,
                            message: this.getCustomMessage(fieldName, 'confirmed') || 'The confirmation field does not match.'
                        };
                    }
                    break;
                    
                case 'date':
                    if (value && isNaN(Date.parse(value))) {
                        return {
                            valid: false,
                            message: this.getCustomMessage(fieldName, 'date') || 'Please enter a valid date.'
                        };
                    }
                    break;
                    
                case 'before':
                    const beforeDate = new Date(ruleOptions.date || rule.date);
                    if (value && new Date(value) >= beforeDate) {
                        return {
                            valid: false,
                            message: this.getCustomMessage(fieldName, 'before') || `Date must be before ${beforeDate.toLocaleDateString()}.`
                        };
                    }
                    break;
                    
                case 'after':
                    const afterDate = new Date(ruleOptions.date || rule.date);
                    if (value && new Date(value) <= afterDate) {
                        return {
                            valid: false,
                            message: this.getCustomMessage(fieldName, 'after') || `Date must be after ${afterDate.toLocaleDateString()}.`
                        };
                    }
                    break;
            }
            
            return { valid: true };
        },
        
        getCustomMessage(fieldName, ruleType) {
            return this.messages[`${fieldName}.${ruleType}`] || this.messages[fieldName];
        },
        
        getFieldLabel(fieldName) {
            const element = this.formElement.querySelector(`[name="${fieldName}"]`);
            const label = this.formElement.querySelector(`label[for="${element?.id}"]`);
            return label?.textContent?.trim() || fieldName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },
        
        updateFieldUI(element) {
            const fieldName = element.name;
            const hasError = this.errors[fieldName];
            const parentGroup = element.closest('.form-group, .mb-4, .mb-3, .mb-2');
            
            // Update field styling
            element.classList.toggle('border-red-500', hasError);
            element.classList.toggle('border-gray-300', !hasError);
            element.classList.toggle('focus:border-red-500', hasError);
            element.classList.toggle('focus:ring-red-500', hasError);
            
            // Update or create error message element
            let errorElement = parentGroup?.querySelector('.field-error');
            if (hasError) {
                if (!errorElement) {
                    errorElement = document.createElement('p');
                    errorElement.className = 'field-error text-sm text-red-600 mt-1';
                    errorElement.setAttribute('role', 'alert');
                    errorElement.setAttribute('id', `${element.id || fieldName}-error`);
                    element.setAttribute('aria-describedby', errorElement.id);
                    
                    if (parentGroup) {
                        parentGroup.appendChild(errorElement);
                    } else {
                        element.parentNode.insertBefore(errorElement, element.nextSibling);
                    }
                }
                errorElement.textContent = this.errors[fieldName];
                errorElement.setAttribute('aria-live', 'polite');
            } else if (errorElement) {
                errorElement.remove();
                element.removeAttribute('aria-describedby');
            }
            
            // Update ARIA invalid state
            element.setAttribute('aria-invalid', hasError ? 'true' : 'false');
        },
        
        announceValidationErrors() {
            const errorCount = Object.keys(this.errors).length;
            this.validationAnnouncement = `Form validation failed. ${errorCount} ${errorCount === 1 ? 'error' : 'errors'} found. Please review and correct the highlighted fields.`;
            
            // Clear announcement after screen readers process it
            setTimeout(() => {
                this.validationAnnouncement = '';
            }, 2000);
        },
        
        async handleSubmit() {
            this.hasBeenSubmitted = true;
            
            // Validate all fields
            if (!this.validateAllFields()) {
                // Focus on first error field
                const firstErrorField = this.formElement.querySelector('[aria-invalid="true"]');
                if (firstErrorField) {
                    firstErrorField.focus();
                }
                return;
            }
            
            // If no submit endpoint is provided, just submit the form normally
            if (!this.submitEndpoint) {
                this.formElement.submit();
                return;
            }
            
            // AJAX submission
            this.isSubmitting = true;
            this.submitProgress = 0;
            
            try {
                // Simulate progress
                if (this.showProgressBar) {
                    this.simulateProgress();
                }
                
                const formData = new FormData(this.formElement);
                
                const response = await fetch(this.submitEndpoint, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });
                
                this.submitProgress = 100;
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    this.successMessage = result.message || 'Form submitted successfully!';
                    this.validationAnnouncement = 'Form submitted successfully!';
                    
                    // Redirect if specified
                    if (this.redirectAfterSuccess) {
                        setTimeout(() => {
                            window.location.href = this.redirectAfterSuccess;
                        }, 2000);
                    }
                } else {
                    // Handle server-side validation errors
                    if (result.errors) {
                        this.errors = result.errors;
                        this.updateAllFieldsUI();
                        this.announceValidationErrors();
                    } else {
                        this.globalErrors = [{ field: 'general', message: result.message || 'An error occurred while submitting the form.' }];
                    }
                }
            } catch (error) {
                this.globalErrors = [{ field: 'general', message: 'Network error. Please check your connection and try again.' }];
                console.error('Form submission error:', error);
            } finally {
                this.isSubmitting = false;
                setTimeout(() => {
                    this.submitProgress = 0;
                }, 1000);
            }
        },
        
        simulateProgress() {
            const intervals = [20, 40, 60, 80];
            intervals.forEach((progress, index) => {
                setTimeout(() => {
                    if (this.isSubmitting) {
                        this.submitProgress = progress;
                    }
                }, (index + 1) * 200);
            });
        },
        
        updateAllFieldsUI() {
            const formElements = this.formElement.querySelectorAll('input, select, textarea');
            formElements.forEach(element => {
                if (element.name) {
                    this.updateFieldUI(element);
                }
            });
        }
    }));
});
</script>

<style>
    .form-validation-wrapper .border-red-500 {
        border-color: #ef4444 !important;
    }
    
    .form-validation-wrapper .focus\:border-red-500:focus {
        border-color: #ef4444 !important;
    }
    
    .form-validation-wrapper .focus\:ring-red-500:focus {
        --tw-ring-color: rgb(239 68 68 / 0.5) !important;
    }
    
    .form-validation-wrapper .field-error {
        animation: fadeIn 0.3s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* High contrast mode support */
    @media (prefers-contrast: high) {
        .form-validation-wrapper .border-red-500 {
            border-color: #ff0000 !important;
            border-width: 2px !important;
        }
        
        .form-validation-wrapper .field-error {
            background-color: #ffebee;
            padding: 4px;
            border-radius: 4px;
        }
    }
</style>