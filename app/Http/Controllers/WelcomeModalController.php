<?php

namespace App\Http\Controllers;

use App\Models\WelcomeModal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WelcomeModalController extends Controller
{

    public function welcomeModal()
    {
        $welcome = WelcomeModal::get();
        if (!$welcome) {
            return response()->json([
                'status' => false,
                'message' => 'No welcome modal found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $welcome
        ], 200);
    }

    public function storeWelcomeModal(Request $request)
    {
        $welcome = new WelcomeModal();
        $welcome->title = $request->title;
        $welcome->description = $request->description;
        $welcome->url_exc = $request->url;
        $welcome->url_sm = $request->sm_url;
        $welcome->status = $request->status ?? 0;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $folder = 'welcome_modals' . str_replace(' ', '_', $request->name);
                $filePath = $this->storeFile($file, $folder); // uses your storeFile method
                $welcome->image_exc = $filePath;
            }
        }
        if ($request->hasFile('sm_image')) {
            $file = $request->file('sm_image');
            if ($file->isValid()) {
                $folder = 'welcome_modals' . str_replace(' ', '_', $request->name);
                $filePath = $this->storeFile($file, $folder); // uses your storeFile method
                $welcome->image_sm = $filePath;
            }
        }


        $welcome->save();

        return response()->json([
            'status' => true,
            'message' => 'Welcome modal saved successfully!',
            'data' => $welcome
        ]);
    }

    public function update(Request $request, $id)
    {
        try {

            $welcome = WelcomeModal::findOrFail($id);

            $welcome->title = $request->title ?? $welcome->title;
            $welcome->description = $request->description ?? $welcome->description;
            $welcome->url_exc = $request->url ?? $welcome->url;
            $welcome->url_sm = $request->sm_url ?? $welcome->sm_url;
            $welcome->status = $request->status ?? $welcome->status;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                if ($file->isValid()) {
                    $folder = 'welcome_modals' . str_replace(' ', '_', $request->name ?? 'welcome_modals');
                    $filePath = $this->storeFile($file, $folder); // your custom file upload method
                    $welcome->image_exc = $filePath;
                }
            }
            if ($request->hasFile('sm_image')) {
                $file = $request->file('sm_image');
                if ($file->isValid()) {
                    $folder = 'welcome_modals' . str_replace(' ', '_', $request->name ?? 'welcome_modals');
                    $filePath = $this->storeFile($file, $folder); // your custom file upload method
                    $welcome->image_sm = $filePath;
                }
            }

            $welcome->save();

            return response()->json([
                'status' => true,
                'message' => 'Welcome modal updated successfully!',
                'data' => $welcome
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update modal.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function statusUpdate(Request $request, $id)
    {
        try {
            $status = $request->input('status', 0);

            WelcomeModal::where('id', '!=', $id)->update(['status' => 0]);

            $modal = WelcomeModal::findOrFail($id);
            $modal->status = $status;
            $modal->save();

            return response()->json([
                'status' => true,
                'message' => 'Welcome modal status updated successfully',
                'data' => $modal,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function storeFile($file, $folder, $disk = 'public')
    {
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('uploads/' . $folder, $filename, $disk);
        return Storage::disk($disk)->url($path);
    }

    public function destroy(string $id)
    {
        $WelcomeModal = WelcomeModal::where('id', $id)->firstOrFail();
        if (!$WelcomeModal) {
            return response()->json(['status' => false, 'message' => 'WelcomeModal not found'], 404);
        }

        $WelcomeModal->delete();
        return response()->json(['status' => true, 'message' => 'WelcomeModal deleted successfully'], 200);
    }
}
