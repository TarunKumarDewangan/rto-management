<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Citizen;
use App\Models\License; // Ensure this Model exists
use App\Models\Dl;      // Ensure this Model exists
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

            $results = [];

            // ==========================================
            // 1. SEARCH CITIZENS (Vehicles & Docs)
            // ==========================================
            $citizens = Citizen::where('user_id', $userId)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('mobile_number', 'like', "%{$query}%")
                        ->orWhereHas('vehicles', function ($v) use ($query) {
                            $v->where('registration_no', 'like', "%{$query}%")
                                ->orWhere('chassis_no', 'like', "%{$query}%")
                                ->orWhere('engine_no', 'like', "%{$query}%")
                                ->orWhereHas('insurances', fn($i) => $i->where('company', 'like', "%{$query}%")->orWhere('policy_number', 'like', "%{$query}%"))
                                ->orWhereHas('permits', fn($p) => $p->where('permit_number', 'like', "%{$query}%"));
                        });
                })
                ->with('vehicles')
                ->limit(5)
                ->get();

            foreach ($citizens as $c) {
                $title = $c->name;
                $subtitle = "Mobile: " . $c->mobile_number;
                $type = "Citizen";

                // Smart Display: If searching for vehicle/chassis, show that instead
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

                $results[] = [
                    'id' => $c->id,
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'type' => $type,
                    'link' => "/citizens/{$c->id}" // Frontend Route
                ];
            }

            // ==========================================
            // 2. SEARCH LL REGISTRY
            // ==========================================
            $licenses = License::where('user_id', $userId)
                ->where(function ($q) use ($query) {
                    $q->where('applicant_name', 'like', "%{$query}%")
                      ->orWhere('mobile_number', 'like', "%{$query}%")
                      ->orWhere('application_no', 'like', "%{$query}%")
                      ->orWhere('ll_number', 'like', "%{$query}%")
                      ->orWhere('dl_number', 'like', "%{$query}%");
                })
                ->limit(3)
                ->get();

            foreach ($licenses as $l) {
                $results[] = [
                    'id' => $l->id,
                    'title' => $l->applicant_name,
                    'subtitle' => $l->ll_number ? "LL: " . $l->ll_number : "App: " . $l->application_no,
                    'type' => "LL Registry",
                    'link' => "/license-registry" // Redirects to LL Table
                ];
            }

            // ==========================================
            // 3. SEARCH DL REGISTRY
            // ==========================================
            $dls = Dl::where('user_id', $userId)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('mobile_number', 'like', "%{$query}%")
                      ->orWhere('dl_number', 'like', "%{$query}%");
                })
                ->limit(3)
                ->get();

            foreach ($dls as $d) {
                $results[] = [
                    'id' => $d->id,
                    'title' => $d->name,
                    'subtitle' => "DL: " . ($d->dl_number ?: 'N/A'),
                    'type' => "DL Registry",
                    'link' => "/dl-registry" // Redirects to DL Table
                ];
            }

            return response()->json($results);

        } catch (\Exception $e) {
            Log::error("Search Error: " . $e->getMessage());
            return response()->json(['error' => 'Search failed'], 500);
        }
    }
}
