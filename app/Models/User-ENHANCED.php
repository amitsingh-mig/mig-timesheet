<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the user's role
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Reliable hasRole() method with multiple role support
     */
    public function hasRole($role): bool
    {
        // Handle null role gracefully
        if (!$this->role) {
            return false;
        }

        // Handle single role check
        if (is_string($role)) {
            return $this->role->name === $role;
        }

        // Handle array of roles
        if (is_array($role)) {
            return in_array($this->role->name, $role);
        }

        return false;
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is employee
     */
    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }

    /**
     * Check if user can access admin features
     */
    public function canAccessAdmin(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage other users
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Get user's role name safely
     */
    public function getRoleName(): string
    {
        return $this->role ? $this->role->name : 'none';
    }

    /**
     * Get user's display role for UI
     */
    public function getDisplayRole(): string
    {
        return match($this->getRoleName()) {
            'admin' => 'Administrator',
            'employee' => 'Employee',
            'manager' => 'Manager',
            default => 'User'
        };
    }

    /**
     * Check if user can view specific user data
     */
    public function canView(User $otherUser): bool
    {
        // Admins can view anyone
        if ($this->isAdmin()) {
            return true;
        }

        // Users can view their own data
        return $this->id === $otherUser->id;
    }

    /**
     * Check if user can edit specific user data
     */
    public function canEdit(User $otherUser): bool
    {
        // Admins can edit anyone except they can't delete themselves
        if ($this->isAdmin()) {
            return true;
        }

        // Users can edit their own data
        return $this->id === $otherUser->id;
    }

    /**
     * Check if user can delete specific user
     */
    public function canDelete(User $otherUser): bool
    {
        // Only admins can delete users
        if (!$this->isAdmin()) {
            return false;
        }

        // Admins cannot delete themselves
        return $this->id !== $otherUser->id;
    }

    // Relationships
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function workSummaries()
    {
        return $this->hasMany(WorkSummary::class);
    }

    /**
     * Scope for admins only
     */
    public function scopeAdmins($query)
    {
        return $query->whereHas('role', function ($q) {
            $q->where('name', 'admin');
        });
    }

    /**
     * Scope for employees only
     */
    public function scopeEmployees($query)
    {
        return $query->whereHas('role', function ($q) {
            $q->where('name', 'employee');
        });
    }

    /**
     * Scope for users with specific role
     */
    public function scopeWithRole($query, $roleName)
    {
        return $query->whereHas('role', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Get user's theme preference based on role
     */
    public function getThemePreference(): string
    {
        return match($this->getRoleName()) {
            'admin' => 'admin-theme',
            'employee' => 'employee-theme',
            'manager' => 'manager-theme',
            default => 'default-theme'
        };
    }

    /**
     * Get user's primary color for UI theming
     */
    public function getPrimaryColor(): string
    {
        return match($this->getRoleName()) {
            'admin' => 'red',
            'employee' => 'green', 
            'manager' => 'blue',
            default => 'gray'
        };
    }

    /**
     * Get user's sidebar menu items based on role
     */
    public function getSidebarMenuItems(): array
    {
        $items = [
            'common' => [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
                ['route' => 'profile.edit', 'label' => 'My Profile', 'icon' => 'user'],
            ]
        ];

        if ($this->isAdmin()) {
            $items['admin'] = [
                ['route' => 'admin.dashboard', 'label' => 'Admin Dashboard', 'icon' => 'shield'],
                ['route' => 'admin.users.index', 'label' => 'User Management', 'icon' => 'users'],
                ['route' => 'admin.employees.time.view', 'label' => 'Employee Time', 'icon' => 'clock'],
                ['route' => 'admin.timesheet.calendar', 'label' => 'Timesheet Calendar', 'icon' => 'calendar'],
            ];
        }

        if ($this->isEmployee() || $this->isAdmin()) {
            $items['employee'] = [
                ['route' => 'timesheet.index', 'label' => 'My Timesheets', 'icon' => 'document'],
                ['route' => 'attendance.index', 'label' => 'My Attendance', 'icon' => 'clock'],
                ['route' => 'daily-update.index', 'label' => 'Daily Updates', 'icon' => 'note'],
            ];
        }

        return $items;
    }
}