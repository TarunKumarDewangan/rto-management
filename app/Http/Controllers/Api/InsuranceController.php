<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Insurance;
use Illuminate\Support\Facades\Validator;

class InsuranceController extends Controller
{
    public function index($vehicleId)
    {
        $insurances = Insurance::where('vehicle_id', $vehicleId)->with('payments')->latest()->get();
        return response()->json($insurances);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'end_date' => 'required|date',
            'company' => 'nullable|string',
            'type' => 'nullable|string',
            'actual_amount' => 'nullable|numeric',
            'bill_amount' => 'nullable|numeric',
            'start_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // --- FIX: Handle Empty Strings ---
        $data = $request->all();
        $data['start_date'] = $request->start_date ?: null;
        $data['actual_amount'] = $request->actual_amount !== "" ? $request->actual_amount : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        try {
            $insurance = Insurance::create($data);
            return response()->json(['message' => 'Insurance Saved', 'data' => $insurance]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $insurance = Insurance::findOrFail($id);

        // --- FIX: Handle Empty Strings ---
        $data = $request->all();
        $data['start_date'] = $request->start_date ?: null;
        $data['actual_amount'] = $request->actual_amount !== "" ? $request->actual_amount : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        $insurance->update($data);
        return response()->json(['message' => 'Updated']);
    }


    public function destroy($id)
    {
        Insurance::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }
}
