<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\Specialite;
use App\Models\Competence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OffreController extends Controller
{
    public function index(Request $request)
    {
        $query = Offre::with(['entreprise', 'specialites', 'competences'])
            ->where('statut', 'active')
            ->where('date_limite', '>=', now());

        // Apply filters if provided
        if ($request->filled('titre')) {
            $query->where('titre', 'like', '%' . $request->titre . '%');
        }

        if ($request->filled('lieu')) {
            $query->where('lieu_travail', 'like', '%' . $request->lieu . '%');
        }

        if ($request->filled('type_contrat')) {
            $query->where('type_contrat', $request->type_contrat);
        }

        if ($request->filled('specialite')) {
            $query->whereHas('specialites', function($q) use ($request) {
                $q->where('specialites.id', $request->specialite);
            });
        }

        $offres = $query->latest()->paginate(15);
        $specialites = Specialite::all();

        return view('offres.index', compact('offres', 'specialites'));
    }

    public function show(Offre $offre)
    {
        // Increment view count
        $offre->increment('nombre_vues');

        // Calculate match score if user is a job seeker with an active CV
        $matchScore = null;
        if (Auth::check() && Auth::user()->type === 'chercheur_emploi') {
            $chercheur = Auth::user()->chercheurEmploi;
            $activeCV = $chercheur->activeCV();
            if ($activeCV) {
                $matchScore = $offre->calculerMatchScore($activeCV);
            }
        }

        return view('offres.show', compact('offre', 'matchScore'));
    }

    public function create()
    {
        // Check if user is a recruiter
        if (Auth::user()->type !== 'recruteur') {
            return redirect()->route('home')->with('error', 'Accès non autorisé');
        }

        $specialites = Specialite::all();
        $competences = Competence::all();

        return view('offres.create', compact('specialites', 'competences'));
    }

    public function store(Request $request)
    {
        // Check if user is a recruiter
        if (Auth::user()->type !== 'recruteur') {
            return redirect()->route('home')->with('error', 'Accès non autorisé');
        }

        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'date_limite' => 'required|date|after:today',
            'salaire_min' => 'nullable|numeric|min:0',
            'salaire_max' => 'nullable|numeric|gte:salaire_min',
            'type_contrat' => 'required|string',
            'lieu_travail' => 'required|string',
            'niveau_etudes_requis' => 'nullable|string',
            'experience_requise' => 'nullable|string',
            'specialites' => 'required|array',
            'specialites.*' => 'exists:specialites,id',
            'competences' => 'required|array',
            'competences.*' => 'exists:competences,id',
        ]);

        $recruteur = Auth::user()->recruteur;

        // Create the job offer
        $offre = Offre::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'date_publication' => now(),
            'date_limite' => $request->date_limite,
            'salaire_min' => $request->salaire_min,
            'salaire_max' => $request->salaire_max,
            'type_contrat' => $request->type_contrat,
            'lieu_travail' => $request->lieu_travail,
            'niveau_etudes_requis' => $request->niveau_etudes_requis,
            'experience_requise' => $request->experience_requise,
            'entreprise_id' => $recruteur->entreprise_id,
            'recruteur_id' => $recruteur->id,
            'statut' => 'active',
        ]);

        // Attach specialties and competencies
        $offre->specialites()->attach($request->specialites);
        $offre->competences()->attach($request->competences);

        return redirect()->route('offres.show', $offre)->with('success', 'Offre publiée avec succès');
    }

    public function edit(Offre $offre)
    {
        // Check if user is the owner of this job offer
        if (Auth::user()->recruteur->id !== $offre->recruteur_id) {
            return redirect()->route('home')->with('error', 'Accès non autorisé');
        }

        $specialites = Specialite::all();
        $competences = Competence::all();

        return view('offres.edit', compact('offre', 'specialites', 'competences'));
    }

    public function update(Request $request, Offre $offre)
    {
        // Check if user is the owner of this job offer
        if (Auth::user()->recruteur->id !== $offre->recruteur_id) {
            return redirect()->route('home')->with('error', 'Accès non autorisé');
        }

        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'date_limite' => 'required|date|after:today',
            'salaire_min' => 'nullable|numeric|min:0',
            'salaire_max' => 'nullable|numeric|gte:salaire_min',
            'type_contrat' => 'required|string',
            'lieu_travail' => 'required|string',
            'niveau_etudes_requis' => 'nullable|string',
            'experience_requise' => 'nullable|string',
            'specialites' => 'required|array',
            'specialites.*' => 'exists:specialites,id',
            'competences' => 'required|array',
            'competences.*' => 'exists:competences,id',
            'statut' => 'required|in:active,inactive',
        ]);

        // Update the job offer
        $offre->update([
            'titre' => $request->titre,
            'description' => $request->description,
            'date_limite' => $request->date_limite,
            'salaire_min' => $request->salaire_min,
            'salaire_max' => $request->salaire_max,
            'type_contrat' => $request->type_contrat,
            'lieu_travail' => $request->lieu_travail,
            'niveau_etudes_requis' => $request->niveau_etudes_requis,
            'experience_requise' => $request->experience_requise,
            'statut' => $request->statut,
        ]);

        // Sync specialties and competencies
        $offre->specialites()->sync($request->specialites);
        $offre->competences()->sync($request->competences);

        return redirect()->route('offres.show', $offre)->with('success', 'Offre mise à jour avec succès');
    }

    public function destroy(Offre $offre)
    {
        // Check if user is the owner of this job offer
        if (Auth::user()->recruteur->id !== $offre->recruteur_id) {
            return redirect()->route('home')->with('error', 'Accès non autorisé');
        }

        // Instead of deleting, change status to 'fermée'
        $offre->update(['statut' => 'fermée']);

        return redirect()->route('recruteur.offres')->with('success', 'Offre fermée avec succès');
    }

    public function duplicate(Offre $offre)
    {
        // Check if user is the owner of this job offer
        if (Auth::user()->recruteur->id !== $offre->recruteur_id) {
            return redirect()->route('home')->with('error', 'Accès non autorisé');
        }

        // Clone the job offer
        $newOffre = $offre->replicate();
        $newOffre->titre = "Copie de " . $offre->titre;
        $newOffre->date_publication = now();
        $newOffre->date_limite = now()->addMonth();
        $newOffre->nombre_vues = 0;
        $newOffre->save();

        // Clone relationships
        $newOffre->specialites()->attach($offre->specialites()->pluck('specialites.id'));
        $newOffre->competences()->attach($offre->competences()->pluck('competences.id'));

        return redirect()->route('offres.edit', $newOffre)->with('success', 'Offre dupliquée avec succès');
    }

    public function myOffers()
    {
        // Check if user is a recruiter
        if (Auth::user()->type !== 'recruteur') {
            return redirect()->route('home')->with('error', 'Accès non autorisé');
        }

        $offres = Auth::user()->recruteur->offres()
            ->withCount('candidatures')
            ->latest()
            ->paginate(10);

        return view('offres.my-offers', compact('offres'));
    }
}
