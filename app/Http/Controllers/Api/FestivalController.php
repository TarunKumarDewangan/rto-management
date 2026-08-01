<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Festival;

class FestivalController extends Controller
{
    public function index(Request $request)
    {
        $festivals = Festival::where('user_id', $request->user()->id)
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($festivals);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'name' => 'required|string',
            'msg_to_send' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['user_id'] = $request->user()->id;

        Festival::create($data);

        return response()->json(['message' => 'Festival Saved Successfully']);
    }

    public function update(Request $request, $id)
    {
        $festival = Festival::where('user_id', $request->user()->id)->findOrFail($id);

        $request->validate([
            'date' => 'required|date',
            'name' => 'required|string',
            'msg_to_send' => 'nullable|string',
        ]);

        $festival->update($request->only('date', 'name', 'msg_to_send'));

        return response()->json(['message' => 'Festival Updated']);
    }

    public function destroy(Request $request, $id)
    {
        Festival::where('user_id', $request->user()->id)->findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
