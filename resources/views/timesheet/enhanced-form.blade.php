@extends('layouts.app-ACCESSIBLE')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <i class="bi bi-calendar-week text-blue-600 mr-2" aria-hidden="true"></i>
                Timesheet Management
            </h1>
            <p class="text-gray-600">Track your work hours and tasks with our enhanced, accessible timesheet form.</p>
        </div>

        <!-- Enhanced Form with Validation -->
        <x-form-validator 
            form-id="timesheetForm"
            :rules="[
                'task_description' => ['required', ['min' => ['length' => 10]], ['max' => ['length' => 500]]],
                'start_time' => ['required'],
                'end_time' => ['required'],
                'break_duration' => ['numeric'],
                'task_category' => ['required'],
                'date' => ['required', 'date']
            ]"
            :messages="[
                'task_description.required' => 'Please describe the task you worked on',
                'task_description.min' => 'Task description must be at least 10 characters long',
                'task_description.max' => 'Task description cannot exceed 500 characters',
                'start_time.required' => 'Start time is required',
                'end_time.required' => 'End time is required',
                'break_duration.numeric' => 'Break duration must be a number (in minutes)',
                'task_category.required' => 'Please select a task category',
                'date.required' => 'Please select a date',
                'date.date' => 'Please enter a valid date'
            ]"
            submit-endpoint="{{ route('timesheet.store') }}"
            redirect-after-success="{{ route('timesheet.index') }}"
            :show-progress-bar="true">

            <div class="card-elevated">
                <form id="timesheetForm" class="space-y-6">
                    @csrf
                    
                    <!-- Date Selection -->
                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-lg font-semibold text-gray-900 px-2">Date Information</legend>
                        
                        <div class="form-group">
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">
                                Work Date <span class="text-red-500" aria-label="required">*</span>
                            </label>
                            <input type="date" 
                                   id="date" 
                                   name="date" 
                                   class="form-input block w-full"
                                   value="{{ old('date', date('Y-m-d')) }}"
                                   aria-describedby="date-help"
                                   required>
                            <p id="date-help" class="text-xs text-gray-500 mt-1">Select the date for this timesheet entry</p>
                        </div>
                    </fieldset>

                    <!-- Time Information -->
                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-lg font-semibold text-gray-900 px-2">Time Information</legend>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="form-group">
                                <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">
                                    Start Time <span class="text-red-500" aria-label="required">*</span>
                                </label>
                                <input type="time" 
                                       id="start_time" 
                                       name="start_time" 
                                       class="form-input block w-full"
                                       value="{{ old('start_time') }}"
                                       aria-describedby="start-time-help"
                                       required>
                                <p id="start-time-help" class="text-xs text-gray-500 mt-1">When did you start working?</p>
                            </div>

                            <div class="form-group">
                                <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">
                                    End Time <span class="text-red-500" aria-label="required">*</span>
                                </label>
                                <input type="time" 
                                       id="end_time" 
                                       name="end_time" 
                                       class="form-input block w-full"
                                       value="{{ old('end_time') }}"
                                       aria-describedby="end-time-help"
                                       required>
                                <p id="end-time-help" class="text-xs text-gray-500 mt-1">When did you finish working?</p>
                            </div>

                            <div class="form-group">
                                <label for="break_duration" class="block text-sm font-medium text-gray-700 mb-1">
                                    Break Duration (minutes)
                                </label>
                                <input type="number" 
                                       id="break_duration" 
                                       name="break_duration" 
                                       min="0" 
                                       max="480"
                                       class="form-input block w-full"
                                       value="{{ old('break_duration', 0) }}"
                                       aria-describedby="break-help">
                                <p id="break-help" class="text-xs text-gray-500 mt-1">Total break time in minutes (optional)</p>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Task Information -->
                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-lg font-semibold text-gray-900 px-2">Task Information</legend>
                        
                        <div class="space-y-4">
                            <div class="form-group">
                                <label for="task_category" class="block text-sm font-medium text-gray-700 mb-1">
                                    Task Category <span class="text-red-500" aria-label="required">*</span>
                                </label>
                                <select id="task_category" 
                                        name="task_category" 
                                        class="form-input block w-full"
                                        aria-describedby="category-help"
                                        required>
                                    <option value="">-- Select a category --</option>
                                    <option value="development" {{ old('task_category') == 'development' ? 'selected' : '' }}>Development</option>
                                    <option value="design" {{ old('task_category') == 'design' ? 'selected' : '' }}>Design</option>
                                    <option value="testing" {{ old('task_category') == 'testing' ? 'selected' : '' }}>Testing</option>
                                    <option value="documentation" {{ old('task_category') == 'documentation' ? 'selected' : '' }}>Documentation</option>
                                    <option value="meeting" {{ old('task_category') == 'meeting' ? 'selected' : '' }}>Meeting</option>
                                    <option value="research" {{ old('task_category') == 'research' ? 'selected' : '' }}>Research</option>
                                    <option value="maintenance" {{ old('task_category') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="other" {{ old('task_category') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                <p id="category-help" class="text-xs text-gray-500 mt-1">Choose the category that best describes your work</p>
                            </div>

                            <div class="form-group">
                                <label for="task_description" class="block text-sm font-medium text-gray-700 mb-1">
                                    Task Description <span class="text-red-500" aria-label="required">*</span>
                                </label>
                                <textarea id="task_description" 
                                          name="task_description" 
                                          rows="4" 
                                          class="form-input block w-full"
                                          placeholder="Describe the work you completed during this time period..."
                                          aria-describedby="description-help description-counter"
                                          required>{{ old('task_description') }}</textarea>
                                <div class="flex justify-between items-center mt-1">
                                    <p id="description-help" class="text-xs text-gray-500">
                                        Provide a detailed description of your work (10-500 characters)
                                    </p>
                                    <p id="description-counter" 
                                       class="text-xs text-gray-500"
                                       x-data="{ count: 0 }"
                                       x-init="count = $refs.textarea.value.length"
                                       x-ref="counter">
                                        <span x-text="count" x-ref="textarea" 
                                              @input="count = $event.target.value.length"
                                              x-bind:textarea="$refs.textarea = $event.target"></span>/500
                                    </p>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Additional Information -->
                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-lg font-semibold text-gray-900 px-2">Additional Information</legend>
                        
                        <div class="form-group">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                Additional Notes (Optional)
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      class="form-input block w-full"
                                      placeholder="Any additional notes, challenges, or important information..."
                                      aria-describedby="notes-help">{{ old('notes') }}</textarea>
                            <p id="notes-help" class="text-xs text-gray-500 mt-1">Optional field for any additional context or notes</p>
                        </div>
                    </fieldset>

                    <!-- Submit Section -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
                        <button type="submit" 
                                class="btn-primary flex-1 sm:flex-none px-6 py-3 text-center"
                                :disabled="isSubmitting"
                                x-bind:aria-label="isSubmitting ? 'Submitting timesheet...' : 'Submit timesheet entry'">
                            <span x-show="!isSubmitting" class="flex items-center justify-center">
                                <i class="bi bi-check-circle mr-2" aria-hidden="true"></i>
                                Submit Timesheet
                            </span>
                            <span x-show="isSubmitting" class="flex items-center justify-center">
                                <i class="bi bi-arrow-clockwise animate-spin mr-2" aria-hidden="true"></i>
                                Submitting...
                            </span>
                        </button>
                        
                        <a href="{{ route('timesheet.index') }}" 
                           class="btn-secondary px-6 py-3 text-center">
                            <i class="bi bi-arrow-left mr-2" aria-hidden="true"></i>
                            Cancel
                        </a>
                        
                        <button type="button" 
                                onclick="document.getElementById('timesheetForm').reset(); location.reload();" 
                                class="btn-secondary px-6 py-3"
                                aria-label="Clear form and start over">
                            <i class="bi bi-arrow-clockwise mr-2" aria-hidden="true"></i>
                            Reset Form
                        </button>
                    </div>
                </form>
            </div>
        </x-form-validator>

        <!-- Help Section -->
        <div class="mt-8 card-elevated bg-blue-50">
            <h2 class="text-lg font-semibold text-blue-900 mb-3">
                <i class="bi bi-info-circle mr-2" aria-hidden="true"></i>
                Tips for Accurate Timesheet Entry
            </h2>
            <ul class="text-blue-800 space-y-2 text-sm" role="list">
                <li class="flex items-start">
                    <i class="bi bi-check-circle-fill mr-2 mt-1 text-blue-600 flex-shrink-0" aria-hidden="true"></i>
                    Be specific in your task descriptions to help with project tracking
                </li>
                <li class="flex items-start">
                    <i class="bi bi-check-circle-fill mr-2 mt-1 text-blue-600 flex-shrink-0" aria-hidden="true"></i>
                    Include break times for accurate hour calculations
                </li>
                <li class="flex items-start">
                    <i class="bi bi-check-circle-fill mr-2 mt-1 text-blue-600 flex-shrink-0" aria-hidden="true"></i>
                    Choose the most appropriate category for better reporting
                </li>
                <li class="flex items-start">
                    <i class="bi bi-check-circle-fill mr-2 mt-1 text-blue-600 flex-shrink-0" aria-hidden="true"></i>
                    Submit your timesheet entries daily for best accuracy
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Character Counter Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('task_description');
    const counter = document.getElementById('description-counter');
    
    if (textarea && counter) {
        textarea.addEventListener('input', function() {
            const count = this.value.length;
            counter.querySelector('span').textContent = count;
            
            // Visual feedback for character limits
            if (count < 10) {
                counter.className = 'text-xs text-amber-600';
            } else if (count > 450) {
                counter.className = 'text-xs text-red-600';
            } else {
                counter.className = 'text-xs text-green-600';
            }
        });
    }
});
</script>
@endsection