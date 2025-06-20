<?php

namespace App\Http\Controllers;

use App\Jobs\SendContactUsMail;
use App\Mail\ContactUsMail;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ContactUsController extends Controller
{
    public function index()
    {
        $ContactUs = ContactUs::get();
        if ($ContactUs->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'ContactUs not found'
            ], 200);
        }
        return response()->json([
            'status' => true,
            'data' => $ContactUs,
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $contactData = new ContactUs();
            $contactData->name = $request->name;
            $contactData->email = $request->email;
            $contactData->number = $request->phone;
            $contactData->address = $request->address;
            $contactData->message = $request->message;

            if ($request->hasFile('screenshot') && $request->file('screenshot')->isValid()) {
                $contactData->image = $this->storeFile($request->file('screenshot'), 'contactUs');
            }

            $contactData->save();

            $emails = ['smit.bhagat@smsforyou.biz', 'janak.rana@smsforyou.biz'];
            // $emails = ['kinjal@yopmail.com', 'kinjal1@yopmail.com'];
            foreach ($emails as $email) {
                dispatch(new SendContactUsMail($contactData->id, $email));
            }

            return response()->json(['status' => true, 'message' => 'contactData craete successfully', 'data' => $contactData], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to contactData '], 404);
        }
    }

    public function show($id) 
    {
        $contactData = ContactUs::find($id);

        if (!$contactData) {
            return response()->json(['status' => false, 'message' => 'contactData not found'], 200);
        }

        return response()->json(['status' => true, 'data' => $contactData], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $contactData = ContactUs::findOrFail($id); // existing record fetch

            $contactData->name = $request->name ?? $contactData->name;
            $contactData->email = $request->email ?? $contactData->email;
            $contactData->number = $request->phone ?? $contactData->number;
            $contactData->address = $request->address ?? $contactData->address;
            $contactData->message = $request->message ?? $contactData->message;

            if ($request->hasFile('screenshot') && $request->file('screenshot')->isValid()) {
                $contactData->image = $this->storeFile($request->file('screenshot'), 'contactUs');
            }

            $contactData->save();

            return response()->json(['status' => true, 'message' => 'contactData updated successfully', 'data' => $contactData], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to update contactData'], 404);
        }
    }

    public function destroy(string $id)
    {
        $contactData = ContactUs::where('id', $id)->firstOrFail();
        if (!$contactData) {
            return response()->json(['status' => false, 'message' => 'contactData not found'], 404);
        }

        $contactData->delete();
        return response()->json(['status' => true, 'message' => 'contactData deleted successfully'], 200);
    }


    private function storeFile($file, $folder, $disk = 'public')
    {
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('uploads/' . $folder, $filename, $disk);
        return Storage::disk($disk)->url($path);
    }
}
