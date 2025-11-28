<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\WhatsAppService;

// Import Models
use App\Models\Tax;
use App\Models\Insurance;
use App\Models\Fitness;
use App\Models\Permit;
use App\Models\Pucc;
use App\Models\SpeedGovernor;
use App\Models\Vltd;

class SendExpiryNotifications extends Command
{
    protected $signature = 'notifications:send-expiries';
    protected $description = 'Check expiring documents and send WhatsApp alerts using Agent credentials.';

    public function handle(WhatsAppService $whatsAppService): void
    {
        $this->info('Starting Expiry Check...');
        Log::info('Scheduler Started: Checking expiries...');

        // 1. SETTINGS: Days before expiry to notify (e.g., 15 days)
        $daysBefore = 15;
        $targetDate = Carbon::today()->addDays($daysBefore)->toDateString();

        $this->info("Looking for documents expiring on: $targetDate");

        // 2. Check each document type
        $this->checkDocuments($whatsAppService, Tax::class, 'upto_date', 'Road Tax', $targetDate);
        $this->checkDocuments($whatsAppService, Insurance::class, 'end_date', 'Insurance', $targetDate);
        $this->checkDocuments($whatsAppService, Fitness::class, 'valid_until', 'Fitness', $targetDate);
        $this->checkDocuments($whatsAppService, Permit::class, 'valid_until', 'Permit', $targetDate);
        $this->checkDocuments($whatsAppService, Pucc::class, 'valid_until', 'PUCC', $targetDate);
        $this->checkDocuments($whatsAppService, SpeedGovernor::class, 'valid_until', 'Speed Governor', $targetDate);
        $this->checkDocuments($whatsAppService, Vltd::class, 'valid_until', 'VLTD', $targetDate);

        $this->info('All checks completed.');
        Log::info('Scheduler Finished.');
    }

    private function checkDocuments(WhatsAppService $service, $modelClass, $dateCol, $docName, $date)
    {
        // Fetch records expiring on target date
        // We need to load Vehicle -> Citizen -> User to get the API Key
        $records = $modelClass::whereDate($dateCol, $date)
            ->with('vehicle.citizen.user')
            ->get();

        foreach ($records as $rec) {
            $this->processNotification($service, $rec, $docName, $rec->$dateCol);
        }
    }

    private function processNotification($service, $record, $docName, $expiryDate)
    {
        // Safety Checks
        if (!$record->vehicle || !$record->vehicle->citizen || !$record->vehicle->citizen->user) {
            return;
        }

        $vehicle = $record->vehicle;
        $citizen = $vehicle->citizen;
        $user = $citizen->user; // The Agent (User)

        // 1. Get Credentials from the User (Agent)
        $apiKey = $user->whatsapp_key;
        $apiHost = $user->whatsapp_host;

        // 2. Skip if User hasn't configured WhatsApp
        if (empty($apiKey) || empty($apiHost)) {
            Log::warning("Skipped {$vehicle->registration_no}: Agent {$user->name} has no API Key.");
            return;
        }

        // 3. Prepare Data
        $regNo = $vehicle->registration_no;
        $mobile = '91' . $citizen->mobile_number; // Add Country Code
        $dateStr = Carbon::parse($expiryDate)->format('d-m-Y');

        // 4. Construct Message (Hindi + English)
        $message = "प्रिय ग्राहक,\n\nआपके वाहन {$regNo} के {$docName} की वैधता {$dateStr} को समाप्त हो रही है।\n\nकृपया समय पर नवीनीकरण कराएं और जुर्माने से बचें।\n\nसंपर्क करें:\n{$user->name}";

        // 5. Send Message
        $this->info("Sending to {$mobile} using {$user->name}'s API...");

        $success = $service->sendTextMessage($mobile, $message, $apiKey, $apiHost);

        if ($success) {
            $this->info("✅ Sent Successfully.");
        } else {
            $this->error("❌ Failed.");
        }
    }
}
