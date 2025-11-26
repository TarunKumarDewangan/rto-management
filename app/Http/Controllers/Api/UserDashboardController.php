<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Citizen;
use App\Models\Vehicle;
use App\Models\Insurance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function stats(Request $request)
    {
        $userId = $request->user()->id;

        // 1. Total Citizens (Linked to this user)
        $totalCitizens = Citizen::where('user_id', $userId)->count();

        // 2. Total Vehicles (Linked to citizens of this user)
        $totalVehicles = Vehicle::whereHas('citizen', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->count();

        // 3. Collected Today (Sum of amounts from documents created today)
        // Note: For now, we only check Insurance. You can add Permits/Fitness later.
        $collectedToday = Insurance::whereHas('vehicle.citizen', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->whereDate('created_at', Carbon::today())->sum('total_amount');

        // 4. Expiring in 15 Days
        $expiringSoon = Insurance::whereHas('vehicle.citizen', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->whereBetween('expiry_date', [Carbon::now(), Carbon::now()->addDays(15)])
            ->count();

        return response()->json([
            'total_citizens' => $totalCitizens,
            'total_vehicles' => $totalVehicles,
            'collected_today' => $collectedToday,
            'expiring_soon' => $expiringSoon
        ]);
    }
}
