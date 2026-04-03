<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\WhatsAppService;
use App\Models\User;

// Models
use App\Models\Tax;
use App\Models\Insurance;
use App\Models\Fitness;
use App\Models\Permit;
use App\Models\Pucc;
use App\Models\SpeedGovernor;
use App\Models\Vltd;
use App\Models\License; // LL Flow
use App\Models\Dl;      // DL Registry

class SendExpiryNotifications extends Command
{
    protected $signature = 'notifications:send-expiries';
    protected $description = 'Send WhatsApp alerts based on per-user settings.';

    public function handle(WhatsAppService $whatsAppService): void
    {
        $this->info('Starting Dynamic Scheduler...');

        $users = User::where('is_active', true)
            ->whereNotNull('whatsapp_key')
            ->whereNotNull('whatsapp_host')
            ->get();

        foreach ($users as $user) {
            $this->info("Processing Agent: {$user->name}");

            // --- A. VEHICLE DOCUMENTS ---
            $this->checkDoc($whatsAppService, $user, Tax::class, 'days_tax', 'upto_date', 'Road Tax');
            $this->checkDoc($whatsAppService, $user, Insurance::class, 'days_insurance', 'end_date', 'Insurance');
            $this->checkDoc($whatsAppService, $user, Fitness::class, 'days_fitness', 'valid_until', 'Fitness');
            $this->checkDoc($whatsAppService, $user, Permit::class, 'days_permit', 'valid_until', 'Permit');
            $this->checkDoc($whatsAppService, $user, Pucc::class, 'days_pucc', 'valid_until', 'PUCC');
            $this->checkDoc($whatsAppService, $user, Vltd::class, 'days_vltd', 'valid_until', 'VLTD');
            $this->checkDoc($whatsAppService, $user, SpeedGovernor::class, 'days_speed', 'valid_until', 'Speed Governor');

            // --- B. LICENSES ---

            // 1. Driving License (Renew Message)
            $this->checkDL($whatsAppService, $user);

            // 2. Learning License (New DL Message)
            $this->checkLL($whatsAppService, $user);
        }

        $this->info('Done.');
    }

    // --- 1. VEHICLE DOCS ---
    private function checkDoc($service, $user, $model, $daysCol, $dateCol, $docName)
    {
        $days = $user->$daysCol ?? 15;
        $targetDate = Carbon::today()->addDays($days)->toDateString();

        $records = $model::whereDate($dateCol, $targetDate)
            ->whereHas('vehicle.citizen', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with('vehicle.citizen')
            ->get();

        foreach ($records as $rec) {
            $vehicle = $rec->vehicle;
            $citizen = $vehicle->citizen;
            $mobile = '91' . $citizen->mobile_number;
            $dateStr = Carbon::parse($targetDate)->format('d-m-Y');

            // Message
            $msg = "प्रिय ग्राहक,\n\n"
                . "आपके वाहन *{$vehicle->registration_no}* के *{$docName}* की वैधता *{$dateStr}* को समाप्त हो रही है।\n"
                . "(शेष *{$days}* दिन)\n\n"
                . "कृपया समय पर नवीनीकरण कराएं।\n\n"
                . "संपर्क:\n"
                . "👤 *{$user->name}*\n"
                . "📱 9876543210"; // Replace with variable if available

            try {
                $service->sendTextMessage($mobile, $msg, $user->whatsapp_key, $user->whatsapp_host);
            } catch (\Exception $e) {
                Log::error("Failed vehicle msg: " . $e->getMessage());
            }
        }
    }

    // --- 2. DRIVING LICENSE (Renew) ---
    private function checkDL($service, $user)
    {
        $days = $user->days_dl ?? 60;
        $targetDate = Carbon::today()->addDays($days)->toDateString();

        $records = Dl::where('user_id', $user->id)->whereDate('valid_upto', $targetDate)->get();

        foreach ($records as $rec) {
            $mobile = '91' . $rec->mobile_number;
            $dateStr = Carbon::parse($targetDate)->format('d-m-Y');

            $msg = "प्रिय ग्राहक *{$rec->name}*,\n\n"
                . "आपका *Driving License (DL)* *{$days}* दिनों में (*{$dateStr}*) समाप्त होने वाला है।\n\n"
                . "⚠️ DL एक्सपायर होने के बाद गाड़ी चलाना दंडनीय अपराध है।\n\n"
                . "कृपया नवीनीकरण (Renewal) के लिए संपर्क करें।\n\n"
                . "संपर्क:\n"
                . "👤 *{$user->name}*\n"
                . "📱 9876543210";

            try {
                $service->sendTextMessage($mobile, $msg, $user->whatsapp_key, $user->whatsapp_host);
            } catch (\Exception $e) {
                Log::error("Failed DL msg: " . $e->getMessage());
            }
        }
    }

    // --- 3. LEARNING LICENSE (Apply New) ---
    private function checkLL($service, $user)
    {
        $days = $user->days_ll ?? 30;
        $targetDate = Carbon::today()->addDays($days)->toDateString();

        // Check LL Flow Table
        $records = License::where('user_id', $user->id)->whereDate('ll_valid_upto', $targetDate)->get();

        foreach ($records as $rec) {
            $mobile = '91' . $rec->mobile_number;
            $dateStr = Carbon::parse($targetDate)->format('d-m-Y');

            $msg = "प्रिय ग्राहक *{$rec->applicant_name}*,\n\n"
                . "आपका *Learning License (LL)* *{$days}* दिनों में (*{$dateStr}*) समाप्त होने वाला है।\n\n"
                . "⚠️ *ध्यान दें:* लर्निंग लाइसेंस की अवधि समाप्त होने के बाद इसे रिन्यू नहीं किया जा सकता।\n\n"
                . "कृपया जल्द से जल्द *'परमानेंट ड्राइविंग लाइसेंस'* (Permanent DL) के लिए आवेदन करें।\n\n"
                . "संपर्क:\n"
                . "👤 *{$user->name}*\n"
                . "📱 9876543210";

            try {
                $service->sendTextMessage($mobile, $msg, $user->whatsapp_key, $user->whatsapp_host);
            } catch (\Exception $e) {
                Log::error("Failed LL msg: " . $e->getMessage());
            }
        }
    }
}
