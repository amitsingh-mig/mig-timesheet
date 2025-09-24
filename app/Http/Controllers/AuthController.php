<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        // validate the request
        $validator = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // create a new user
        $user = User::create([
            'name' => $validator['name'],
            'email' => $validator['email'],
            'password' => Hash::make($validator['password']),
            'role_id' => 2,
        ]);

        //assign role to the user
        $employee_role = Role::where('name', 'employee')->first();
        $employee_role->users()->save($user);

        //create token for the user
        $token = $user->createToken('api_token')->plainTextToken;

        //return response
        $response = [
            'message' => 'User created successfully',
            'user' => $user,
            'access_token' => $token,
        ];

        return response()->json($response, 201);
    }

    public function login(Request $request)
    {
        //validate the request
        $validator = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        //attempt to authenticate
        if (Auth::attempt($validator)) {
            //get user
            $user = User::where('email', $validator['email'])->first();
            //token generated
            $token = $user->createToken('api_token')->plainTextToken;
            //return response with token
            $response = [
                'data' => [
                    'access_token' => $token
                ]
            ];
            return response()->json($response);
        } else {
            return response()->json(['massage' => 'Unauthenticated'], 401);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(null, 204);
    }

    public function resetPassword(Request $request)
{
    // validate
    $validator = $request->validate([
        'password' => 'required|min:8',
    ]);

    // get user
    $user = $request->user();

    // change password
    $user->password = Hash::make($validator['password']);
    $user->save();

    // delete all tokens (logout from everywhere)
    $user->tokens()->delete(); // ✅ FIX HERE

    // return response 
    return response()->json([
        'message' => 'Password changed successfully' // ✅ also corrected spelling
    ], 200);
}



}
