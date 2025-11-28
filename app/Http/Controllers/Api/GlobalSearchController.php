<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Citizen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        try {
            $query = $request->input('query');
            $userId = Auth::id();

            if (!$query || strlen($query) < 2) {
                return response()->json([]);
            }

            // --- NEW LOGIC: Search Citizens Table Directly ---
            $citizens = Citizen::where('user_id', $userId)
                ->where(function ($q) use ($query) {
                    // 1. Match Citizen Name or Mobile (Even if 0 vehicles)
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('mobile_number', 'like', "%{$query}%")

                        // 2. Match Vehicle Details (Reg No, Chassis, Engine)
                        ->orWhereHas('vehicles', function ($v) use ($query) {
                        $v->where('registration_no', 'like', "%{$query}%")
                            ->orWhere('chassis_no', 'like', "%{$query}%")
                            ->orWhere('engine_no', 'like', "%{$query}%")

                            // 3. Match Documents (Policy No, Permit No, etc.)
                            ->orWhereHas('insurances', fn($i) => $i->where('company', 'like', "%{$query}%"))
                            ->orWhereHas('permits', fn($p) => $p->where('permit_number', 'like', "%{$query}%"))
                            ->orWhereHas('puccs', fn($pc) => $pc->where('pucc_number', 'like', "%{$query}%"))
                            ->orWhereHas('speedGovernors', fn($s) => $s->where('governor_number', 'like', "%{$query}%"))
                            ->orWhereHas('vltds', fn($vl) => $vl->where('vendor_name', 'like', "%{$query}%"));
                    });
                })
                ->with('vehicles') // Load vehicles to check if we matched one
                ->limit(10)
                ->get();

            // --- Format Results for Frontend ---
            $results = $citizens->map(function ($c) use ($query) {

                // Default Display: Citizen Name
                $title = $c->name;
                $subtitle = "Mobile: " . $c->mobile_number;
                $type = "Citizen";

                // Smart Display: If the search matches a Vehicle, show that instead
                foreach ($c->vehicles as $v) {
                    if (stripos($v->registration_no ?? '', $query) !== false) {
                        $title = $v->registration_no;
                        $subtitle = "Owner: " . $c->name;
                        $type = "Vehicle";
                        break;
                    }
                    if (stripos($v->chassis_no ?? '', $query) !== false) {
                        $title = "Chassis: " . $v->chassis_no;
                        $subtitle = "Vehicle: " . $v->registration_no;
                        $type = "Chassis";
                        break;
                    }
                }

                return [
                    'id' => $c->id, // Navigates to /citizens/{id}
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'type' => $type
                ];
            });

            return response()->json($results);

        } catch (\Exception $e) {
            Log::error("Search Error: " . $e->getMessage());
            return response()->json(['error' => 'Search failed'], 500);
        }
    }
}
