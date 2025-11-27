<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle; // <--- THIS LINE IS CRITICAL
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    public function store(Request $request)
    {
        // 1. Get the Current Logged-in User
        $user = $request->user();

        // 2. Custom Check: Does this User already have this vehicle?
        // We check if any vehicle with this Reg No exists belongs to a Citizen owned by this User.
        $exists = Vehicle::where('registration_no', $request->registration_no)
            ->whereHas('citizen', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();

        if ($exists) {
            return response()->json([
                'errors' => ['registration_no' => ['You have already added this Vehicle Number.']]
            ], 422);
        }

        // 3. Standard Validation (Removed 'unique:vehicles' rule)
        $validator = Validator::make($request->all(), [
            'citizen_id' => 'required|exists:citizens,id',
            'registration_no' => 'required|string', // Removed 'unique'
            'type' => 'nullable|string',
            'make_model' => 'nullable|string',
            'chassis_no' => 'nullable|string',
            'engine_no' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 4. Create Vehicle
        $vehicle = Vehicle::create($request->all());

        return response()->json(['message' => 'Vehicle Added Successfully', 'vehicle' => $vehicle]);
    }
}
