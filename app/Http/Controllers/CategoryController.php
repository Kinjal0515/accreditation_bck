<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
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
            return response()->json(['status' => true, 'message' => 'categoryData craete successfully', 'data' => $categoryData,], 200);
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

            return response()->json(['status' => true, 'message' => 'categoryData updated successfully', 'data' => $categoryData], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to update categoryData'], 404);
        }
    }

    public function destroy(string $id)
    {
        $categoryData = Category::where('id', $id)->firstOrFail();
        if (!$categoryData) {
            return response()->json(['status' => false, 'message' => 'categoryData not found'], 404);
        }

        $categoryData->delete();
        return response()->json(['status' => true, 'message' => 'categoryData deleted successfully'], 200);
    }

    private function storeFile($file, $folder, $disk = 'public')
    {
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('uploads/' . $folder, $filename, $disk);
        return Storage::disk($disk)->url($path);
    }

}
