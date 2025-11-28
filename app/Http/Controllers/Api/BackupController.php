<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{
    public function export(Request $request)
    {
        if (!class_exists('ZipArchive')) {
            return response()->json(['error' => 'PHP ZipArchive extension missing.'], 500);
        }

        $userId = $request->user()->id; // <--- SECURITY KEY
        $selections = explode(',', $request->query('include'));
        $zipFileName = 'backup_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = public_path($zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

            // 1. MASTER RECORD (Combined & Filtered)
            if (in_array('master', $selections)) {
                $masterData = DB::table('citizens')
                    ->where('citizens.user_id', $userId) // <--- Filter by User
                    ->leftJoin('vehicles', 'citizens.id', '=', 'vehicles.citizen_id')
                    ->select(
                        'citizens.name',
                        'citizens.mobile_number',
                        'citizens.city_district',
                        'citizens.address',
                        'vehicles.registration_no',
                        'vehicles.type',
                        'vehicles.make_model'
                    )->get();

                $this->addCsvToZip($zip, 'master_combined.csv', $masterData);
            }

            // 2. Individual Tables (Filtered Logic)
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
                    $query = DB::table($tableName);

                    // Filter logic based on table type
                    if ($tableName === 'citizens') {
                        $query->where('user_id', $userId);
                    } elseif ($tableName === 'vehicles') {
                        $query->join('citizens', 'vehicles.citizen_id', '=', 'citizens.id')
                            ->where('citizens.user_id', $userId)
                            ->select('vehicles.*');
                    } else {
                        // For documents (tax, insurance, etc.)
                        $query->join('vehicles', "$tableName.vehicle_id", '=', 'vehicles.id')
                            ->join('citizens', 'vehicles.citizen_id', '=', 'citizens.id')
                            ->where('citizens.user_id', $userId)
                            ->select("$tableName.*");
                    }

                    $this->addCsvToZip($zip, "{$key}_table.csv", $query->get());
                }
            }

            $zip->close();
        } else {
            return response()->json(['error' => 'Could not create ZIP file'], 500);
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
        $firstRow = (array) $data->first();
        fputcsv($tempStream, array_keys($firstRow));

        foreach ($data as $row) {
            fputcsv($tempStream, (array) $row);
        }

        rewind($tempStream);
        $zip->addFromString($filename, stream_get_contents($tempStream));
        fclose($tempStream);
    }
}
