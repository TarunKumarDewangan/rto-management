<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Citizen;
use App\Models\Vehicle;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserDashboardController extends Controller
{
    public function stats(Request $request)
    {
        $userId = $request->user()->id;
        $today = Carbon::today();
        $next15Days = Carbon::today()->addDays(15);

        // 1. Total Citizens
        $totalCitizens = Citizen::where('user_id', $userId)->count();

        // 2. Total Vehicles
        $totalVehicles = Vehicle::whereHas('citizen', fn($q) => $q->where('user_id', $userId))->count();

        // 3. Collected Today
        // This checks if the payment is linked to ANY document owned by the user
        $collectedToday = Payment::whereDate('payment_date', $today)
            ->where(function ($query) use ($userId) {
                $query->whereHas('tax.vehicle.citizen', fn($q) => $q->where('user_id', $userId))
                    ->orWhereHas('insurance.vehicle.citizen', fn($q) => $q->where('user_id', $userId))
                    ->orWhereHas('pucc.vehicle.citizen', fn($q) => $q->where('user_id', $userId))
                    ->orWhereHas('fitness.vehicle.citizen', fn($q) => $q->where('user_id', $userId))
                    ->orWhereHas('vltd.vehicle.citizen', fn($q) => $q->where('user_id', $userId))
                    ->orWhereHas('permit.vehicle.citizen', fn($q) => $q->where('user_id', $userId))
                    ->orWhereHas('speedGovernor.vehicle.citizen', fn($q) => $q->where('user_id', $userId));
            })
            ->sum('amount');

        // 4. Expiring in 15 Days (Direct DB Query for speed)
        $expiringSoon = 0;
        $docTables = [
            'taxes' => 'upto_date',
            'insurances' => 'end_date',
            'puccs' => 'valid_until',
            'fitnesses' => 'valid_until',
            'permits' => 'valid_until',
            'vltds' => 'valid_until',
            'speed_governors' => 'valid_until'
        ];

        foreach ($docTables as $table => $col) {
            $count = DB::table($table)
                ->join('vehicles', "$table.vehicle_id", '=', 'vehicles.id')
                ->join('citizens', 'vehicles.citizen_id', '=', 'citizens.id')
                ->where('citizens.user_id', $userId)
                ->whereBetween("$table.$col", [$today, $next15Days])
                ->count();
            $expiringSoon += $count;
        }

        return response()->json([
            'total_citizens' => $totalCitizens,
            'total_vehicles' => $totalVehicles,
            'collected_today' => (int) $collectedToday,
            'expiring_soon' => $expiringSoon
        ]);
    }
}
