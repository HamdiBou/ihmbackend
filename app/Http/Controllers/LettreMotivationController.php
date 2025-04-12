<?php

namespace App\Http\Controllers;

use App\Models\LettreMotivation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;


class LettreMotivationController extends Controller
{
    public function index()
    {
        $chercheurEmploi = Auth::user()->chercheurEmploi;
        $lettres = $chercheurEmploi->lettresMotivation()->latest()->paginate(10);

        return view('lettres.index', compact('lettres'));
    }

    public function create()
    {
        return view('lettres.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string|min:100',
        ]);

        $chercheurEmploi = Auth::user()->chercheurEmploi;

        $lettre = $chercheurEmploi->lettresMotivation()->create([
            'titre' => $request->titre,
            'contenu' => $request->contenu,
            'date_creation' => now(),
        ]);

        return redirect()->route('lettres.index')->with('success', 'Lettre de motivation créée avec succès');
    }

    public function show(LettreMotivation $lettre)
    {
        // Check if user is authorized to view this cover letter
        if (Auth::user()->chercheurEmploi->id !== $lettre->chercheur_emploi_id) {
            return redirect()->route('home')->with('error', 'Accès non autorisé');
        }

        return view('lettres.show', compact('lettre'));
    }

    public function edit(LettreMotivation $lettre)
    {
        // Check if user is authorized to edit this cover letter
        if (Auth::user()->chercheurEmploi->id !== $lettre->chercheur_emploi_id) {
            return redirect()->route('home')->with('error', 'Accès non autorisé');
        }

        return view('lettres.edit', compact('lettre'));
    }

    public function update(Request $request, LettreMotivation $lettre)
    {
        // Check if user is authorized to update this cover letter
        if (Auth::user()->chercheurEmploi->id !== $lettre->chercheur_emploi_id) {
            return redirect()->route('home')->with('error', 'Accès non autorisé');
        }

        $request->validate([
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string|min:100',
        ]);

        $lettre->update([
            'titre' => $request->titre,
            'contenu' => $request->contenu,
        ]);

        return redirect()->route('lettres.index')->with('success', 'Lettre de motivation mise à jour avec succès');
    }

    public function destroy(LettreMotivation $lettre)
    {
        // Check if user is authorized to delete this cover letter
        if (Auth::user()->chercheurEmploi->id !== $lettre->chercheur_emploi_id) {
            return redirect()->route('home')->with('error', 'Accès non autorisé');
        }

        // Don't delete if used in active applications
        $isUsed = $lettre->candidatures()->exists();
        if ($isUsed) {
            return redirect()->route('lettres.index')->with('error', 'Cette lettre est utilisée dans une ou plusieurs candidatures et ne peut pas être supprimée');
        }

        $lettre->delete();

        return redirect()->route('lettres.index')->with('success', 'Lettre de motivation supprimée avec succès');
    }

    public function download(LettreMotivation $lettre)
    {
        // Check if user is authorized to download this cover letter
        if (Auth::user()->chercheurEmploi->id !== $lettre->chercheur_emploi_id) {
            return redirect()->route('home')->with('error', 'Accès non autorisé');
        }

        // Generate PDF from the letter content
        $pdf = pdf::loadView('lettres.pdf', compact('lettre'));

        return $pdf->download($lettre->titre . '.pdf');
    }
}
