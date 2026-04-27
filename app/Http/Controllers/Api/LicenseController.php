<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\License;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LicenseController extends Controller
{
    // Helper to get Team IDs
    private function getTeamIds($user)
    {
        $bossId = $user->parent_id ?? $user->id;
        return \App\Models\User::where('id', $bossId)->orWhere('parent_id', $bossId)->pluck('id');
    }

    // 1. GET LIST
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $query = License::where('user_id', $userId)->latest();

        // Search
        if ($request->search) {
            $k = $request->search;
            $query->where(function ($q) use ($k) {
                $q->where('applicant_name', 'like', "%$k%")
                    ->orWhere('mobile_number', 'like', "%$k%")
                    ->orWhere('application_no', 'like', "%$k%")
                    ->orWhere('ll_number', 'like', "%$k%");
            });
        }

        // Filters
        if ($request->from_date)
            $query->whereDate('ll_valid_upto', '>=', $request->from_date);
        if ($request->to_date)
            $query->whereDate('ll_valid_upto', '<=', $request->to_date);

        if ($request->crossed_31_days === 'true') {
            $thirtyDaysAgo = Carbon::today()->subDays(30);
            $query->whereDate('ll_valid_from', '<=', $thirtyDaysAgo);
        }

        if ($request->pending_dues === 'true') {
            $query->whereRaw('(ll_bill_amount - ll_paid_amount) > 0');
        }

        if ($request->expires_soon === 'true') {
            $nextMonth = Carbon::today()->addDays(30);
            $query->whereDate('ll_valid_upto', '<=', $nextMonth)
                ->whereDate('ll_valid_upto', '>=', Carbon::today());
        }

        return response()->json($query->get());
    }

    // 2. CREATE ENTRY (THE MISSING METHOD)
    public function store(Request $request)
    {
        // 1. Validate
        $request->validate([
            'applicant_name' => 'required',
            'dob' => 'required|date',
            'mobile_number' => 'required',
        ]);

        try {
            // 2. Prepare Data Manually
            $data = [
                'user_id' => $request->user()->id,
                'applicant_name' => strtoupper($request->applicant_name),
                'dob' => $request->dob,
                'mobile_number' => $request->mobile_number,
                'address' => $request->address,
                'categories' => is_array($request->categories) ? implode(',', $request->categories) : null,

                // LL Details
                'application_no' => $request->application_no,
                'll_number' => $request->ll_number,
                'll_status' => $request->ll_status,
                'll_valid_from' => $request->ll_valid_from ?: null,
                'll_valid_upto' => $request->ll_valid_upto ?: null,

                // DL Details
                'dl_app_no' => $request->dl_app_no,
                'dl_number' => $request->dl_number,
                'dl_status' => $request->dl_status,
                'dl_valid_from' => $request->dl_valid_from ?: null,
                'dl_valid_upto' => $request->dl_valid_upto ?: null,

                // Financials
                'll_bill_amount' => $request->ll_bill_amount ?: 0,
                'll_paid_amount' => $request->ll_paid_amount ?: 0,
                'dl_bill_amount' => $request->dl_bill_amount ?: 0,
                'dl_paid_amount' => $request->dl_paid_amount ?: 0,
            ];

            // 3. Create
            $license = License::create($data);

            return response()->json(['message' => 'Saved Successfully', 'id' => $license->id]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // 3. UPDATE ENTRY
    public function update(Request $request, $id)
    {
        try {
            $license = License::where('user_id', $request->user()->id)->findOrFail($id);

            $data = $request->all();

            // Clean Arrays
            if (isset($data['categories']) && is_array($data['categories'])) {
                $data['categories'] = implode(',', $data['categories']);
            }

            // Clean Dates
            foreach (['ll_valid_from', 'll_valid_upto', 'dl_valid_from', 'dl_valid_upto'] as $field) {
                if (isset($data[$field]) && $data[$field] === "") {
                    $data[$field] = null;
                }
            }

            // Clean Amounts
            $data['ll_bill_amount'] = $request->ll_bill_amount ?: 0;
            $data['ll_paid_amount'] = $request->ll_paid_amount ?: 0;
            $data['dl_bill_amount'] = $request->dl_bill_amount ?: 0;
            $data['dl_paid_amount'] = $request->dl_paid_amount ?: 0;

            $license->update($data);

            return response()->json(['message' => 'Updated Successfully']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // 4. DELETE ENTRY
    public function destroy(Request $request, $id)
    {
        try {
            $license = License::where('user_id', $request->user()->id)->findOrFail($id);
            $license->delete();
            return response()->json(['message' => 'Deleted Successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // 5. SEND WHATSAPP
    public function sendWhatsApp(Request $request, WhatsAppService $whatsapp)
    {
        try {
            $rec = License::where('user_id', $request->user()->id)->findOrFail($request->id);
            $user = $request->user();

            if (empty($user->whatsapp_key) || empty($user->whatsapp_host)) {
                return response()->json(['message' => 'WhatsApp API not configured'], 400);
            }

            $mobile = '91' . $rec->mobile_number;
            $balance = $rec->ll_bill_amount - $rec->ll_paid_amount;
            $expiry = $rec->ll_valid_upto ? Carbon::parse($rec->ll_valid_upto)->format('d-m-Y') : 'N/A';

            if ($balance > 0) {
                $msg = "Hello {$rec->applicant_name},\n\nYour Learning License application has a pending balance of Rs. {$balance}. Please pay immediately.\n\nRegard,\n{$user->name}";
            } else {
                $msg = "Hello {$rec->applicant_name},\n\nYour Learning License (LL No: {$rec->ll_number}) is valid until {$expiry}.\n\nRegard,\n{$user->name}";
            }

            $whatsapp->sendTextMessage($mobile, $msg, $user->whatsapp_key, $user->whatsapp_host, $user->id);
            return response()->json(['message' => 'Message Sent!']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send: ' . $e->getMessage()], 500);
        }
    }
}
