<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task',
        'hours',
        'date',
        'start_time',
        'end_time',
        'hours_worked',
        'description',
        'status',
        'location',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'is_overtime',
        'expected_hours',
        'break_duration',
        'project_id',
        'notes',
        'submitted_at',
        'last_modified_by',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'hours_worked' => 'decimal:2',
        'start_time' => 'string',
        'end_time' => 'string',
        'approved_at' => 'datetime',
        'submitted_at' => 'datetime',
        'is_overtime' => 'boolean',
        'expected_hours' => 'decimal:2',
        'break_duration' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvals()
    {
        return $this->hasMany(TimesheetApproval::class);
    }

    /**
     * Get formatted hours for display
     */
    public function getFormattedHoursAttribute()
    {
        if ($this->hours_worked) {
            return number_format($this->hours_worked, 1);
        }
        
        if ($this->hours && is_string($this->hours)) {
            // Convert HH:MM format to decimal
            [$h, $m] = explode(':', $this->hours);
            return number_format(intval($h) + (intval($m) / 60), 1);
        }
        
        return '0.0';
    }

    /**
     * Get task description (backward compatibility)
     */
    public function getTaskDescriptionAttribute()
    {
        return $this->description ?: $this->task;
    }
}

