<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use App\Models\Role;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user is admin
        $user = $this->user();
        return $user && $user->role && $user->role->name === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $availableRoles = Role::pluck('name')->toArray();
        $roleValidation = empty($availableRoles) 
            ? 'required|string|in:admin,employee' 
            : 'required|string|in:' . implode(',', $availableRoles);

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/' // Only letters and spaces
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users,email'
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            'role' => $roleValidation,
            'department' => 'nullable|string|max:100|in:Web,Graphic,Editorial,Multimedia,Sales,Marketing,Intern,General',
            'position' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20|regex:/^[\+\-\(\)\s\d]+$/',
            'start_date' => 'nullable|date|after_or_equal:today',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The name may only contain letters and spaces.',
            'email.unique' => 'This email address is already registered.',
            'email.email' => 'Please provide a valid email address.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'role.required' => 'Please select a user role.',
            'role.in' => 'The selected role is invalid.',
            'phone.regex' => 'Please provide a valid phone number.',
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
            'password' => 'password',
            'role' => 'user role',
            'phone' => 'phone number',
            'start_date' => 'employment start date',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        abort(403, 'Only administrators can create new users.');
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
            'role' => strtolower(trim($this->role)),
        ]);
    }

    /**
     * Get validated data with additional processing
     */
    public function getValidatedData(): array
    {
        $data = $this->validated();
        
        // Set email as verified for admin-created accounts
        $data['email_verified_at'] = now();
        
        // Set default values based on role
        if (!isset($data['department'])) {
            $data['department'] = $data['role'] === 'admin' ? 'Admin' : 'General';
        }
        $data['start_date'] = $data['start_date'] ?? now()->toDateString();
        
        return $data;
    }
}