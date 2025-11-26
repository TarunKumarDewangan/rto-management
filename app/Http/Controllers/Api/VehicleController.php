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
        // Validation
        $validator = Validator::make($request->all(), [
            'citizen_id' => 'required|exists:citizens,id',
            'registration_no' => 'required|string|unique:vehicles,registration_no',

            'type' => 'nullable|string', // <--- CHANGED THIS from 'required' to 'nullable'

            'make_model' => 'nullable|string',
            'chassis_no' => 'nullable|string',
            'engine_no' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create Vehicle
        try {
            $vehicle = Vehicle::create($request->all());
            return response()->json(['message' => 'Vehicle Added Successfully', 'vehicle' => $vehicle]);
        } catch (\Exception $e) {
            // This will show the real error if something else is wrong
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
