<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): Response
    {
        return $this->isAdmin($user)
            ? Response::allow()
            : Response::deny('Admin privileges required to view users.');
    }

    /**
     * Determine whether the user can view the user list.
     */
    public function index(User $user): Response
    {
        return $this->isAdmin($user)
            ? Response::allow() 
            : Response::deny('Admin privileges required to access user management.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): Response
    {
        if ($this->isAdmin($user) || $user->id === $model->id) {
            return Response::allow();
        }
        
        return Response::deny('You can only view your own profile or require admin privileges.');
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): Response
    {
        return $this->isAdmin($user)
            ? Response::allow()
            : Response::deny('Admin privileges required to create users.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): Response
    {
        if ($this->isAdmin($user) || $user->id === $model->id) {
            return Response::allow();
        }
        
        return Response::deny('You can only edit your own profile or require admin privileges.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): Response
    {
        // Prevent users from deleting themselves
        if ($user->id === $model->id) {
            return Response::deny('You cannot delete your own account.');
        }
        
        return $this->isAdmin($user)
            ? Response::allow()
            : Response::deny('Admin privileges required to delete users.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): Response
    {
        return $this->isAdmin($user)
            ? Response::allow()
            : Response::deny('Admin privileges required to restore users.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): Response
    {
        return $this->isAdmin($user)
            ? Response::allow()
            : Response::deny('Admin privileges required to permanently delete users.');
    }

    /**
     * Check if user is an admin with null safety
     */
    private function isAdmin(User $user): bool
    {
        return $user->role && $user->role->name === 'admin';
    }

    /**
     * Backwards compatibility methods (using old names)
     */
    public function show(User $user, User $model): Response
    {
        return $this->view($user, $model);
    }

    public function destroy(User $user, User $model): Response
    {
        return $this->delete($user, $model);
    }
}