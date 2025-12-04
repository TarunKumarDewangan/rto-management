<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use ZipArchive;

class BackupController extends Controller
{
    public function getDownloadLink(Request $request)
    {
        $url = URL::signedRoute('backup.download', [
            'include' => $request->query('include'),
            'user_id' => $request->user()->id
        ], now()->addMinutes(5));

        return response()->json(['url' => $url]);
    }

    public function export(Request $request)
    {
        if (!class_exists('ZipArchive')) {
            return response()->json(['error' => 'PHP ZipArchive extension missing.'], 500);
        }

        if (!$request->hasValidSignature()) {
            abort(403);
        }

        $userId = $request->query('user_id');
        $selections = explode(',', $request->query('include'));
        $zipFileName = 'backup_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = public_path($zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

            // ==========================================
            // 1. MASTER COMBINED RECORD (UPDATED)
            // ==========================================
            if (in_array('master', $selections)) {
                $query = DB::table('citizens')
                    ->where('citizens.user_id', $userId)
                    ->join('vehicles', 'citizens.id', '=', 'vehicles.citizen_id') // Use Join to ensure we get vehicle data
                    ->select(
                        'citizens.name',
                        'citizens.mobile_number',
                        'citizens.city_district',
                        'citizens.address',
                        'vehicles.registration_no',
                        'vehicles.type',
                        'vehicles.make_model'
                    );

                // --- ADD SUBQUERIES FOR LATEST DATES ---
                // This fetches the latest single date for each document type per vehicle

                $query->addSelect([
                    'tax_upto' => DB::table('taxes')
                        ->select('upto_date')
                        ->whereColumn('vehicle_id', 'vehicles.id')
                        ->orderBy('upto_date', 'desc')
                        ->limit(1),

                    'insurance_upto' => DB::table('insurances')
                        ->select('end_date')
                        ->whereColumn('vehicle_id', 'vehicles.id')
                        ->orderBy('end_date', 'desc')
                        ->limit(1),

                    'pucc_upto' => DB::table('puccs')
                        ->select('valid_until')
                        ->whereColumn('vehicle_id', 'vehicles.id')
                        ->orderBy('valid_until', 'desc')
                        ->limit(1),

                    'fitness_upto' => DB::table('fitnesses')
                        ->select('valid_until')
                        ->whereColumn('vehicle_id', 'vehicles.id')
                        ->orderBy('valid_until', 'desc')
                        ->limit(1),

                    'permit_upto' => DB::table('permits')
                        ->select('valid_until')
                        ->whereColumn('vehicle_id', 'vehicles.id')
                        ->orderBy('valid_until', 'desc')
                        ->limit(1),

                    'vltd_upto' => DB::table('vltds')
                        ->select('valid_until')
                        ->whereColumn('vehicle_id', 'vehicles.id')
                        ->orderBy('valid_until', 'desc')
                        ->limit(1),

                    'speed_gov_upto' => DB::table('speed_governors')
                        ->select('valid_until')
                        ->whereColumn('vehicle_id', 'vehicles.id')
                        ->orderBy('valid_until', 'desc')
                        ->limit(1),
                ]);

                $this->addCsvToZip($zip, 'master_combined.csv', $query->get());
            }

            // ==========================================
            // 2. INDIVIDUAL TABLES (KEEP AS IS)
            // ==========================================
            $tables = [
                'citizen' => 'citizens',
                'vehicle' => 'vehicles',
                'tax' => 'taxes',
                'insurance' => 'insurances',
                'pucc' => 'puccs',
                'fitness' => 'fitnesses',
                'permit' => 'permits',
                'speed_gov' => 'speed_governors',
                'vltd' => 'vltds'
            ];

            foreach ($tables as $key => $tableName) {
                if (in_array($key, $selections)) {
                    $q = DB::table($tableName);

                    if ($tableName === 'citizens') {
                        $q->where('user_id', $userId);
                    } elseif ($tableName === 'vehicles') {
                        $q->join('citizens', 'vehicles.citizen_id', '=', 'citizens.id')
                            ->where('citizens.user_id', $userId)->select('vehicles.*');
                    } else {
                        $q->join('vehicles', "$tableName.vehicle_id", '=', 'vehicles.id')
                            ->join('citizens', 'vehicles.citizen_id', '=', 'citizens.id')
                            ->where('citizens.user_id', $userId)->select("$tableName.*");
                    }
                    $this->addCsvToZip($zip, "{$key}_table.csv", $q->get());
                }
            }

            $zip->close();
        } else {
            return response()->json(['error' => 'Could not create ZIP'], 500);
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    private function addCsvToZip($zip, $filename, $data)
    {
        if ($data->isEmpty()) {
            $zip->addFromString($filename, "Status\nNo records found");
            return;
        }
        $tempStream = fopen('php://memory', 'w+');
        fputcsv($tempStream, array_keys((array) $data->first()));
        foreach ($data as $row) {
            fputcsv($tempStream, (array) $row);
        }
        rewind($tempStream);
        $zip->addFromString($filename, stream_get_contents($tempStream));
        fclose($tempStream);
    }
}
