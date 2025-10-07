<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('report', function (User $user, $user_id_report = null) {
            // Null safety check for role
            if (!$user->role) {
                return false;
            }
            
            // Admin can view any report, users can view their own
            return $user->role->name === 'admin' || $user->id == $user_id_report;
        });

        Gate::define('admin', function (User $user) {
            return $user->role && $user->role->name === 'admin';
        });

        // Additional security gates
        Gate::define('manage-users', function (User $user) {
            return $user->role && $user->role->name === 'admin';
        });

        Gate::define('view-timesheets', function (User $user, $targetUserId = null) {
            if (!$user->role) {
                return false;
            }
            
            // Admin can view any timesheet, users can view their own
            return $user->role->name === 'admin' || $user->id == $targetUserId;
        });

        Gate::define('approve-timesheets', function (User $user) {
            return $user->role && $user->role->name === 'admin';
        });
    }
}