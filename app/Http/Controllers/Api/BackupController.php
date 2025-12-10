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

            // =========================================================
            // 1. MASTER COMBINED RECORD (FULL DETAILED VIEW)
            // =========================================================
            if (in_array('master', $selections)) {
                $query = DB::table('citizens')
                    ->where('citizens.user_id', $userId)
                    ->join('vehicles', 'citizens.id', '=', 'vehicles.citizen_id')
                    ->select(
                        'citizens.name as Owner Name',
                        'citizens.mobile_number as Mobile',
                        'citizens.address as Address',
                        'vehicles.registration_no as Reg No',
                        'vehicles.type as Class',
                        'vehicles.make_model as Make/Model',
                        'vehicles.chassis_no as Chassis No',
                        'vehicles.engine_no as Engine No'
                    );

                // --- SUBQUERIES FOR EVERY SINGLE COLUMN ---

                // 1. TAX
                $query->addSelect([
                    'Tax Mode' => DB::table('taxes')->select('tax_mode')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('upto_date', 'desc')->limit(1),
                    'Tax Govt Fee' => DB::table('taxes')->select('govt_fee')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('upto_date', 'desc')->limit(1),
                    'Tax Bill' => DB::table('taxes')->select('bill_amount')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('upto_date', 'desc')->limit(1),
                    'Tax From' => DB::table('taxes')->select('from_date')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('upto_date', 'desc')->limit(1),
                    'Tax Upto' => DB::table('taxes')->select('upto_date')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('upto_date', 'desc')->limit(1),
                ]);

                // 2. INSURANCE
                $query->addSelect([
                    'Ins Company' => DB::table('insurances')->select('company')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('end_date', 'desc')->limit(1),
                    'Ins Policy No' => DB::table('insurances')->select('policy_number')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('end_date', 'desc')->limit(1), // Includes new field
                    'Ins Type' => DB::table('insurances')->select('type')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('end_date', 'desc')->limit(1),
                    'Ins Actual' => DB::table('insurances')->select('actual_amount')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('end_date', 'desc')->limit(1),
                    'Ins Bill' => DB::table('insurances')->select('bill_amount')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('end_date', 'desc')->limit(1),
                    'Ins Start' => DB::table('insurances')->select('start_date')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('end_date', 'desc')->limit(1),
                    'Ins End' => DB::table('insurances')->select('end_date')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('end_date', 'desc')->limit(1),
                ]);

                // 3. FITNESS
                $query->addSelect([
                    'Fit No' => DB::table('fitnesses')->select('fitness_no')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'Fit Actual' => DB::table('fitnesses')->select('actual_amount')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'Fit Bill' => DB::table('fitnesses')->select('bill_amount')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'Fit Start' => DB::table('fitnesses')->select('valid_from')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'Fit End' => DB::table('fitnesses')->select('valid_until')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                ]);

                // 4. PERMIT
                $query->addSelect([
                    'Permit No' => DB::table('permits')->select('permit_number')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'Permit Type' => DB::table('permits')->select('permit_type')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'Permit Actual' => DB::table('permits')->select('actual_amount')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'Permit Bill' => DB::table('permits')->select('bill_amount')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'Permit Start' => DB::table('permits')->select('valid_from')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'Permit End' => DB::table('permits')->select('valid_until')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                ]);

                // 5. PUCC
                $query->addSelect([
                    'PUCC No' => DB::table('puccs')->select('pucc_number')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'PUCC Actual' => DB::table('puccs')->select('actual_amount')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'PUCC Bill' => DB::table('puccs')->select('bill_amount')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'PUCC Start' => DB::table('puccs')->select('valid_from')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'PUCC End' => DB::table('puccs')->select('valid_until')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                ]);

                // 6. SPEED GOVERNOR
                $query->addSelect([
                    'Speed Gov No' => DB::table('speed_governors')->select('governor_number')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'Speed Actual' => DB::table('speed_governors')->select('actual_amount')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'Speed Bill' => DB::table('speed_governors')->select('bill_amount')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'Speed Start' => DB::table('speed_governors')->select('valid_from')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'Speed End' => DB::table('speed_governors')->select('valid_until')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                ]);

                // 7. VLTD
                $query->addSelect([
                    'VLTD Vendor' => DB::table('vltds')->select('vendor_name')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'VLTD Actual' => DB::table('vltds')->select('actual_amount')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'VLTD Bill' => DB::table('vltds')->select('bill_amount')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'VLTD Start' => DB::table('vltds')->select('valid_from')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
                    'VLTD End' => DB::table('vltds')->select('valid_until')->whereColumn('vehicle_id', 'vehicles.id')->orderBy('valid_until', 'desc')->limit(1),
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
