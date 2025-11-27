<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    public function export(Request $request)
    {
        // 1. Check if ZipArchive exists
        if (!class_exists('ZipArchive')) {
            return response()->json(['error' => 'PHP ZipArchive extension is missing. Enable it in php.ini'], 500);
        }

        $selections = explode(',', $request->query('include'));
        $zipFileName = 'backup_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = public_path($zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

            // 2. MASTER RECORD (Combined Data)
            if (in_array('master', $selections)) {
                $masterData = DB::table('citizens')
                    ->leftJoin('vehicles', 'citizens.id', '=', 'vehicles.citizen_id') // Use Left Join to include citizens without vehicles
                    ->select(
                        'citizens.name',
                        'citizens.mobile_number',
                        'citizens.city_district',
                        'citizens.address',
                        'vehicles.registration_no',
                        'vehicles.type',
                        'vehicles.make_model',
                        'vehicles.chassis_no',
                        'vehicles.engine_no'
                    )->get();

                $this->addCsvToZip($zip, 'master_combined.csv', $masterData);
            }

            // 3. Individual Tables Mapping
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
                    // Fetch Data
                    $data = DB::table($tableName)->get();
                    // Add to Zip
                    $this->addCsvToZip($zip, "{$key}_table.csv", $data);
                }
            }

            $zip->close();
        } else {
            return response()->json(['error' => 'Could not create ZIP file'], 500);
        }

        // 4. Return Download Response
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    // --- Helper to safely add CSV to Zip ---
    private function addCsvToZip($zip, $filename, $data)
    {
        // PREVENT CRASH: If table is empty, create a dummy CSV with "No Records"
        if ($data->isEmpty()) {
            $zip->addFromString($filename, "Status\nNo records found in this table");
            return;
        }

        $tempStream = fopen('php://memory', 'w+');

        // Convert first row object to array to get headers
        $firstRow = (array) $data->first();
        fputcsv($tempStream, array_keys($firstRow));

        // Add Rows
        foreach ($data as $row) {
            fputcsv($tempStream, (array) $row);
        }

        rewind($tempStream);
        $csvContent = stream_get_contents($tempStream);
        fclose($tempStream);

        $zip->addFromString($filename, $csvContent);
    }
}
