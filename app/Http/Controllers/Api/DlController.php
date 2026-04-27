<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dl;
use Carbon\Carbon;

class DlController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $query = \App\Models\Dl::where('user_id', $userId)->latest();

        // 1. Search (Existing)
        if ($request->search) {
            $k = $request->search;
            $query->where(function ($q) use ($k) {
                $q->where('name', 'like', "%$k%")
                    ->orWhere('mobile_number', 'like', "%$k%")
                    ->orWhere('dl_number', 'like', "%$k%");
            });
        }

        // 2. Date Range (Existing)
        if ($request->from_date)
            $query->whereDate('valid_upto', '>=', $request->from_date);
        if ($request->to_date)
            $query->whereDate('valid_upto', '<=', $request->to_date);

        // 3. SPECIAL FILTERS


        // B. Expires in 1 Month (Existing)
        if ($request->expires_soon === 'true') {
            $nextMonth = \Carbon\Carbon::today()->addDays(30);
            $query->whereDate('valid_upto', '<=', $nextMonth)
                ->whereDate('valid_upto', '>=', \Carbon\Carbon::today());
        }

        // C. Expires in 2 Months (NEW)
        if ($request->expires_2_months === 'true') {
            $twoMonths = \Carbon\Carbon::today()->addDays(60);
            $query->whereDate('valid_upto', '<=', $twoMonths)
                ->whereDate('valid_upto', '>=', \Carbon\Carbon::today());
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'mobile_number' => 'required',
            'valid_upto' => 'required|date'
        ]);

        $data = $request->all();
        $data['user_id'] = $request->user()->id;
        $data['name'] = strtoupper($request->name);
        $data['dl_number'] = strtoupper($request->dl_number);

        // Handle Empty Dates
        $data['dob'] = $request->dob ?: null;
        $data['valid_from'] = $request->valid_from ?: null;

        Dl::create($data);

        return response()->json(['message' => 'DL Saved Successfully']);
    }

    public function update(Request $request, $id)
    {
        $dl = Dl::where('user_id', $request->user()->id)->findOrFail($id);

        $data = $request->all();
        $data['name'] = strtoupper($request->name);
        $data['dl_number'] = strtoupper($request->dl_number);
        $data['dob'] = $request->dob ?: null;
        $data['valid_from'] = $request->valid_from ?: null;

        $dl->update($data);

        return response()->json(['message' => 'DL Updated']);
    }

    public function destroy(Request $request, $id)
    {
        Dl::where('user_id', $request->user()->id)->findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
