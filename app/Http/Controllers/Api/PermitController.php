<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permit;
use Illuminate\Support\Facades\Validator;

class PermitController extends Controller
{
    public function index($vehicleId)
    {
        return response()->json(Permit::where('vehicle_id', $vehicleId)->with('payments')->latest()->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'valid_until' => 'required|date',
            'valid_from' => 'nullable|date',
            'permit_number' => 'nullable|string',
            'permit_type' => 'nullable|string',
            'actual_amount' => 'nullable|numeric',
            'bill_amount' => 'nullable|numeric',
        ]);
        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);

        $data = $request->all();
        $data['valid_from'] = $request->valid_from ?: null;
        $data['actual_amount'] = $request->actual_amount !== "" ? $request->actual_amount : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        $permit = Permit::create($data);
        return response()->json(['message' => 'Saved', 'data' => $permit]);
    }

    public function update(Request $request, $id)
    {
        $permit = Permit::findOrFail($id);
        $data = $request->all();
        $data['valid_from'] = $request->valid_from ?: null;
        $data['actual_amount'] = $request->actual_amount !== "" ? $request->actual_amount : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;
        $permit->update($data);
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        Permit::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }
}
