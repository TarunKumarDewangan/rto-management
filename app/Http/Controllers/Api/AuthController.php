<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 1. Login Function (Returns Token)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Create Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    // 2. Create User Function (Only Admin can do this)
    public function createUser(Request $request)
    {
        // Check if current user is admin (Simple check)
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user'
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user]);
    }

    // 3. Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed', // expects new_password_confirmation field
        ]);

        $user = $request->user();

        // 1. Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 400);
        }

        // 2. Update Password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully!']);
    }

    public function updateSettings(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'days_tax' => 'nullable|integer',
            'days_insurance' => 'nullable|integer',
            'days_fitness' => 'nullable|integer',
            'days_permit' => 'nullable|integer',
            'days_pucc' => 'nullable|integer',
            'days_vltd' => 'nullable|integer',
            'days_speed' => 'nullable|integer',
            'days_ll' => 'nullable|integer',
            'days_dl' => 'nullable|integer',
            'whatsapp_key' => 'nullable|string',
            'whatsapp_host' => 'nullable|string',
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Settings updated successfully!',
            'user' => $user->fresh()
        ]);
    }
}
