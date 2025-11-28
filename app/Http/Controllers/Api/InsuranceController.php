<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Insurance;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class InsuranceController extends Controller
{
    private function checkOwnership($vehicleId)
    {
        return Vehicle::where('id', $vehicleId)->whereHas('citizen', fn($q) => $q->where('user_id', Auth::id()))->exists();
    }

    public function index($vehicleId)
    {
        if (!$this->checkOwnership($vehicleId))
            return response()->json([], 403);
        return response()->json(Insurance::where('vehicle_id', $vehicleId)->with('payments')->latest()->get());
    }

    public function store(Request $request)
    {
        if (!$this->checkOwnership($request->vehicle_id))
            return response()->json(['error' => 'Unauthorized'], 403);
        $validator = Validator::make($request->all(), ['vehicle_id' => 'required', 'end_date' => 'required|date']);
        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);

        $data = $request->all();
        $data['start_date'] = $request->start_date ?: null;
        $data['actual_amount'] = $request->actual_amount !== "" ? $request->actual_amount : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;

        Insurance::create($data);
        return response()->json(['message' => 'Saved']);
    }

    public function update(Request $request, $id)
    {
        $ins = Insurance::findOrFail($id);
        if (!$this->checkOwnership($ins->vehicle_id))
            return response()->json(['error' => 'Unauthorized'], 403);
        $data = $request->all();
        $data['start_date'] = $request->start_date ?: null;
        $data['actual_amount'] = $request->actual_amount !== "" ? $request->actual_amount : null;
        $data['bill_amount'] = $request->bill_amount !== "" ? $request->bill_amount : null;
        $ins->update($data);
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        $ins = Insurance::findOrFail($id);
        if (!$this->checkOwnership($ins->vehicle_id))
            return response()->json(['error' => 'Unauthorized'], 403);
        $ins->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
