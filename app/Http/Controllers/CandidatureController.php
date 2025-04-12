<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use App\Models\Offre;
use App\Models\LettreMotivation;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidatureController extends Controller
{
    public function index()
    {
        // If user is a chercheur emploi, show their applications
        if (Auth::user()->type === 'chercheur_emploi') {
            $candidatures = Auth::user()->chercheurEmploi->candidatures()
                ->with(['offre.entreprise', 'documentCV'])
                ->latest()
                ->paginate(10);

            return view('candidatures.index', compact('candidatures'));
        }

        // If user is a recruteur, show applications for their job offers
        if (Auth::user()->type === 'recruteur') {
            $offres = Auth::user()->recruteur->offres()->pluck('id');
            $candidatures = Candidature::whereIn('offre_id', $offres)
                ->with(['chercheurEmploi.utilisateur', 'offre', 'documentCV'])
                ->latest()
                ->paginate(10);

            return view('candidatures.recruiter-index', compact('candidatures'));
        }

        // Redirect if user type is not valid
        return redirect()->route('home')->with('error', 'Accès non autorisé');
    }

    public function create(Offre $offre)
    {
        // Check if the offer is still active
        if ($offre->statut !== 'active' || $offre->date_limite < now()) {
            return redirect()->route('offres.show', $offre)->with('error', 'Cette offre n\'est plus disponible');
        }

        $chercheurEmploi = Auth::user()->chercheurEmploi;

        // Check if user already applied
        $alreadyApplied = $chercheurEmploi->candidatures()
            ->where('offre_id', $offre->id)
            ->exists();

        if ($alreadyApplied) {
            return redirect()->route('offres.show', $offre)->with('error', 'Vous avez déjà postulé à cette offre');
        }

        // Get user's active CV and letters
        $cv = $chercheurEmploi->activeCV();
        if (!$cv) {
            return redirect()->route('profile.cv')->with('error', 'Veuillez d\'abord importer votre CV');
        }

        $lettres = $chercheurEmploi->lettresMotivation;

        return view('candidatures.create', compact('offre', 'cv', 'lettres'));
    }

    public function store(Request $request, Offre $offre)
    {
        $request->validate([
            'document_cv_id' => 'required|exists:document_cvs,id',
            'lettre_motivation_id' => 'nullable|exists:lettres_motivation,id',
            'nouvelle_lettre' => 'nullable|required_without:lettre_motivation_id|string|min:100',
            'nouvelle_lettre_titre' => 'nullable|required_with:nouvelle_lettre|string|max:255',
        ]);

        $chercheurEmploi = Auth::user()->chercheurEmploi;

        // Create new cover letter if provided
        if ($request->filled('nouvelle_lettre')) {
            $lettre = $chercheurEmploi->lettresMotivation()->create([
                'titre' => $request->nouvelle_lettre_titre,
                'contenu' => $request->nouvelle_lettre,
                'date_creation' => now(),
            ]);
            $lettreMotivationId = $lettre->id;
        } else {
            $lettreMotivationId = $request->lettre_motivation_id;
        }

        // Create the application
        $candidature = $chercheurEmploi->candidatures()->create([
            'offre_id' => $offre->id,
            'document_cv_id' => $request->document_cv_id,
            'lettre_motivation_id' => $lettreMotivationId,
            'date_postulation' => now(),
            'statut' => 'en_attente',
        ]);

        // Create notification for the recruiter
        $recruteur = $offre->recruteur;
        Notification::create([
            'type' => 'nouvelle_candidature',
            'contenu' => "Nouvelle candidature pour l'offre: {$offre->titre}",
            'utilisateur_id' => $recruteur->utilisateur_id,
        ]);

        return redirect()->route('candidatures.index')->with('success', 'Votre candidature a été soumise avec succès');
    }

    public function show(Candidature $candidature)
    {
        // Check if user is authorized to view this application
        $this->authorize('view', $candidature);

        return view('candidatures.show', compact('candidature'));
    }

    public function updateStatus(Request $request, Candidature $candidature)
    {
        // Check if user is authorized to update this application
        $this->authorize('update', $candidature);

        $request->validate([
            'statut' => 'required|in:en_attente,en_cours_analyse,entretien,accepte,refuse',
            'note_recruteur' => 'nullable|integer|min:0|max:5',
            'commentaire' => 'nullable|string',
        ]);

        $oldStatus = $candidature->statut;
        $candidature->update([
            'statut' => $request->statut,
            'note_recruteur' => $request->note_recruteur,
            'commentaire' => $request->commentaire,
        ]);

        // Notify the job seeker of the status change
        if ($oldStatus !== $request->statut) {
            Notification::create([
                'type' => 'statut_candidature',
                'contenu' => "Le statut de votre candidature pour l'offre {$candidature->offre->titre} a changé: {$request->statut}",
                'utilisateur_id' => $candidature->chercheurEmploi->utilisateur_id,
            ]);
        }

        return redirect()->back()->with('success', 'Statut de la candidature mis à jour');
    }

    public function destroy(Candidature $candidature)
    {
        // Check if user is authorized to delete this application
        $this->authorize('delete', $candidature);

        // Only allow cancellation if status is 'en_attente'
        if ($candidature->statut !== 'en_attente') {
            return redirect()->back()->with('error', 'Vous ne pouvez pas annuler cette candidature');
        }

        $candidature->delete();

        return redirect()->route('candidatures.index')->with('success', 'Candidature annulée avec succès');
    }

    public function export(Request $request)
    {
        // Check if user is a recruiter
        if (Auth::user()->type !== 'recruteur') {
            return redirect()->route('home')->with('error', 'Accès non autorisé');
        }

        $request->validate([
            'offre_id' => 'required|exists:offres,id',
        ]);

        $offre = Offre::findOrFail($request->offre_id);

        // Check if user is the owner of this job offer
        if ($offre->recruteur_id !== Auth::user()->recruteur->id) {
            return redirect()->route('home')->with('error', 'Accès non autorisé');
        }

        // Export logic - could generate CSV, Excel, etc.
        // This is a placeholder - you would implement actual export functionality

        return redirect()->back()->with('success', 'Export des candidatures en cours...');
    }
}
