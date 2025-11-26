<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fitness;
use Illuminate\Support\Facades\Validator;

class FitnessController extends Controller
{
    public function index($vehicleId)
    {
        $fitness = Fitness::where('vehicle_id', $vehicleId)->with('payments')->latest()->get();
        return response()->json($fitness);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'valid_until' => 'required|date',
            'valid_from' => 'nullable|date',
            'fitness_no' => 'nullable|string',
            'actual_amount' => 'nullable|numeric',
            'bill_amount' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // --- FIX: Handle Empty Strings ---
        $data = $request->all();
        $data['valid_from'] = $request->valid_from ?: null;
        $data['actual_amount'] = $request->actual_amount !== "" ? $request->actual_amount : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        try {
            $fit = Fitness::create($data);
            return response()->json(['message' => 'Fitness Saved', 'data' => $fit]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $fit = Fitness::findOrFail($id);

        // --- FIX: Handle Empty Strings ---
        $data = $request->all();
        $data['valid_from'] = $request->valid_from ?: null;
        $data['actual_amount'] = $request->actual_amount !== "" ? $request->actual_amount : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        $fit->update($data);
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        Fitness::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }
}
