<?php

namespace App\Http\Controllers;

use App\Models\WelcomeModal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WelcomeModalController extends Controller
{

    public function welcomeModal()
    {
        $welcome = WelcomeModal::first();
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
            $welcome->image_exc = $this->storeFile($request->file('image'), 'welcome_modals');
        }

        if ($request->hasFile('sm_image')) {
            $welcome->image_sm = $this->storeFile($request->file('sm_image'), 'welcome_modals');
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
                $welcome->image_exc = $this->storeFile($request->file('image'), 'welcome_modals');
            }

            if ($request->hasFile('sm_image')) {
                $welcome->image_sm = $this->storeFile($request->file('sm_image'), 'welcome_modals');
            }

            $welcome->save();

            return response()->json([
                'status' => true,
                'message' => 'Welcome modal updated successfully!',
                'data' => $welcome
            ],200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update modal.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    private function storeFile($file, $folder)
    {
        if (!$file) return null;
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('uploads/' . $folder, $filename, 'public');
        return Storage::url($path);
    }
}
