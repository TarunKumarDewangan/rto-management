<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Citizen;

class AccountController extends Controller
{
    public function statement(Request $request, $citizenId)
    {
        try {
            // --- SECURITY FIX: Add ->where('user_id', ...) ---
            $citizen = Citizen::where('id', $citizenId)
                ->where('user_id', $request->user()->id) // <--- THIS LINE SECURES IT
                ->with([
                    'vehicles.taxes.payments',
                    'vehicles.insurances.payments',
                    'vehicles.puccs.payments',
                    'vehicles.fitnesses.payments',
                    'vehicles.vltds.payments',
                    'vehicles.permits.payments',
                    'vehicles.speedGovernors.payments',
                ])->first(); // Changed findOrFail to first()

            if (!$citizen) {
                return response()->json(['message' => 'Unauthorized or Not Found'], 403);
            }

            // ... Rest of the code remains exactly the same ...
            $statement = [];

            foreach ($citizen->vehicles as $v) {
                // (Keep the rest of your logic here...)
                // Helper to format data rows
                $addItem = function ($items, $serviceName, $dateField, $billField) use ($v, &$statement) {
                    if (!$items)
                        return;
                    foreach ($items as $item) {
                        $paid = $item->payments->sum('amount');
                        $statement[] = [
                            'id' => $item->id,
                            'date' => $item->$dateField ?? $item->created_at,
                            'vehicle' => $v->registration_no,
                            'service' => $serviceName,
                            'bill_amount' => $item->$billField ?? 0,
                            'paid' => $paid,
                            'balance' => ($item->$billField ?? 0) - $paid,
                            'payments' => $item->payments,
                            'raw_date' => $item->created_at
                        ];
                    }
                };

                $addItem($v->taxes, 'Tax', 'upto_date', 'bill_amount');
                $addItem($v->insurances, 'Insurance', 'end_date', 'bill_amount');
                $addItem($v->puccs, 'PUCC', 'valid_until', 'bill_amount');
                $addItem($v->fitnesses, 'Fitness', 'valid_until', 'bill_amount');
                $addItem($v->vltds, 'VLTD', 'valid_until', 'bill_amount');
                $addItem($v->permits, 'Permit', 'valid_until', 'bill_amount');
                $addItem($v->speedGovernors, 'Speed Gov', 'valid_until', 'bill_amount');
            }

            usort($statement, fn($a, $b) => strtotime($b['raw_date']) - strtotime($a['raw_date']));

            return response()->json([
                'citizen' => $citizen,
                'statement' => $statement
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
