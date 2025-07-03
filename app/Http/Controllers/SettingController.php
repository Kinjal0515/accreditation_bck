<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class SettingController extends Controller
{

    public function index()
    {
        $settings = Setting::first();
        return response()->json(['status' => true, 'data' => $settings], 200);
    }

    public function storeSettings(Request $request)
    {
        try {
            $settings = Setting::firstOrNew([]);
    
            // Update fields only if they exist in the request
            $settings->app_name = $request->input('app_name', $settings->app_name);
            $settings->whatsApp_number = $request->input('whatsApp_number', $settings->whatsApp_number);
            $settings->missed_call_number = $request->input('missed_call_number', $settings->missed_call_number);
            $settings->user_notification_permission = $request->input('user_notification_permission', $settings->user_notification_permission);
            $settings->welcome_modal_status = $request->input('welcome_modal_status', $settings->welcome_modal_status);
            
            // Handle file uploads
            if ($request->hasFile('logo')) {
                $settings->logo = $this->storeFile($request->file('logo'), 'settings/logo');
            }
            if ($request->hasFile('auth_logo')) {
                $settings->auth_logo = $this->storeFile($request->file('auth_logo'), 'settings/auth_logo');
            }
            if ($request->hasFile('favicon')) {
                $settings->favicon = $this->storeFile($request->file('favicon'), 'settings/favicon');
            }
            if ($request->hasFile('mobile_logo')) {
                $settings->mobile_logo = $this->storeFile($request->file('mobile_logo'), 'settings/mobile_logo');
            }
               
            $settings->save();
    
            return response()->json([
                'status' => true,
                'success' => 'Settings saved successfully.',
                'data' => $settings,
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'Failed to save settings.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    private function storeFile($file, $folder, $disk = 'public')
    {
        if (!$file) return null;
    
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('uploads/' . $folder, $filename, $disk);
        return Storage::disk($disk)->url($path);
    }
    
    

}
