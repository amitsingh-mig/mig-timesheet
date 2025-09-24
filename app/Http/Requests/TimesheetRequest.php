<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;
use App\Models\Timesheet;

class TimesheetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        // If updating existing timesheet
        if ($this->route('timesheet') || $this->route('id')) {
            $timesheetId = $this->route('timesheet') ?? $this->route('id');
            $timesheet = Timesheet::find($timesheetId);
            
            if (!$timesheet) {
                return false;
            }
            
            return $timesheet->canBeEditedBy($user->id);
        }
        
        // For creating new timesheet, user must be authenticated
        return $user !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $isUpdate = $this->route('timesheet') || $this->route('id');
        
        return [
            'date' => [
                'required',
                'date',
                'before_or_equal:today',
                'after:' . now()->subDays(30)->toDateString() // Can't add entries older than 30 days
            ],
            'task' => 'nullable|string|max:255', // Legacy support
            'description' => 'required|string|max:1000|min:10',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'hours_worked' => [
                'nullable',
                'numeric',
                'min:0.25', // Minimum 15 minutes
                'max:24',   // Maximum 24 hours per entry
            ],
            'break_duration' => [
                'nullable',
                'numeric',
                'min:0',
                'max:8', // Maximum 8 hours break
            ],
            'location' => 'nullable|string|max:100',
            'project_id' => 'nullable|integer|exists:projects,id',
            'is_overtime' => 'boolean',
            'notes' => 'nullable|string|max:500',
            
            // Legacy format support
            'hours' => 'nullable|string|regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date.before_or_equal' => 'Cannot create timesheets for future dates.',
            'date.after' => 'Cannot create timesheets older than 30 days.',
            'description.required' => 'Please provide a description of your work.',
            'description.min' => 'Work description must be at least 10 characters.',
            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Start time must be in HH:MM format.',
            'end_time.required' => 'End time is required.',
            'end_time.date_format' => 'End time must be in HH:MM format.',
            'end_time.after' => 'End time must be after start time.',
            'hours_worked.min' => 'Minimum work duration is 15 minutes (0.25 hours).',
            'hours_worked.max' => 'Maximum work duration is 24 hours per entry.',
            'break_duration.max' => 'Break duration cannot exceed 8 hours.',
            'project_id.exists' => 'Selected project does not exist.',
            'hours.regex' => 'Hours must be in HH:MM format.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'date' => 'work date',
            'description' => 'work description',
            'start_time' => 'start time',
            'end_time' => 'end time',
            'hours_worked' => 'hours worked',
            'break_duration' => 'break duration',
            'project_id' => 'project',
            'is_overtime' => 'overtime indicator',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $data = [];
        
        // Trim string fields
        if ($this->has('description')) {
            $data['description'] = trim($this->description);
        }
        
        if ($this->has('location')) {
            $data['location'] = trim($this->location);
        }
        
        if ($this->has('notes')) {
            $data['notes'] = trim($this->notes);
        }
        
        // Calculate hours_worked from start_time and end_time if not provided
        if ($this->has('start_time') && $this->has('end_time') && !$this->has('hours_worked')) {
            try {
                $startTime = Carbon::createFromFormat('H:i', $this->start_time);
                $endTime = Carbon::createFromFormat('H:i', $this->end_time);
                
                // Handle overnight work (end time next day)
                if ($endTime->lessThan($startTime)) {
                    $endTime->addDay();
                }
                
                $hoursWorked = $endTime->diffInMinutes($startTime) / 60;
                
                // Subtract break duration if provided
                if ($this->has('break_duration') && is_numeric($this->break_duration)) {
                    $hoursWorked -= (float)$this->break_duration;
                }
                
                $data['hours_worked'] = round($hoursWorked, 2);
            } catch (\Exception $e) {
                // Let validation handle invalid time formats
            }
        }
        
        // Convert legacy hours format to hours_worked
        if ($this->has('hours') && preg_match('/^(\d{1,2}):(\d{2})$/', $this->hours, $matches)) {
            $hours = (int)$matches[1];
            $minutes = (int)$matches[2];
            $data['hours_worked'] = $hours + ($minutes / 60);
        }
        
        // Set is_overtime default
        $data['is_overtime'] = $this->boolean('is_overtime');
        
        $this->merge($data);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check for duplicate entries on the same date
            $this->checkDuplicateEntries($validator);
            
            // Validate work duration limits
            $this->validateWorkDuration($validator);
            
            // Check if timesheet date conflicts with holidays/weekends (if business rule)
            $this->validateWorkingDay($validator);
        });
    }

    /**
     * Check for duplicate timesheet entries
     */
    private function checkDuplicateEntries($validator)
    {
        if (!$this->date || !$this->start_time) {
            return;
        }
        
        $query = Timesheet::where('user_id', $this->user()->id)
            ->where('date', $this->date)
            ->where('start_time', $this->start_time);
            
        // Exclude current timesheet when updating
        if ($timesheetId = $this->route('timesheet') ?? $this->route('id')) {
            $query->where('id', '!=', $timesheetId);
        }
        
        if ($query->exists()) {
            $validator->errors()->add(
                'start_time',
                'You already have a timesheet entry for this date and time.'
            );
        }
    }

    /**
     * Validate total work duration for the day
     */
    private function validateWorkDuration($validator)
    {
        if (!$this->date || !$this->hours_worked) {
            return;
        }
        
        $query = Timesheet::where('user_id', $this->user()->id)
            ->where('date', $this->date);
            
        // Exclude current timesheet when updating
        if ($timesheetId = $this->route('timesheet') ?? $this->route('id')) {
            $query->where('id', '!=', $timesheetId);
        }
        
        $existingHours = $query->sum('hours_worked');
        $totalHours = $existingHours + $this->hours_worked;
        
        // Business rule: Maximum 16 hours per day (including overtime)
        if ($totalHours > 16) {
            $validator->errors()->add(
                'hours_worked',
                sprintf(
                    'Total work hours for this date would be %.2f hours. Maximum allowed is 16 hours per day.',
                    $totalHours
                )
            );
        }
        
        // Warn for excessive hours (over 12 hours)
        if ($totalHours > 12 && $totalHours <= 16) {
            // Add a warning (you might want to implement a warning system)
            $validator->errors()->add(
                'hours_worked',
                sprintf(
                    'Warning: Total work hours for this date will be %.2f hours. Please ensure overtime is approved.',
                    $totalHours
                )
            );
        }
    }

    /**
     * Validate working day (business rule)
     */
    private function validateWorkingDay($validator)
    {
        if (!$this->date) {
            return;
        }
        
        $date = Carbon::parse($this->date);
        
        // Skip validation for admins (they can override)
        if ($this->user()->role && $this->user()->role->name === 'admin') {
            return;
        }
        
        // Check if it's a weekend (this could be made configurable)
        if ($date->isWeekend()) {
            $validator->after(function ($validator) use ($date) {
                $validator->errors()->add(
                    'date',
                    'Timesheet entry for weekend (' . $date->format('l, M j, Y') . ') requires manager approval.'
                );
            });
        }
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        abort(403, 'You are not authorized to create/modify this timesheet entry.');
    }

    /**
     * Get processed validated data
     */
    public function getProcessedData(): array
    {
        $data = $this->validated();
        
        // Ensure user_id is set
        $data['user_id'] = $this->user()->id;
        
        // Set status for new entries
        if (!$this->route('timesheet') && !$this->route('id')) {
            $data['status'] = 'draft';
        }
        
        // Convert legacy task field to description if needed
        if (empty($data['description']) && !empty($data['task'])) {
            $data['description'] = $data['task'];
        }
        
        // Remove legacy fields that shouldn't be saved
        unset($data['task'], $data['hours']);
        
        return $data;
    }
}