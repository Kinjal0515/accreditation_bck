<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function listData()
    {
        $zoneData = Zone::get();
        if ($zoneData->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Zone not found'
            ], 200);
        }
        return response()->json([
            'status' => true,
            'data' => $zoneData,
        ], 200);
    }

    public function index($userId)
    {
        $zoneData = Zone::where('user_id', $userId)->get();
        if ($zoneData->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Zone not found'
            ], 200);
        }
        return response()->json([
            'status' => true,
            'data' => $zoneData,
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $zoneData = new Zone();
            $zoneData->title = $request->title;
            $zoneData->user_id = $request->user_id;            
            $zoneData->description = $request->description;

            $zoneData->save();
            return response()->json(['status' => true, 'message' => 'Zone craete successfully', 'data' => $zoneData,], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to zoneData '], 404);
        }
    }

    public function show($id)
    {
        $zoneData = Zone::find($id);

        if (!$zoneData) {
            return response()->json(['status' => false, 'message' => 'Zone not found'], 200);
        }

        return response()->json(['status' => true, 'data' => $zoneData], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $zoneData = Zone::findOrFail($id);
            
            $zoneData->title = $request->title;
            $zoneData->user_id = $request->user_id;
            $zoneData->description = $request->description;
            $zoneData->save();

            return response()->json(['status' => true, 'message' => 'Zone updated successfully', 'data' => $zoneData], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to update zoneData'], 404);
        }
    }

    public function destroy(string $id)
    {
        $zoneData = Zone::where('id', $id)->firstOrFail();
        if (!$zoneData) {
            return response()->json(['status' => false, 'message' => 'zoneData not found'], 404);
        }

        $zoneData->delete();
        return response()->json(['status' => true, 'message' => 'Zone deleted successfully'], 200);
    }
}
