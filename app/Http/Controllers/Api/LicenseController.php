<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LicenseController extends Controller
{
    // --- Helper to get Team IDs (Optional based on your role logic) ---
    private function getTeamIds($user)
    {
        // If your User model has parent_id logic, keep this.
        // Otherwise, just return [$user->id];
        $bossId = $user->parent_id ?? $user->id;
        return \App\Models\User::where('id', $bossId)->orWhere('parent_id', $bossId)->pluck('id');
    }

    // 1. Get List (With Filters)
    public function index(Request $request)
    {
        // Modified to work even if you don't have team logic yet
        $userId = $request->user()->id;

        // If you strictly want only the logged-in user's data:
        $query = DB::table('licenses')->where('user_id', $userId);

        // Search
        if ($request->search) {
            $k = $request->search;
            $query->where(function ($q) use ($k) {
                $q->where('applicant_name', 'like', "%$k%")
                    ->orWhere('mobile_number', 'like', "%$k%")
                    ->orWhere('application_no', 'like', "%$k%")
                    ->orWhere('ll_number', 'like', "%$k%")
                    ->orWhere('dl_number', 'like', "%$k%");
            });
        }

        // Date Filter
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        return response()->json($query->latest()->get());
    }

    // 2. Create License Entry
    public function store(Request $request)
    {
        $request->validate([
            'applicant_name' => 'required',
            'dob' => 'required|date',
            'mobile_number' => 'required',
        ]);

        $data = $request->except(['categories']);
        $data['user_id'] = $request->user()->id;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        if ($request->categories && is_array($request->categories)) {
            $data['categories'] = implode(',', $request->categories);
        } else {
            $data['categories'] = null;
        }

        $data['ll_bill_amount'] = $request->ll_bill_amount ?: 0;
        $data['ll_paid_amount'] = $request->ll_paid_amount ?: 0;
        $data['dl_bill_amount'] = $request->dl_bill_amount ?: 0;
        $data['dl_paid_amount'] = $request->dl_paid_amount ?: 0;

        foreach (['ll_valid_from', 'll_valid_upto', 'dl_valid_from', 'dl_valid_upto'] as $field) {
            if (empty($data[$field]))
                $data[$field] = null;
        }

        $id = DB::table('licenses')->insertGetId($data);
        return response()->json(['message' => 'License Entry Saved', 'id' => $id]);
    }

    // 3. Update Entry
    public function update(Request $request, $id)
    {
        $data = $request->except(['id', 'user_id', 'created_at', 'updated_at', 'categories']);

        if ($request->categories && is_array($request->categories)) {
            $data['categories'] = implode(',', $request->categories);
        }

        $data['ll_bill_amount'] = $request->ll_bill_amount ?: 0;
        $data['ll_paid_amount'] = $request->ll_paid_amount ?: 0;
        $data['dl_bill_amount'] = $request->dl_bill_amount ?: 0;
        $data['dl_paid_amount'] = $request->dl_paid_amount ?: 0;

        foreach (['ll_valid_from', 'll_valid_upto', 'dl_valid_from', 'dl_valid_upto'] as $field) {
            if (empty($data[$field]))
                $data[$field] = null;
        }

        $data['updated_at'] = now();

        DB::table('licenses')->where('id', $id)->update($data);

        return response()->json(['message' => 'Updated Successfully']);
    }

    // 4. Delete Entry
    public function destroy(Request $request, $id)
    {
        DB::table('licenses')->where('id', $id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
