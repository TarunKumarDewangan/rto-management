<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Citizen;
use Illuminate\Support\Facades\Validator;

class CitizenController extends Controller
{
    /**
     * Get list of citizens ONLY for the logged-in user.
     */
    public function index(Request $request)
    {
        // STRICT FILTERING:
        // Only get citizens where 'user_id' matches the currently logged-in user.
        $citizens = Citizen::where('user_id', $request->user()->id)
            ->withCount('vehicles')
            ->latest()
            ->get();

        return response()->json($citizens);
    }

    /**
     * Store a new citizen for the logged-in user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:15',
            'email' => 'nullable|email',
            'birth_date' => 'nullable|date',
            'relation_type' => 'nullable|string',
            'relation_name' => 'nullable|string',
            'state' => 'nullable|string',
            'city_district' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $citizen = Citizen::create([
            'user_id' => $request->user()->id, // <--- IMPORTANT: Links data to YOU
            'name' => $request->name,
            'mobile_number' => $request->mobile_number,
            'email' => $request->email,
            'birth_date' => $request->birth_date ? $request->birth_date : null,
            'relation_type' => $request->relation_type,
            'relation_name' => $request->relation_name,
            'address' => $request->address,
            'state' => $request->state,
            'city_district' => $request->city_district,
        ]);

        return response()->json(['message' => 'Citizen Registered Successfully', 'citizen' => $citizen]);
    }
    public function show(Request $request, $id)
    {
        // Find the citizen matching the ID AND the current logged-in user
        // Also fetch their 'vehicles' list
        $citizen = Citizen::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with('vehicles') // Load vehicles relationship
            ->first();

        if (!$citizen) {
            return response()->json(['message' => 'Citizen not found'], 404);
        }

        return response()->json($citizen);
    }
}
