<?php

namespace App\Http\Controllers;

use App\Models\DocumentCV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentCVController extends Controller
{
    public function index()
    {
        return DocumentCV::where('job_seeker_id', Auth::id())
                        ->latest()
                        ->get();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cv' => 'required|file|mimes:pdf,doc,docx|max:2048',
            'title' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $file = $request->file('cv');
            $path = $file->store('cvs/' . Auth::id());

            $cv = DocumentCV::create([
                'job_seeker_id' => Auth::id(),
                'storage_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'title' => $request->title,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize()
            ]);

            return response()->json($cv, 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error uploading CV',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $cv = DocumentCV::where('id', $id)
            ->where('job_seeker_id', Auth::id())
            ->first();

        if (!$cv) {
            return response()->json(['message' => 'CV not found'], 404);
        }

        return response()->json($cv);
    }

    public function download($id)
    {
        $cv = DocumentCV::where('id', $id)
            ->where('job_seeker_id', Auth::id())
            ->first();

        if (!$cv) {
            return response()->json(['message' => 'CV not found'], 404);
        }

        if (!Storage::exists($cv->storage_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::download($cv->storage_path, $cv->file_name);
    }

    public function destroy($id)
    {
        $cv = DocumentCV::where('id', $id)
            ->where('job_seeker_id', Auth::id())
            ->first();

        if (!$cv) {
            return response()->json(['message' => 'CV not found'], 404);
        }

        try {
            if (Storage::exists($cv->storage_path)) {
                Storage::delete($cv->storage_path);
            }

            $cv->delete();

            return response()->json(['message' => 'CV deleted successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting CV',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
