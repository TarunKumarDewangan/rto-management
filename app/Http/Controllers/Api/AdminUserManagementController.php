<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserManagementController extends Controller
{
    private function checkAdmin($request)
    {
        if ($request->user()->role !== 'admin')
            abort(403, 'Unauthorized');
    }

    public function index(Request $request)
    {
        $this->checkAdmin($request);
        // Get all users (Agents) ordered by latest
        $users = User::where('role', 'user')->orderBy('created_at', 'desc')->get();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'whatsapp_key' => 'nullable|string',
            'whatsapp_host' => 'nullable|string',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'is_active' => true,
            'whatsapp_key' => $request->whatsapp_key,   // Saved
            'whatsapp_host' => $request->whatsapp_host, // Saved
        ]);

        return response()->json(['message' => 'User Created Successfully']);
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin($request);
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'whatsapp_key' => 'nullable|string',
            'whatsapp_host' => 'nullable|string',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->whatsapp_key = $request->whatsapp_key;   // Updated
        $user->whatsapp_host = $request->whatsapp_host; // Updated

        // Only update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json(['message' => 'User Updated Successfully']);
    }

    public function toggleStatus(Request $request, $id)
    {
        $this->checkAdmin($request);
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();
        return response()->json(['message' => 'User Status Updated']);
    }

    public function destroy(Request $request, $id)
    {
        $this->checkAdmin($request);
        User::destroy($id);
        return response()->json(['message' => 'User Deleted']);
    }
}
