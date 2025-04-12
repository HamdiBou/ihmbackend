<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OffreController extends Controller
{
    public function index()
    {
        return Offre::where('statut', 'active')
                   ->with('employer')
                   ->paginate(10);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'competences_requises' => 'required|array',
            'date_limite' => 'required|date|after:today',
            'type_emploi' => 'required|string',
            'localisation' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $employer = Auth::user()->employer;

        if (!$employer) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $offre = Offre::create([
            'employer_id' => $employer->id,
            'statut' => 'active',
            ...$request->validated()
        ]);

        return response()->json($offre, 201);
    }

    public function show($id)
    {
        $offre = Offre::with('employer')->find($id);

        if (!$offre) {
            return response()->json(['message' => 'Offre not found'], 404);
        }

        return response()->json($offre);
    }

    public function update(Request $request, $id)
    {
        $offre = Offre::find($id);

        if (!$offre) {
            return response()->json(['message' => 'Offre not found'], 404);
        }

        if ($offre->employer_id !== Auth::user()->employer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'titre' => 'string|max:255',
            'description' => 'string',
            'competences_requises' => 'array',
            'date_limite' => 'date|after:today',
            'type_emploi' => 'string',
            'localisation' => 'string',
            'statut' => 'in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $offre->update($request->validated());

        return response()->json($offre);
    }

    public function destroy($id)
    {
        $offre = Offre::find($id);

        if (!$offre) {
            return response()->json(['message' => 'Offre not found'], 404);
        }

        if ($offre->employer_id !== Auth::user()->employer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $offre->delete();

        return response()->json(['message' => 'Offre deleted successfully']);
    }

    public function employerOffres()
    {
        $employer = Auth::user()->employer;
        return Offre::where('employer_id', $employer->id)
                   ->paginate(10);
    }

    public function offreCandidatures($id)
    {
        $offre = Offre::find($id);

        if (!$offre) {
            return response()->json(['message' => 'Offre not found'], 404);
        }

        if ($offre->employer_id !== Auth::user()->employer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $offre->candidatures()->with('jobSeeker')->paginate(10);
    }
}
