<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tax;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TaxController extends Controller
{
    private function checkOwnership($vehicleId)
    {
        return Vehicle::where('id', $vehicleId)
            ->whereHas('citizen', function ($q) {
                $q->where('user_id', Auth::id());
            })->exists();
    }

    public function index($vehicleId)
    {
        if (!$this->checkOwnership($vehicleId))
            return response()->json([], 403);
        return response()->json(Tax::where('vehicle_id', $vehicleId)->with('payments')->latest()->get());
    }

    public function store(Request $request)
    {
        // Check Security
        if (!$this->checkOwnership($request->vehicle_id))
            return response()->json(['error' => 'Unauthorized'], 403);

        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'upto_date' => 'required|date',
            'tax_mode' => 'nullable|string', // Optional
            'from_date' => 'nullable|date',
            'govt_fee' => 'nullable|numeric',
            'bill_amount' => 'nullable|numeric',
            'type' => 'nullable|string',
        ]);

        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);

        // --- FIX: Clean Data (Convert "" to NULL) ---
        $data = $request->all();
        $data['tax_mode'] = $request->tax_mode ?: null; // Handle empty tax mode
        $data['from_date'] = $request->from_date ?: null;
        $data['govt_fee'] = $request->govt_fee !== "" ? $request->govt_fee : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        try {
            $tax = Tax::create($data);
            return response()->json(['message' => 'Saved', 'tax' => $tax]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $tax = Tax::findOrFail($id);
        if (!$this->checkOwnership($tax->vehicle_id))
            return response()->json(['error' => 'Unauthorized'], 403);

        // --- FIX: Clean Data ---
        $data = $request->all();
        $data['tax_mode'] = $request->tax_mode ?: null;
        $data['from_date'] = $request->from_date ?: null;
        $data['govt_fee'] = $request->govt_fee !== "" ? $request->govt_fee : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        $tax->update($data);
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        $tax = Tax::findOrFail($id);
        if (!$this->checkOwnership($tax->vehicle_id))
            return response()->json(['error' => 'Unauthorized'], 403);
        $tax->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
