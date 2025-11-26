<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserManagementController extends Controller
{
    // 1. Get All Users (excluding admins if you want)
    public function index()
    {
        // Get all users who are NOT admins, ordered by latest
        $users = User::where('role', 'user')->orderBy('created_at', 'desc')->get();
        return response()->json($users);
    }

    // 2. Create User
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'is_active' => true
        ]);

        return response()->json(['message' => 'User Created Successfully']);
    }

    // 3. Update User
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->has('password') && $request->password != '') {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json(['message' => 'User Updated Successfully']);
    }

    // 4. Toggle Active/Deactive Status
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active; // Switch status
        $user->save();

        return response()->json([
            'message' => 'User status updated',
            'is_active' => $user->is_active
        ]);
    }

    // 5. Delete User
    public function destroy($id)
    {
        User::destroy($id);
        return response()->json(['message' => 'User Deleted Successfully']);
    }
}
