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
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ]);

        // Get employee role dynamically
        $employeeRole = Role::where('name', 'employee')->first();
        if (!$employeeRole) {
            return response()->json([
                'message' => 'Employee role not found. Please contact administrator.'
            ], 500);
        }

        // create a new user
        $user = User::create([
            'name' => $validator['name'],
            'email' => $validator['email'],
            'password' => Hash::make($validator['password']),
            'role_id' => $employeeRole->id,
        ]);

        //create token for the user
        $token = $user->createToken('api_token')->plainTextToken;

        //return response (don't expose full user object)
        $response = [
            'message' => 'User created successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name ?? 'employee'
            ],
            'access_token' => $token,
        ];

        return response()->json($response, 201);
    }

    public function login(Request $request)
    {
        //validate the request
        $validator = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        //attempt to authenticate
        if (Auth::attempt($validator)) {
            //get user
            $user = User::where('email', $validator['email'])->with('role')->first();
            
            //token generated
            $token = $user->createToken('api_token')->plainTextToken;
            
            //return response with token and user info
            $response = [
                'message' => 'Login successful',
                'data' => [
                    'access_token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role->name ?? 'employee'
                    ]
                ]
            ];
            return response()->json($response);
        } else {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
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
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ]);

        // get user
        $user = $request->user();

        // verify current password
        if (!Hash::check($validator['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // change password
        $user->password = Hash::make($validator['password']);
        $user->save();

        // delete all tokens (logout from everywhere)
        $user->tokens()->delete();

        // return response 
        return response()->json([
            'message' => 'Password changed successfully. Please log in again.'
        ], 200);
    }



}
