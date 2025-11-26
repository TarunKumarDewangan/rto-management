<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        // Allow either tax_id OR insurance_id
        $request->validate([
            'amount' => 'required|numeric',
            'payment_date' => 'required|date',
        ]);

        Payment::create([
            'tax_id' => $request->tax_id ?? null,
            'insurance_id' => $request->insurance_id ?? null,
            'pucc_id' => $request->pucc_id ?? null,
            'fitness_id' => $request->fitness_id ?? null, // Add this
            'vltd_id' => $request->vltd_id ?? null, // Add this line
            'permit_id' => $request->permit_id ?? null, // Added
            'speed_governor_id' => $request->speed_governor_id ?? null, // Added
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'remarks' => $request->remarks
        ]);

        return response()->json(['message' => 'Payment Added']);
    }

    // UPDATE Payment
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->update([
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'remarks' => $request->remarks
        ]);
        return response()->json(['message' => 'Payment Updated']);
    }

    // DELETE Payment
    public function destroy($id)
    {
        Payment::destroy($id);
        return response()->json(['message' => 'Payment Deleted']);
    }
}
