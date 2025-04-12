<?php

namespace App\Http\Controllers;

use App\Models\ProfilProfessionnel;
use App\Models\ExperienceProfessionnelle;
use App\Models\FormationAcademique;
use App\Models\Competence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $chercheurEmploi = Auth::user()->chercheurEmploi;
        $profil = $chercheurEmploi->profilProfessionnel;

        if (!$profil) {
            return redirect()->route('profile.create')->with('info', 'Veuillez créer votre profil professionnel');
        }

        return view('profiles.show', compact('profil'));
    }

    public function create()
    {
        $chercheurEmploi = Auth::user()->chercheurEmploi;

        // Check if profile already exists
        if ($chercheurEmploi->profilProfessionnel) {
            return redirect()->route('profile.edit')->with('info', 'Vous avez déjà un profil professionnel');
        }

        $competences = Competence::all();

        return view('profiles.create', compact('competences'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'resume_competences' => 'nullable|string',
            'competences' => 'array',
            'competences.*' => 'exists:competences,id',
        ]);

        $chercheurEmploi = Auth::user()->chercheurEmploi;

        // Create the profile
        $profil = ProfilProfessionnel::create([
            'chercheur_emploi_id' => $chercheurEmploi->id,
            'titre' => $request->titre,
            'resume_competences' => $request->resume_competences,
            'extracted_from_cv' => false,
        ]);

        // Attach competencies
        if ($request->has('competences')) {
            $profil->competences()->attach($request->competences);
        }

        return redirect()->route('profile.experiences.create')->with('success', 'Profil professionnel créé avec succès');
    }

    public function edit()
    {
        $chercheurEmploi = Auth::user()->chercheurEmploi;
        $profil = $chercheurEmploi->profilProfessionnel;

        if (!$profil) {
            return redirect()->route('profile.create')->with('info', 'Veuillez créer votre profil professionnel');
        }

        $competences = Competence::all();
        $selectedCompetences = $profil->competences->pluck('id')->toArray();

        return view('profiles.edit', compact('profil', 'competences', 'selectedCompetences'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'resume_competences' => 'nullable|string',
            'competences' => 'array',
            'competences.*' => 'exists:competences,id',
        ]);

        $chercheurEmploi = Auth::user()->chercheurEmploi;
        $profil = $chercheurEmploi->profilProfessionnel;

        // Update the profile
        $profil->update([
            'titre' => $request->titre,
            'resume_competences' => $request->resume_competences,
        ]);

        // Sync competencies
        $profil->competences()->sync($request->competences);
        return redirect()->route('profile.show')->with('success', 'Profil professionnel mis à jour avec succès');
    }
    public function addExperience(Request $request)
    {
        $request->validate([
            'poste' => 'required|string|max:255',
            'entreprise' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'description' => 'nullable|string',
        ]);

        $chercheurEmploi = Auth::user()->chercheurEmploi;
        $profil = $chercheurEmploi->profilProfessionnel;

        // Create the experience
        ExperienceProfessionnelle::create([
            'profil_professionnel_id' => $profil->id,
            'poste' => $request->poste,
            'entreprise' => $request->entreprise,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'description' => $request->description,
        ]);

        return redirect()->route('profile.show')->with('success', 'Expérience professionnelle ajoutée avec succès');
    }
    public function addFormation(Request $request)
    {
        $request->validate([
            'diplome' => 'required|string|max:255',
            'etablissement' => 'required|string|max:255',
            'date_obtention' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $chercheurEmploi = Auth::user()->chercheurEmploi;
        $profil = $chercheurEmploi->profilProfessionnel;

        // Create the formation
        FormationAcademique::create([
            'profil_professionnel_id' => $profil->id,
            'diplome' => $request->diplome,
            'etablissement' => $request->etablissement,
            'date_obtention' => $request->date_obtention,
            'description' => $request->description,
        ]);

        return redirect()->route('profile.show')->with('success', 'Formation académique ajoutée avec succès');
    }
    public function deleteExperience(ExperienceProfessionnelle $experience)
    {
        // Check if user is authorized to delete this experience
        $this->authorize('delete', $experience);

        $experience->delete();

        return redirect()->route('profile.show')->with('success', 'Expérience professionnelle supprimée avec succès');
    }
    public function deleteFormation(FormationAcademique $formation)
    {
        // Check if user is authorized to delete this formation
        $this->authorize('delete', $formation);

        $formation->delete();

        return redirect()->route('profile.show')->with('success', 'Formation académique supprimée avec succès');
    }
    public function deleteCompetence(Competence $competence)
    {
        // Check if user is authorized to delete this competence
        $this->authorize('delete', $competence);

        $competence->delete();

        return redirect()->route('profile.show')->with('success', 'Compétence supprimée avec succès');
    }
    public function deleteProfile()
    {
        $chercheurEmploi = Auth::user()->chercheurEmploi;
        $profil = $chercheurEmploi->profilProfessionnel;

        // Check if user is authorized to delete this profile
        $this->authorize('delete', $profil);

        // Delete the profile and its related experiences and formations
        $profil->experiences()->delete();
        $profil->formations()->delete();
        $profil->competences()->detach();
        $profil->delete();

        return redirect()->route('home')->with('success', 'Profil professionnel supprimé avec succès');
    }
    public function downloadCV()
    {
        $chercheurEmploi = Auth::user()->chercheurEmploi;
        $cv = $chercheurEmploi->documentCV;

        if (!$cv) {
            return redirect()->route('profile.show')->with('error', 'Aucun CV trouvé');
        }

        return response()->download(storage_path('app/' . $cv->chemin_stockage), $cv->nom_fichier);
    }
    public function uploadCV(Request $request)
    {
        $request->validate([
            'cv' => 'required|file|mimes:pdf|max:2048',
        ]);

        $chercheurEmploi = Auth::user()->chercheurEmploi;

        // Store the CV
        $path = $request->file('cv')->store('cvs');

        // Create or update the CV record
        $chercheurEmploi->documentCV()->updateOrCreate([], [
            'nom_fichier' => $request->file('cv')->getClientOriginalName(),
            'chemin_stockage' => $path,
        ]);

        return redirect()->route('profile.show')->with('success', 'CV téléchargé avec succès');
    }
}
