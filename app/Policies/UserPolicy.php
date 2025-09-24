<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
 

class UserPolicy
{
    use HandlesAuthorization;
    
    public function index(User $user)
    {
        if($user->role->name == 'admin'){
            return Response::allow(); 
        }
        return Response::deny('user not authorized');
    }

    public function show(User $user, User $model ) 
    {
        if($user->role->name == 'admin' || $user->id == $model->id){
            return Response::allow();
        }
        return Response::deny('user not authorized');
    }

    public function update(User $user, User $model ) 
    {
        if($user->role->name == 'admin' || $user->id == $model->id){
            return Response::allow();
        }
        return Response::deny('user not authorized');  
    }


    public function destroy(User $user, User $model )
    {
        if($user->role->name == 'admin' || $user->id == $model->id){
            return Response::allow(); 
        }
        return Response::deny('user not authorized');
    }

}
