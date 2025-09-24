<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'is_auto_clock_out',
        'notes',
        'session_order',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'is_auto_clock_out' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workSummaries()
    {
        return $this->hasMany(WorkSummary::class, 'related_id')->where('type', 'session');
    }

    // Accessors
    public function getDurationInMinutesAttribute()
    {
        if (!$this->clock_in) {
            return 0;
        }

        $clockOut = $this->clock_out ?: Carbon::now();
        return $this->clock_in->diffInMinutes($clockOut);
    }

    public function getDurationFormattedAttribute()
    {
        $minutes = $this->duration_in_minutes;
        $hours = intval($minutes / 60);
        $mins = $minutes % 60;
        
        return sprintf('%02d:%02d', $hours, $mins);
    }

    public function getStatusAttribute()
    {
        if (!$this->clock_in) {
            return 'not_started';
        }
        
        if (!$this->clock_out) {
            return 'active';
        }
        
        return 'completed';
    }

    public function getStatusMessageAttribute()
    {
        switch ($this->status) {
            case 'not_started':
                return 'Session not started';
            case 'active':
                return 'Session active (Duration: ' . $this->duration_formatted . ')' . 
                       ($this->is_auto_clock_out ? ' - Auto Clock-Out pending' : '');
            case 'completed':
                return 'Session completed (Duration: ' . $this->duration_formatted . ')' .
                       ($this->is_auto_clock_out ? ' - Auto Clock-Out (system added)' : '');
            default:
                return 'Unknown status';
        }
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeActive($query)
    {
        return $query->whereNotNull('clock_in')->whereNull('clock_out');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('clock_in')->whereNotNull('clock_out');
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeOrderedBySession($query)
    {
        return $query->orderBy('date', 'asc')->orderBy('session_order', 'asc');
    }

    // Static methods
    public static function getNextSessionOrder($userId, $date)
    {
        return static::forUser($userId)->forDate($date)->max('session_order') + 1;
    }

    public static function autoClockOutMissingSessions()
    {
        $yesterday = Carbon::yesterday();
        $endOfDay = $yesterday->copy()->endOfDay();
        
        $activeSessions = static::active()
            ->where('date', '<', Carbon::today())
            ->get();
            
        foreach ($activeSessions as $session) {
            $session->update([
                'clock_out' => $session->date->copy()->setTime(23, 59, 0),
                'is_auto_clock_out' => true,
                'notes' => ($session->notes ? $session->notes . "\n" : '') . 'Auto Clock-Out (system added)'
            ]);
        }
        
        return $activeSessions->count();
    }
}
