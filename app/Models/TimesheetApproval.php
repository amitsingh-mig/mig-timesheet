<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetApproval extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'timesheet_id',
        'admin_id',
        'action',
        'reason'
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Get the timesheet that was approved/rejected
     */
    public function timesheet()
    {
        return $this->belongsTo(Timesheet::class);
    }
    
    /**
     * Get the admin who performed the action
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
