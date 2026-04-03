<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WhatsAppLogController extends Controller
{
    public function index()
    {
        $logs = WhatsAppLog::whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($logs);
    }
}
