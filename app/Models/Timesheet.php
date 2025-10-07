<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Timesheet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Removed sensitive approval fields to prevent unauthorized modifications
     */
    protected $fillable = [
        'user_id',
        'task',
        'hours',
        'date',
        'start_time',
        'end_time',
        'hours_worked',
        'description',
        'location',
        'is_overtime',
        'overtime_hours',
        'expected_hours',
        'break_duration',
        'project_id',
        'notes',
        'submitted_at',
    ];

    /**
     * The attributes that should be guarded from mass assignment.
     * These should only be updated through specific methods
     */
    protected $guarded = [
        'status',           // Should be updated through workflow methods
        'approved_by',      // Should only be set by admins
        'approved_at',      // Should only be set by admins
        'rejection_reason', // Should only be set by admins
        'last_modified_by', // Should be automatically set
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'hours_worked' => 'decimal:2',
        'start_time' => 'string',
        'end_time' => 'string',
        'approved_at' => 'datetime',
        'submitted_at' => 'datetime',
        'is_overtime' => 'boolean',
        'overtime_hours' => 'decimal:2',
        'expected_hours' => 'decimal:2',
        'break_duration' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the approver user
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get timesheet approvals
     */
    public function approvals()
    {
        return $this->hasMany(TimesheetApproval::class);
    }

    /**
     * Calculate overtime based on hours worked and expected hours
     */
    public function calculateOvertime()
    {
        $hoursWorked = $this->hours_worked ?? $this->hours ?? 0;
        $expectedHours = $this->expected_hours ?? 8.0; // Default 8 hours per day
        
        // Check if hours worked exceed expected hours
        $this->is_overtime = $hoursWorked > $expectedHours;
        
        // Calculate overtime hours
        $this->overtime_hours = $this->is_overtime ? max(0, $hoursWorked - $expectedHours) : 0;
        
        return $this;
    }

    /**
     * Get overtime hours for this timesheet
     */
    public function getOvertimeHoursAttribute()
    {
        if (!$this->is_overtime) {
            return 0;
        }
        
        $hoursWorked = $this->hours_worked ?? $this->hours ?? 0;
        $expectedHours = $this->expected_hours ?? 8.0;
        
        return max(0, $hoursWorked - $expectedHours);
    }

    /**
     * Get regular hours (non-overtime)
     */
    public function getRegularHoursAttribute()
    {
        $hoursWorked = $this->hours_worked ?? $this->hours ?? 0;
        $expectedHours = $this->expected_hours ?? 8.0;
        
        return min($hoursWorked, $expectedHours);
    }

    /**
     * Check if timesheet requires overtime approval
     */
    public function requiresOvertimeApproval()
    {
        return $this->is_overtime && $this->overtime_hours > 0;
    }

    /**
     * Get overtime rate multiplier (1.5x for overtime)
     */
    public function getOvertimeRateAttribute()
    {
        return 1.5; // Standard overtime rate
    }

    /**
     * Calculate total pay including overtime
     */
    public function calculateTotalPay($hourlyRate = 0)
    {
        if ($hourlyRate <= 0) {
            return 0;
        }
        
        $regularPay = $this->regular_hours * $hourlyRate;
        $overtimePay = $this->overtime_hours * $hourlyRate * $this->overtime_rate;
        
        return $regularPay + $overtimePay;
    }

    /**
     * Boot method to automatically set user modifications and calculate overtime
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($timesheet) {
            // Calculate overtime automatically
            $timesheet->calculateOvertime();
            $timesheet->last_modified_by = auth()->id();
            // Don't set status here - let it use the database default
        });

        static::updating(function ($timesheet) {
            $timesheet->last_modified_by = auth()->id();
        });
    }

    public function lastModifiedBy()
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    /**
     * Scope for user's own timesheets
     */
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for pending approval timesheets
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope for approved timesheets
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * SECURE METHODS - Only these should modify approval fields
     */

    /**
     * Submit timesheet for approval (can only be done by owner)
     */
    public function submit()
    {
        if ($this->user_id !== auth()->id()) {
            throw new \Exception('You can only submit your own timesheets.');
        }

        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    /**
     * Approve timesheet (admin only)
     */
    public function approve($adminUserId, $notes = null)
    {
        $admin = User::find($adminUserId);
        if (!$admin->role || $admin->role->name !== 'admin') {
            throw new \Exception('Only administrators can approve timesheets.');
        }

        $this->forceFill([
            'status' => 'approved',
            'approved_by' => $adminUserId,
            'approved_at' => now(),
            'rejection_reason' => null,
            'notes' => $notes ? $this->notes . "\nApproval notes: " . $notes : $this->notes,
        ])->save();
    }

    /**
     * Reject timesheet (admin only)
     */
    public function reject($adminUserId, $reason)
    {
        $admin = User::find($adminUserId);
        if (!$admin->role || $admin->role->name !== 'admin') {
            throw new \Exception('Only administrators can reject timesheets.');
        }

        $this->forceFill([
            'status' => 'rejected',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => $reason,
        ])->save();
    }

    /**
     * Check if timesheet can be edited
     */
    public function canBeEditedBy($userId)
    {
        // Admin can edit any timesheet
        $user = User::find($userId);
        if ($user->role && $user->role->name === 'admin') {
            return true;
        }

        // Owner can only edit draft or rejected timesheets
        return $this->user_id === $userId && in_array($this->status, ['draft', 'rejected']);
    }

    /**
     * Check if timesheet is editable
     */
    public function getIsEditableAttribute()
    {
        return $this->canBeEditedBy(auth()->id());
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

