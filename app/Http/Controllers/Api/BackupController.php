<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL; // Import URL
use ZipArchive;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{
    // --- NEW FUNCTION: Generate a Secure Link ---
    public function getDownloadLink(Request $request)
    {
        // Create a secure, temporary URL valid for 5 minutes
        // We pass the 'include' params into the signed URL
        $url = URL::signedRoute('backup.download', [
            'include' => $request->query('include'),
            'user_id' => $request->user()->id // Pass ID for security check
        ], now()->addMinutes(5));

        return response()->json(['url' => $url]);
    }

    // --- EXISTING EXPORT FUNCTION ---
    public function export(Request $request)
    {
        if (!class_exists('ZipArchive')) {
            return response()->json(['error' => 'PHP ZipArchive extension missing.'], 500);
        }

        // Ensure the request has a valid signature (Laravel middleware handles this, but good to know)
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        // Security: Use the ID passed in the signed URL, not Auth::id() (since this route is outside Sanctum)
        $userId = $request->query('user_id');

        $selections = explode(',', $request->query('include'));
        $zipFileName = 'backup_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = public_path($zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

            // 1. MASTER RECORD
            if (in_array('master', $selections)) {
                $masterData = DB::table('citizens')
                    ->where('citizens.user_id', $userId) // Filter by User
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

            // 2. Individual Tables
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

                    if ($tableName === 'citizens') {
                        $query->where('user_id', $userId);
                    } elseif ($tableName === 'vehicles') {
                        $query->join('citizens', 'vehicles.citizen_id', '=', 'citizens.id')
                            ->where('citizens.user_id', $userId)->select('vehicles.*');
                    } else {
                        $query->join('vehicles', "$tableName.vehicle_id", '=', 'vehicles.id')
                            ->join('citizens', 'vehicles.citizen_id', '=', 'citizens.id')
                            ->where('citizens.user_id', $userId)->select("$tableName.*");
                    }
                    $this->addCsvToZip($zip, "{$key}_table.csv", $query->get());
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
