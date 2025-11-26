<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tax;
use Illuminate\Support\Facades\Validator;

class TaxController extends Controller
{
    // Get Taxes (with Payments to calculate balance)
    public function index($vehicleId)
    {
        $taxes = Tax::where('vehicle_id', $vehicleId)
            ->with('payments') // Load payments relationship
            ->latest()
            ->get();
        return response()->json($taxes);
    }

    // Save New Tax
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'tax_mode' => 'required|string',
            'upto_date' => 'required|date',
            'from_date' => 'nullable|date',
            'govt_fee' => 'nullable|numeric',
            'bill_amount' => 'nullable|numeric',
            'type' => 'nullable|string',
        ]);

        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);

        // Handle empty strings
        $data = $request->all();
        $data['from_date'] = $request->from_date ?: null;
        $data['govt_fee'] = $request->govt_fee !== "" ? $request->govt_fee : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        $tax = Tax::create($data);
        return response()->json(['message' => 'Tax Saved', 'tax' => $tax]);
    }

    // Update Tax
    public function update(Request $request, $id)
    {
        $tax = Tax::findOrFail($id);

        $data = $request->all();
        $data['from_date'] = $request->from_date ?: null;
        $data['govt_fee'] = $request->govt_fee !== "" ? $request->govt_fee : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        $tax->update($data);
        return response()->json(['message' => 'Tax Updated']);
    }

    // Delete Tax
    public function destroy($id)
    {
        Tax::destroy($id);
        return response()->json(['message' => 'Tax Deleted']);
    }
}
