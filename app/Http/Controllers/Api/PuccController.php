<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pucc;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PuccController extends Controller
{
    // Helper to check if the logged-in user owns the vehicle
    private function checkOwnership($vehicleId) {
        return Vehicle::where('id', $vehicleId)
            ->whereHas('citizen', function($q) {
                $q->where('user_id', Auth::id());
            })->exists();
    }

    public function index($vehicleId)
    {
        if (!$this->checkOwnership($vehicleId)) return response()->json(['message' => 'Unauthorized'], 403);

        $puccs = Pucc::where('vehicle_id', $vehicleId)->with('payments')->latest()->get();
        return response()->json($puccs);
    }

    public function store(Request $request)
    {
        if (!$this->checkOwnership($request->vehicle_id)) return response()->json(['message' => 'Unauthorized'], 403);

        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'valid_until' => 'required|date',
            'valid_from' => 'nullable|date',
            'pucc_number' => 'nullable|string',
            'actual_amount' => 'nullable|numeric',
            'bill_amount' => 'nullable|numeric',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        // Handle empty strings
        $data = $request->all();
        $data['valid_from'] = $request->valid_from ?: null;
        $data['actual_amount'] = $request->actual_amount !== "" ? $request->actual_amount : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        $pucc = Pucc::create($data);
        return response()->json(['message' => 'PUCC Saved', 'data' => $pucc]);
    }

    public function update(Request $request, $id)
    {
        $pucc = Pucc::findOrFail($id);
        if (!$this->checkOwnership($pucc->vehicle_id)) return response()->json(['message' => 'Unauthorized'], 403);

        $data = $request->all();
        $data['valid_from'] = $request->valid_from ?: null;
        $data['actual_amount'] = $request->actual_amount !== "" ? $request->actual_amount : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        $pucc->update($data);
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        $pucc = Pucc::findOrFail($id);
        if (!$this->checkOwnership($pucc->vehicle_id)) return response()->json(['message' => 'Unauthorized'], 403);

        $pucc->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
