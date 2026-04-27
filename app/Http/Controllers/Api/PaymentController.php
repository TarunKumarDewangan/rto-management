<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        // Validate Ownership before creating
        $checkRelation = function($relationId, $modelClass) use ($request) {
            if (!$relationId) return true;
            return \Illuminate\Support\Facades\DB::table((new $modelClass)->getTable())
                ->join('vehicles', 'vehicles.id', '=', (new $modelClass)->getTable() . '.vehicle_id')
                ->join('citizens', 'citizens.id', '=', 'vehicles.citizen_id')
                ->where((new $modelClass)->getTable() . '.id', $relationId)
                ->where('citizens.user_id', $request->user()->id)
                ->exists();
        };

        $valid = $checkRelation($request->tax_id, \App\Models\Tax::class) &&
                 $checkRelation($request->insurance_id, \App\Models\Insurance::class) &&
                 $checkRelation($request->pucc_id, \App\Models\Pucc::class) &&
                 $checkRelation($request->fitness_id, \App\Models\Fitness::class) &&
                 $checkRelation($request->vltd_id, \App\Models\Vltd::class) &&
                 $checkRelation($request->permit_id, \App\Models\Permit::class) &&
                 $checkRelation($request->speed_governor_id, \App\Models\SpeedGovernor::class);

        if (!$valid) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        Payment::create([
            'tax_id' => $request->tax_id ?? null,
            'insurance_id' => $request->insurance_id ?? null,
            'pucc_id' => $request->pucc_id ?? null,
            'fitness_id' => $request->fitness_id ?? null,
            'vltd_id' => $request->vltd_id ?? null,
            'permit_id' => $request->permit_id ?? null,
            'speed_governor_id' => $request->speed_governor_id ?? null,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'remarks' => $request->remarks
        ]);

        return response()->json(['message' => 'Payment Added']);
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::with([
            'tax.vehicle.citizen',
            'insurance.vehicle.citizen',
            'pucc.vehicle.citizen',
            'fitness.vehicle.citizen',
            'vltd.vehicle.citizen',
            'permit.vehicle.citizen',
            'speedGovernor.vehicle.citizen'
        ])->findOrFail($id);

        $checkOwner = function ($relation) use ($payment, $request) {
            return $payment->$relation && $payment->$relation->vehicle->citizen->user_id === $request->user()->id;
        };

        $isOwner = $checkOwner('tax') || $checkOwner('insurance') || $checkOwner('pucc') ||
                   $checkOwner('fitness') || $checkOwner('vltd') ||
                   $checkOwner('permit') || $checkOwner('speedGovernor');

        if (!$isOwner) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $payment->update([
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'remarks' => $request->remarks
        ]);
        return response()->json(['message' => 'Payment Updated']);
    }

    public function destroy($id, Request $request)
    {
        $payment = Payment::with([
            'tax.vehicle.citizen',
            'insurance.vehicle.citizen',
            // ... load others if needed for strictness
        ])->findOrFail($id);

        // Helper to check ownership
        $checkOwner = function ($relation) use ($payment, $request) {
            return $payment->$relation && $payment->$relation->vehicle->citizen->user_id === $request->user()->id;
        };

        // Verify if the payment belongs to the logged-in user
        $isOwner = $checkOwner('tax') || $checkOwner('insurance') || $checkOwner('pucc') ||
            $checkOwner('fitness') || $checkOwner('vltd') ||
            $checkOwner('permit') || $checkOwner('speedGovernor');

        if (!$isOwner) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $payment->delete();
        return response()->json(['message' => 'Payment Deleted']);
    }
}
