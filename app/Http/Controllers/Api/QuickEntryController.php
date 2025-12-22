<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Citizen;
use App\Models\Vehicle;
use App\Models\Pucc;

class QuickEntryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'mobile_number' => 'required',
            'registration_no' => 'required',
            'valid_until' => 'required|date',
        ]);

        // 1. Get Logged in User ID
        $ownerId = $request->user()->id;

        // Clean Inputs
        $mobile = trim($request->mobile_number);
        $regNo = strtoupper(str_replace(' ', '', trim($request->registration_no)));
        $name = strtoupper(trim($request->name));

        DB::beginTransaction();
        try {
            // --- 2. Find or Create Citizen (For THIS User) ---
            $citizen = Citizen::where('mobile_number', $mobile)
                ->where('user_id', $ownerId)
                ->first();

            if (!$citizen) {
                $citizen = Citizen::create([
                    'user_id' => $ownerId,
                    'name' => $name,
                    'mobile_number' => $mobile,
                ]);
            } else {
                // Update name if they fixed a typo
                $citizen->update(['name' => $name]);
            }

            // --- 3. Find or Create Vehicle (For THIS Citizen/User) ---
            // We search for vehicles belonging to this specific citizen
            // OR vehicles belonging to this User (in case they are moving ownership, though rare)

            $vehicle = Vehicle::where('registration_no', $regNo)
                ->whereHas('citizen', function ($q) use ($ownerId) {
                    $q->where('user_id', $ownerId);
                })
                ->first();

            if ($vehicle) {
                // Vehicle Exists: Ensure it links to this citizen and update type
                $vehicle->update([
                    'citizen_id' => $citizen->id,
                    'type' => $request->type ?? $vehicle->type
                ]);
            } else {
                // Create New Vehicle
                $vehicle = Vehicle::create([
                    'citizen_id' => $citizen->id,
                    'registration_no' => $regNo,
                    'type' => $request->type ?? null,
                    'make_model' => null
                ]);
            }

            // --- 4. Create PUCC Entry ---
            Pucc::create([
                'vehicle_id' => $vehicle->id,
                'valid_from' => $request->valid_from,
                'valid_until' => $request->valid_until,
                'pucc_number' => null,
                'bill_amount' => 0,
                'actual_amount' => 0
            ]);

            DB::commit();
            return response()->json(['message' => 'Entry Saved & Linked Successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
