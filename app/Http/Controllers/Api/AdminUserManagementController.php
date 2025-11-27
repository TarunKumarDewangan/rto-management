<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserManagementController extends Controller
{
    // Middleware should handle this, but explicit check is safer
    private function checkAdmin($request)
    {
        if ($request->user()->role !== 'admin')
            abort(403, 'Unauthorized');
    }

    public function index(Request $request)
    {
        $this->checkAdmin($request);
        $users = User::where('role', 'user')->orderBy('created_at', 'desc')->get();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $this->checkAdmin($request);
        $request->validate(['name' => 'required', 'email' => 'required|email|unique:users', 'password' => 'required|min:6']);
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'is_active' => true
        ]);
        return response()->json(['message' => 'User Created']);
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin($request);
        $user = User::findOrFail($id);
        $user->update($request->only(['name', 'email']));
        if ($request->password)
            $user->update(['password' => Hash::make($request->password)]);
        return response()->json(['message' => 'Updated']);
    }

    public function toggleStatus(Request $request, $id)
    {
        $this->checkAdmin($request);
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
        return response()->json(['message' => 'Status Updated']);
    }

    public function destroy(Request $request, $id)
    {
        $this->checkAdmin($request);
        User::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }
}
