<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsAppLog;

class WhatsAppService
{
    public function sendTextMessage($mobile, $message, $apiKey, $apiHost, $userId = null)
    {
        // 1. Prepare Data
        $cleanHost = preg_replace('#^https?://#', '', rtrim($apiHost, '/'));
        $url = "http://{$cleanHost}/wapp/v2/api/send";

        $status = false;
        $responseBody = null;

        try {
            // 2. Send Request
            $response = Http::withoutVerifying()->get($url, [
                'apikey' => $apiKey,
                'mobile' => $mobile,
                'msg' => $message,
            ]);

            $responseBody = $response->body();
            Log::info("WhatsApp Response: " . $responseBody);

            if ($response->successful()) {
                $status = true;
            } else {
                throw new \Exception("Provider Error: " . $responseBody);
            }

            return true;

        } catch (\Exception $e) {
            Log::error("WhatsApp Exception: " . $e->getMessage());
            $responseBody = $responseBody ?? $e->getMessage();
            throw $e;
        } finally {
            // 3. Save Log
            try {
                WhatsAppLog::create([
                    'user_id' => $userId,
                    'mobile' => $mobile,
                    'message' => $message,
                    'status' => $status,
                    'response_body' => $responseBody,
                ]);
            } catch (\Exception $logError) {
                Log::error("Failed to save WhatsApp log: " . $logError->getMessage());
            }
        }
    }
}
