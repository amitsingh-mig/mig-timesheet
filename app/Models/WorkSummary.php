<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'summary',
        'type',
        'related_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceSession()
    {
        return $this->belongsTo(AttendanceSession::class, 'related_id');
    }

    public function timesheet()
    {
        return $this->belongsTo(Timesheet::class, 'related_id');
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

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // Static methods
    public static function createDailySummary($userId, $date, $summary)
    {
        return static::create([
            'user_id' => $userId,
            'date' => $date,
            'summary' => $summary,
            'type' => 'daily',
        ]);
    }

    public static function createSessionSummary($userId, $date, $summary, $sessionId)
    {
        return static::create([
            'user_id' => $userId,
            'date' => $date,
            'summary' => $summary,
            'type' => 'session',
            'related_id' => $sessionId,
        ]);
    }

    public static function createTaskSummary($userId, $date, $summary, $taskId)
    {
        return static::create([
            'user_id' => $userId,
            'date' => $date,
            'summary' => $summary,
            'type' => 'task',
            'related_id' => $taskId,
        ]);
    }
}
