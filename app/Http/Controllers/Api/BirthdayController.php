<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Citizen;
use Carbon\Carbon;

class BirthdayController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $name = $request->name;
        $mobile = $request->mobile_number;
        $showAll = $request->boolean('show_all');
        $date = $request->date;

        $query = Citizen::where('user_id', $userId)->whereNotNull('birth_date');

        if ($name) {
            $query->where('name', 'like', "%$name%");
        }
        if ($mobile) {
            $query->where('mobile_number', 'like', "%$mobile%");
        }

        if (!$showAll) {
            $d = $date ? Carbon::parse($date) : Carbon::today();
            $query->whereMonth('birth_date', $d->month)->whereDay('birth_date', $d->day);
        }

        $query->orderByRaw("DATE_FORMAT(birth_date, '%m-%d') asc");

        return response()->json($query->paginate(15));
    }
}
