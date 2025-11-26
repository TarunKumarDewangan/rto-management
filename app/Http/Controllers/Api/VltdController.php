<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vltd;
use Illuminate\Support\Facades\Validator;

class VltdController extends Controller
{
    public function index($vehicleId)
    {
        $vltds = Vltd::where('vehicle_id', $vehicleId)->with('payments')->latest()->get();
        return response()->json($vltds);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'valid_until' => 'required|date',
            'valid_from' => 'nullable|date',
            'vendor_name' => 'nullable|string',
            'actual_amount' => 'nullable|numeric',
            'bill_amount' => 'nullable|numeric',
        ]);

        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);

        $data = $request->all();
        $data['valid_from'] = $request->valid_from ?: null;
        $data['actual_amount'] = $request->actual_amount !== "" ? $request->actual_amount : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        $vltd = Vltd::create($data);
        return response()->json(['message' => 'VLTD Saved', 'data' => $vltd]);
    }

    public function update(Request $request, $id)
    {
        $vltd = Vltd::findOrFail($id);
        $data = $request->all();
        $data['valid_from'] = $request->valid_from ?: null;
        $data['actual_amount'] = $request->actual_amount !== "" ? $request->actual_amount : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        $vltd->update($data);
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        Vltd::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }
}
