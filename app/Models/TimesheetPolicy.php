<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetPolicy extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'max_daily_hours',
        'max_weekly_hours',
        'require_approval',
        'overtime_threshold',
        'allow_retroactive_entries',
        'retroactive_limit_days'
    ];
    
    protected $casts = [
        'max_daily_hours' => 'decimal:2',
        'max_weekly_hours' => 'decimal:2',
        'require_approval' => 'boolean',
        'overtime_threshold' => 'decimal:2',
        'allow_retroactive_entries' => 'boolean',
        'retroactive_limit_days' => 'integer'
    ];
    
    /**
     * Users assigned to this policy
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_policies')->withTimestamps();
    }
    
    /**
     * Check if hours exceed overtime threshold
     */
    public function isOvertime($hours)
    {
        return $hours > $this->overtime_threshold;
    }
    
    /**
     * Check if entry is within retroactive limit
     */
    public function isWithinRetroactiveLimit($date)
    {
        if (!$this->allow_retroactive_entries) {
            return false;
        }
        
        $entryDate = \Carbon\Carbon::parse($date);
        $limitDate = now()->subDays($this->retroactive_limit_days);
        
        return $entryDate->greaterThanOrEqualTo($limitDate);
    }
}
