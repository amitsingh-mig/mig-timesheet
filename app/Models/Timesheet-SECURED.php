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
        'expected_hours' => 'decimal:2',
        'break_duration' => 'decimal:2',
    ];

    /**
     * Boot method to automatically set user modifications
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($timesheet) {
            $timesheet->last_modified_by = auth()->id();
            $timesheet->status = 'draft';
        });

        static::updating(function ($timesheet) {
            $timesheet->last_modified_by = auth()->id();
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lastModifiedBy()
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    public function approvals()
    {
        return $this->hasMany(TimesheetApproval::class);
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
}