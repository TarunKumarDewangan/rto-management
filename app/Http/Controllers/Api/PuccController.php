<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pucc;
use Illuminate\Support\Facades\Validator;

class PuccController extends Controller
{
    public function index($vehicleId)
    {
        // Ensure 'payments' relationship exists in Model
        $puccs = Pucc::where('vehicle_id', $vehicleId)
            ->with('payments')
            ->latest()
            ->get();
        return response()->json($puccs);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'valid_until' => 'required|date',
            'valid_from' => 'nullable|date',
            'pucc_number' => 'nullable|string',
            'actual_amount' => 'nullable|numeric',
            'bill_amount' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // --- FIX: Convert Empty Strings to NULL ---
        $data = $request->all();
        $data['valid_from'] = $request->valid_from ?: null;
        $data['actual_amount'] = $request->actual_amount !== "" ? $request->actual_amount : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        try {
            $pucc = Pucc::create($data);
            return response()->json(['message' => 'PUCC Saved', 'data' => $pucc]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $pucc = Pucc::findOrFail($id);

        $data = $request->all();
        $data['valid_from'] = $request->valid_from ?: null;
        $data['actual_amount'] = $request->actual_amount !== "" ? $request->actual_amount : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        $pucc->update($data);
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        Pucc::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }
}
