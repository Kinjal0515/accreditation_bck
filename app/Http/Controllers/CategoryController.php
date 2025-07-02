<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CatLayout;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function listData()
    {
        $Category = Category::get();
        if ($Category->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found'
            ], 200);
        }
        return response()->json([
            'status' => true,
            'data' => $Category,
        ], 200);
    }

    public function index($user_id)
    {
        $Category = Category::where('user_id', $user_id)->get();
        if ($Category->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found'
            ], 200);
        }
        return response()->json([
            'status' => true,
            'data' => $Category,
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $categoryData = new Category();
            $categoryData->user_id = $request->user_id;
            $categoryData->title = $request->title;
            $categoryData->description = $request->description;

            if ($request->hasFile('background_image')) {
                $file = $request->file('background_image');
                if ($file->isValid()) {
                    $folder = 'background_image/' . str_replace(' ', '_', $request->name);
                    $filePath = $this->storeFile($file, $folder); // uses your storeFile method
                    $categoryData->background_image = $filePath;
                }
            }

            $categoryData->save();

            if ($request->has('layout')) {
                $layout = $request->input('layout');

                if (is_string($layout)) {
                    $layout = json_decode($layout, true);
                }

                if (is_array($layout)) {
                    $this->storeLayout($layout, $categoryData->id);
                }
            }

            return response()->json(['status' => true, 'message' => 'Category data created successfully', 'data' => $categoryData,], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to categoryData '], 404);
        }
    }

    public function show($id)
    {
        $Category = Category::find($id);

        if (!$Category) {
            return response()->json(['status' => false, 'message' => 'Category not found'], 200);
        }

        return response()->json(['status' => true, 'data' => $Category], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $categoryData = Category::findOrFail($id);

            $categoryData->user_id = $request->user_id;
            $categoryData->title = $request->title;
            $categoryData->description = $request->description;

            if ($request->hasFile('background_image')) {
                $file = $request->file('background_image');
                if ($file->isValid()) {
                    $folder = 'background_image/' . str_replace(' ', '_', $request->name ?? 'category');
                    $filePath = $this->storeFile($file, $folder); // your custom file upload method
                    $categoryData->background_image = $filePath;
                }
            }
            $categoryData->save();
            if ($request->has('layout')) {
                $layout = $request->input('layout');

                if (is_string($layout)) {
                    $layout = json_decode($layout, true);
                }

                if (is_array($layout)) {
                    $this->storeLayout($layout, $id);
                }
            }
            return response()->json(['status' => true, 'message' => 'Category data update successfully', 'data' => $categoryData], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to update categoryData'], 404);
        }
    }

    public function destroy(string $id)
    {
        $categoryData = Category::where('id', $id)->firstOrFail();
        if (!$categoryData) {
            return response()->json(['status' => false, 'message' => 'Category not found'], 404);
        }

        $categoryData->delete();
        return response()->json(['status' => true, 'message' => 'Category deleted successfully'], 200);
    }

    private function storeFile($file, $folder, $disk = 'public')
    {
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('uploads/' . $folder, $filename, $disk);
        return Storage::disk($disk)->url($path);
    }

    private function storeLayout(array $layout, $categoryId)
    {
        CatLayout::updateOrCreate(
            ['category_id' => $categoryId],
            [
                'user_photo' => isset($layout['userPhoto']) ? json_encode($layout['userPhoto']) : null,
                'zones'      => isset($layout['zoneGroup']) ? json_encode($layout['zoneGroup']) : null,
                'qr_code'    => isset($layout['qrCode']) ? json_encode($layout['qrCode']) : null,
                'text_1'     => isset($layout['textValue_0']) ? json_encode($layout['textValue_0']) : null,
                'text_2'     => isset($layout['textValue_1']) ? json_encode($layout['textValue_1']) : null,
                'text_3'     => isset($layout['textValue_2']) ? json_encode($layout['textValue_2']) : null,
            ]
        );
    }

    public function layoutList($user_id)
    {
        // $user = User::with('comp')->find($user_id);

        // $categoryId = $user->comp->category_id ?? null;

        // if (!$categoryId) {
        //     return response()->json(['status' => false, 'message' => 'Category not found'], 404);
        // }

        $layout = CatLayout::where('category_id', $user_id)->first();

        return response()->json(['status' => true, 'data' => $layout], 200);
    }
}
