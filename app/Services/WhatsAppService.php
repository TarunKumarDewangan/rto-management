<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendTextMessage($mobile, $message, $apiKey, $apiHost)
    {
        // 1. Prepare Data
        // Remove 'http://' or 'https://' if user typed it, to avoid duplication
        $cleanHost = preg_replace('#^https?://#', '', rtrim($apiHost, '/'));

        // Use the exact path from your screenshot
        $url = "http://{$cleanHost}/wapp/v2/api/send";

        try {
            // 2. Send Request
            $response = Http::withoutVerifying()->get($url, [
                'apikey' => $apiKey,
                'mobile' => $mobile,
                'msg' => $message, // Your provider uses 'msg', not 'message'
            ]);

            $data = $response->json();

            // 3. Log & Return
            Log::info("WhatsApp Response: " . $response->body());

            if ($response->successful()) {
                return true;
            } else {
                throw new \Exception("Provider Error: " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("WhatsApp Exception: " . $e->getMessage());
            throw $e;
        }
    }
}
