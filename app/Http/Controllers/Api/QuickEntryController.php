<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Citizen;
use App\Models\Vehicle;
use App\Models\Pucc;
use App\Models\Insurance; // <--- Import Insurance
use App\Models\Permit;

class QuickEntryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'mobile_number' => 'required',
            'registration_no' => 'required',
            'valid_until' => 'required|date',
            'mode' => 'required|in:PUCC,INSURANCE,PERMIT' // <--- Validate Mode
        ]);

        $ownerId = $request->user()->id;
        $mobile = trim($request->mobile_number);
        $regNo = strtoupper(str_replace(' ', '', trim($request->registration_no)));
        $name = strtoupper(trim($request->name));

        DB::beginTransaction();
        try {
            // 1. Citizen Logic (Same)
            $citizen = Citizen::where('mobile_number', $mobile)->where('user_id', $ownerId)->first();
            if (!$citizen) {
                $citizen = Citizen::create(['user_id' => $ownerId, 'name' => $name, 'mobile_number' => $mobile]);
            } else {
                $citizen->update(['name' => $name]);
            }

            // 2. Vehicle Logic (Same)
            $vehicle = Vehicle::where('registration_no', $regNo)
                ->whereHas('citizen', fn($q) => $q->where('user_id', $ownerId))
                ->first();

            if ($vehicle) {
                $vehicle->update(['citizen_id' => $citizen->id, 'type' => $request->type ?? $vehicle->type]);
            } else {
                $vehicle = Vehicle::create([
                    'citizen_id' => $citizen->id,
                    'registration_no' => $regNo,
                    'type' => $request->type ?? null,
                    'make_model' => null
                ]);
            }

            // 3. Insert Document based on Mode
            if ($request->mode === 'PUCC') {
                Pucc::create([
                    'vehicle_id' => $vehicle->id,
                    'valid_from' => $request->valid_from,
                    'valid_until' => $request->valid_until,
                    'pucc_number' => null,
                    'bill_amount' => 0,
                    'actual_amount' => 0
                ]);
            } elseif ($request->mode === 'INSURANCE') {
                Insurance::create([
                    'vehicle_id' => $vehicle->id,
                    'start_date' => $request->valid_from, // Mapped to start_date
                    'end_date' => $request->valid_until,   // Mapped to end_date
                    'company' => $request->company,
                    'policy_number' => $request->policy_number,
                    'bill_amount' => 0,
                    'actual_amount' => 0
                ]);
            } elseif ($request->mode === 'PERMIT') {
                Permit::create([
                    'vehicle_id' => $vehicle->id,
                    'valid_from' => $request->valid_from,
                    'valid_until' => $request->valid_until,
                    'permit_number' => $request->permit_number,
                    'permit_type' => $request->permit_type,
                    'bill_amount' => 0,
                    'actual_amount' => 0
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Entry Saved Successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
