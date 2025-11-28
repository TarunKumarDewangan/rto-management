<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpiryReportController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Filters
        $citizenId = $request->citizen_id; // <--- NEW FILTER
        $name = $request->owner_name;
        $vehicleNo = $request->vehicle_no;
        $docType = $request->doc_type;
        $dateFrom = $request->expiry_from;
        $dateUpto = $request->expiry_upto;

        // Helper to build query
        $buildQuery = function ($table, $typeLabel, $dateCol) use ($userId) {
            return \Illuminate\Support\Facades\DB::table($table)
                ->join('vehicles', "$table.vehicle_id", '=', 'vehicles.id')
                ->join('citizens', 'vehicles.citizen_id', '=', 'citizens.id')
                ->where('citizens.user_id', $userId)
                ->select(
                    'citizens.id as citizen_id',
                    'citizens.name as owner_name',
                    'citizens.mobile_number',
                    'vehicles.registration_no',
                    \Illuminate\Support\Facades\DB::raw("'$typeLabel' as doc_type"),
                    "$table.$dateCol as expiry_date"
                );
        };

        // ... (Queries for all 7 tables remain the same) ...
        // (Copy the array of queries from previous code)
        $queries = [];
        if (!$docType || $docType == 'Tax')
            $queries[] = $buildQuery('taxes', 'Tax', 'upto_date');
        if (!$docType || $docType == 'Insurance')
            $queries[] = $buildQuery('insurances', 'Insurance', 'end_date');
        if (!$docType || $docType == 'PUCC')
            $queries[] = $buildQuery('puccs', 'PUCC', 'valid_until');
        if (!$docType || $docType == 'Fitness')
            $queries[] = $buildQuery('fitnesses', 'Fitness', 'valid_until');
        if (!$docType || $docType == 'Permit')
            $queries[] = $buildQuery('permits', 'Permit', 'valid_until');
        if (!$docType || $docType == 'Speed Gov')
            $queries[] = $buildQuery('speed_governors', 'Speed Gov', 'valid_until');
        if (!$docType || $docType == 'VLTD')
            $queries[] = $buildQuery('vltds', 'VLTD', 'valid_until');

        // Combine Queries
        $mainQuery = null;
        foreach ($queries as $q) {
            if (!$mainQuery)
                $mainQuery = $q;
            else
                $mainQuery->union($q);
        }

        $result = \Illuminate\Support\Facades\DB::query()->fromSub($mainQuery, 'combined_table');

        // --- APPLY NEW CITIZEN FILTER ---
        if ($citizenId) {
            $result->where('citizen_id', $citizenId);
        }

        if ($name)
            $result->where('owner_name', 'like', "%$name%");
        if ($vehicleNo)
            $result->where('registration_no', 'like', "%$vehicleNo%");
        if ($dateFrom)
            $result->whereDate('expiry_date', '>=', $dateFrom);
        if ($dateUpto)
            $result->whereDate('expiry_date', '<=', $dateUpto);

        $result->orderBy('expiry_date', 'asc');

        return response()->json($result->paginate(15));
    }
}
