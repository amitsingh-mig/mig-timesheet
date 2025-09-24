<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // API methods remain as-is below. Additional web methods added.
    public function indexPage()
    {
        $this->authorize('index', User::class);
        $users = User::orderBy('id', 'asc')->paginate(15);
        return view('users.index', ['users' => $users]);
    }

    public function showPage($id)
    {
        $user = User::where('id', $id)->first();
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }
        $this->authorize('show', $user);
        return view('users.show', ['user' => $user]);
    }

    public function editPage($id)
    {
        $user = User::where('id', $id)->first();
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }
        $this->authorize('update', $user);
        return view('users.edit', ['user' => $user]);
    }

    public function updatePage(Request $request, $id)
    {
        $validator = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $user = User::where('id', $id)->first();
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }
        $this->authorize('update', $user);

        $user->name = $validator['name'];
        $user->email = $validator['email'];
        $user->save();

        return redirect()->route('users.show', $user->id)->with('success', 'User updated successfully');
    }

    public function destroyPage(Request $request, $id)
    {
        $this->authorize('destroy', User::class);
        $user = User::where('id', $id)->first();
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted');
    }

    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::find($id);
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }

        // Only admins (via can:admin middleware) reach here
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return redirect()->route('users.show', $user->id)->with('success', 'Password reset successfully');
    }
    public function index()
    {
        $this->authorize('index',User::class);
        $user = User::all();

        return response()->json(['data' => $user], 200);
    }

    public function show($id)
    {
        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json(['message' => 'User Not Found'], 404);
        }
        $this->authorize('show', $user); 
        return response()->json($user, 200);
    }

    public function update(Request $request, $id)
    {
        //input
        $validator = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id
        ]);

        //get user object
        $user = User::where('id', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'User Not Found'], 404);
        }

        $this->authorize('update',$user);

        //update user
        $user->name = $validator['name'];
        $user->email = $validator['email'];
        $user->save();

        //return response
        return response()->json($user, 200);
    }

    public function destroy($id)
    {
        $this->authorize('destroy',User::class);
        
        $user = User::where('id', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'User Not Found'], 404);
        }

        $user->delete();

        return response(null, 204);
    }
}
