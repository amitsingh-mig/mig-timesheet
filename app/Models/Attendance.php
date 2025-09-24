<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'clock_in',
        'clock_out',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Calculate total work duration in minutes
     */
    public function getTotalMinutesAttribute()
    {
        if (!$this->clock_in) {
            return 0;
        }

        $clockOut = $this->clock_out ?: Carbon::now();
        
        return $this->clock_in->diffInMinutes($clockOut);
    }

    /**
     * Get formatted total hours (HH:MM)
     */
    public function getTotalHoursAttribute()
    {
        $minutes = $this->total_minutes;
        $hours = intval($minutes / 60);
        $mins = $minutes % 60;
        
        return sprintf('%02d:%02d', $hours, $mins);
    }

    /**
     * Check if clock out is missing
     */
    public function getIsMissingClockOutAttribute()
    {
        return $this->clock_in && !$this->clock_out;
    }

    /**
     * Get attendance status
     */
    public function getStatusAttribute()
    {
        if (!$this->clock_in) {
            return 'Not Clocked In';
        }
        
        if (!$this->clock_out) {
            return 'Clocked In';
        }
        
        return 'Complete';
    }

    /**
     * Get attendance status message with duration
     */
    public function getStatusMessageAttribute()
    {
        switch ($this->status) {
            case 'Not Clocked In':
                return 'No clock-in recorded';
            case 'Clocked In':
                return 'Clock-out not recorded (Duration: ' . $this->total_hours . ')';
            case 'Complete':
                return 'Complete (Duration: ' . $this->total_hours . ')';
            default:
                return 'Unknown status';
        }
    }

    /**
     * Scope for getting attendance within date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for getting attendance for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for incomplete attendance (missing clock out)
     */
    public function scopeIncomplete($query)
    {
        return $query->whereNotNull('clock_in')->whereNull('clock_out');
    }
    
    /**
     * Get clock in time formatted for display
     */
    public function getFormattedClockInAttribute()
    {
        if (!$this->clock_in) {
            return null;
        }
        
        return $this->clock_in->setTimezone(config('app.timezone', 'UTC'))->format('g:i A');
    }
    
    /**
     * Get clock out time formatted for display
     */
    public function getFormattedClockOutAttribute()
    {
        if (!$this->clock_out) {
            return null;
        }
        
        return $this->clock_out->setTimezone(config('app.timezone', 'UTC'))->format('g:i A');
    }
    
    /**
     * Get current work duration in real time (for active sessions)
     */
    public function getCurrentDurationAttribute()
    {
        if (!$this->clock_in) {
            return '00:00';
        }
        
        $clockOut = $this->clock_out ?: Carbon::now();
        $minutes = $this->clock_in->diffInMinutes($clockOut);
        $hours = intval($minutes / 60);
        $mins = $minutes % 60;
        
        return sprintf('%02d:%02d', $hours, $mins);
    }
}
