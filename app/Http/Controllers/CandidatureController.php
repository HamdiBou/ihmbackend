<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use App\Models\Offre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CandidatureController extends Controller
{
    public function index()
    {
        return Candidature::where('job_seeker_id', Auth::id())
                         ->with(['offre', 'cv'])
                         ->paginate(10);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'offre_id' => 'required|exists:offres,id',
            'cv_id' => 'required|exists:cvs,id',
            'lettre_motivation' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check if already applied
        $existingApplication = Candidature::where('job_seeker_id', Auth::id())
            ->where('offre_id', $request->offre_id)
            ->first();

        if ($existingApplication) {
            return response()->json([
                'message' => 'You have already applied for this position'
            ], 422);
        }

        // Check if offer is still active
        $offre = Offre::find($request->offre_id);
        if (!$offre || $offre->statut !== 'active') {
            return response()->json([
                'message' => 'This job offer is no longer active'
            ], 422);
        }

        $candidature = Candidature::create([
            'job_seeker_id' => Auth::id(),
            'offre_id' => $request->offre_id,
            'cv_id' => $request->cv_id,
            'lettre_motivation' => $request->lettre_motivation,
            'statut' => 'submitted',
            'date_candidature' => now()
        ]);

        return response()->json($candidature, 201);
    }

    public function show($id)
    {
        $candidature = Candidature::where('id', $id)
            ->where('job_seeker_id', Auth::id())
            ->with(['offre', 'cv'])
            ->first();

        if (!$candidature) {
            return response()->json(['message' => 'Candidature not found'], 404);
        }

        return response()->json($candidature);
    }

    public function destroy($id)
    {
        $candidature = Candidature::where('id', $id)
            ->where('job_seeker_id', Auth::id())
            ->first();

        if (!$candidature) {
            return response()->json(['message' => 'Candidature not found'], 404);
        }

        if ($candidature->statut !== 'submitted') {
            return response()->json([
                'message' => 'Cannot withdraw application at current status'
            ], 422);
        }

        $candidature->delete();

        return response()->json(['message' => 'Application withdrawn successfully']);
    }
}
