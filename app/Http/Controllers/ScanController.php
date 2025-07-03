<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Organizer;
use App\Models\ScanHistory;
use App\Models\User;
use App\Models\Zone;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ScanController extends Controller
{

    public function verifyCard(Request $request, $orderId)
    {
        try {
            $loggedInUser = Auth::user();

            $user = User::where('order_id', $orderId)
                ->with([
                    'roles',
                    'reportingUser.roles',
                    'reportingUser.reportingUser.roles'
                ])
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ]);
            }

            $roleName = $user->roles->pluck('name')->first();

            $result = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_number' => $user->number,
                'role' => $roleName,
            ];

            if ($roleName === 'User') {
                $companyUser = $user->comp_id ?? null;
                $organizerUser = $user->org_id ?? null;
                // $companyUser = $user->reportingUser ?? null;
                // $organizerUser = $companyUser?->reportingUser ?? null;

                $companyUserData = Company::where('user_id', $companyUser)->first();
                $organizerUserData = Organizer::where('user_id', $organizerUser)->first();
                $zoneData = Zone::select('id', 'title')->get();

                $result['company_user'] = $companyUserData ? [
                    'id' => $companyUserData->id,
                    'company_name' => $companyUserData->company_name,
                    'email' => $companyUserData->email,
                    'name' => $companyUserData->name,
                    'Zone' => $companyUserData->zone ?? null,
                ] : null;

                $result['organizer_user'] = $organizerUserData ? [
                    'id' => $organizerUserData->id,
                    'name' => $organizerUserData->name,
                    'email' => $organizerUserData->email,
                ] : null;
                $result['zone_data'] = $zoneData->map(function ($zone) {
                    return [
                        'id' => $zone->id,
                        'title' => $zone->title,
                    ];
                });
            }

            return response()->json([
                'status' => true,
                'message' => 'Role-based data fetched successfully.',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function ChekIn($orderId)
    {
        $booking = User::where('order_id', $orderId)->first();

        if (!$booking) {
            return response()->json(['status' => false, 'message' => 'Booking not found'], 404);
        }
        $UserId = $booking->id;
        $scannerId = auth()->id();
        $now = now()->toDateTimeString();
        $history = ScanHistory::where('user_id', $UserId)
            ->where('scanner_id', $scannerId)
            ->first();
        if ($history) {
            $times = json_decode($history->scan_time ?? '[]', true);

            $times[] = $now;

            $history->scan_time = json_encode($times);
            $history->count = $history->count + 1;
            $history->save();
        } else {
            $history = new ScanHistory();
            $history->user_id = $UserId;
            $history->scanner_id = $scannerId;
            $history->scan_time = json_encode([$now]);
            $history->count = 1;
            $history->save();
        }
        $booking->status = true;
        $booking->save();
        return response()->json([
            'status' => true,
            'message' => 'Scan history recorded',
            'data' => $history
        ], 200);
    }

    public function scannerHistory(Request $request)
    {
        try {

            $scanHistory = new ScanHistory();
            $scanHistory->user_id = $request->user_id;
            $scanHistory->scan_time = $request->scan_time;
            $scanHistory->scanner_id = $request->scanner_id;

            $scanHistory->save();

            return response()->json([
                'status' => true,
                'message' => 'scan history saved successfully.',
                'data' => $scanHistory
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save scan history.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function scannedReports()
    {
        $today = Carbon::today()->toDateString();
    
        $scans = ScanHistory::all()->groupBy('scanner_id');
    
        $result = $scans->map(function ($items, $scannerId) use ($today) {
            $scannerUser = User::find($scannerId); // scanner is also user
    
            $organizer = Organizer::where('user_id', $scannerUser->org_id)->first();
            $company =  Company::where('user_id', $organizer->org_id)->first();
    
            // Total count
            $totalScans = $items->sum('count');
    
            // Today's count (check scan_time JSON array contains today's date)
            $todayCount = $items->filter(function ($item) use ($today) {
                $scanTimes = json_decode($item->scan_time, true);
                foreach ($scanTimes as $scanTime) {
                    if (Str::startsWith($scanTime, $today)) {
                        return true;
                    }
                }
                return false;
            })->sum('count');
    
            return [
                'scanner_id' => $scannerId,
                'organizer_name' => $organizer->name ?? null,
                'company_name' => $company->company_name ?? null,
                'scanner_name' =>  $scannerUser->name ?? null,
                'total_scans' => $totalScans,
                'today_scans' => $todayCount,
            ];
        })->values();
    
        return response()->json([
            'status' => true,
            'data' => $result
        ]);
    }
    

    //    public function verifyCard(Request $request, $orderId)
    // {
    //     try {
    //         $loggedInUser = Auth::user();

    //         $user = User::where('order_id', $orderId)
    //             ->with([
    //                 'roles',
    //                 'reportingUser.roles',
    //                 'reportingUser.reportingUser.roles'
    //             ])
    //             ->first();

    //         if (!$user) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'User not found.'
    //             ]);
    //         }

    //         $roleName = $user->roles->pluck('name')->first();

    //         $result = [
    //             'user_id' => $user->id,
    //             'user_name' => $user->name,
    //             'user_email' => $user->email,
    //             'user_number' => $user->number,
    //             'role' => $roleName,
    //         ];

    //         // if ($roleName === 'Organizer') {
    //         //     $result['organizer_data'] = [
    //         //         'name' => $user->name,
    //         //         'email' => $user->email,
    //         //         'number' => $user->number,
    //         //         'photo' => $user->photo,
    //         //         'company' => $user->company_name,
    //         //     ];
    //         // } elseif ($roleName === 'Sub Organizer') {
    //         //     $result['sub-organizer_data'] = [
    //         //         'name' => $user->name,
    //         //         'email' => $user->email,
    //         //         'number' => $user->number,
    //         //         'photo' => $user->photo,
    //         //         'company' => $user->company_name,
    //         //     ];
    //         // } elseif ($roleName === 'Company') {
    //         //     $company = Company::where('user_id', $user->id)->first();

    //         //     $result['company_data'] = $company ? [
    //         //         'id' => $company->id,
    //         //         'name' => $company->name,
    //         //         'email' => $company->email,
    //         //         'number' => $company->number,
    //         //         'gst_no' => $company->gst_no,
    //         //         'company_name' => $company->company_name,
    //         //         'company_letter' => $company->company_letter,
    //         //     ] : null;
    //         if ($roleName === 'User') {
    //             $companyUser = $user->comp_id ?? null;
    //             $organizerUser = $companyUser?->org_id ?? null;
    //             // $companyUser = $user->reportingUser ?? null;
    //             // $organizerUser = $companyUser?->reportingUser ?? null;

    //             $companyUser = User::where('user_id', $companyUser)->with(['company', 'roles'])->first();

    //             $result['company_user'] = $companyUser ? [
    //                 'id' => $companyUser->id,
    //                 'name' => $companyUser->name,
    //                 'email' => $companyUser->email,
    //                 'Zone' => $companyUser->company->zones ?? null,
    //                 'role' => $companyUser->roles->pluck('name')->first(),
    //                 'company' => $companyUser->company ? [
    //                     'id' => $companyUser->company->id,
    //                     'company_name' => $companyUser->company->company_name,
    //                     'gst_no' => $companyUser->company->gst_no,
    //                 ] : null,
    //             ] : null;

    //             $result['organizer_user'] = $organizerUser ? [
    //                 'id' => $organizerUser->id,
    //                 'name' => $organizerUser->name,
    //                 'email' => $organizerUser->email,
    //                 'role' => $organizerUser->roles->pluck('name')->first(),
    //                 'company' => $organizerUser->organizerNew ? [
    //                     'id' => $organizerUser->organizerNew->id,
    //                     'company_name' => $organizerUser->organizerNew->company_name,
    //                     'gst_no' => $organizerUser->organizerNew->gst_no,
    //                 ] : null,
    //             ] : null;
    //         }

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Role-based data fetched successfully.',
    //             'data' => $result
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
}
