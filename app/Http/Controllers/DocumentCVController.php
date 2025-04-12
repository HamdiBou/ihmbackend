<?php

namespace App\Http\Controllers;
use App\Models\DocumentCV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class DocumentCVController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'cv' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $chercheurEmploi = Auth::user()->chercheurEmploi;
        $cv = $chercheurEmploi->importCV($request);

        // Extract text and analyze the CV
        $cv->extraireTexte();
        $competences = $cv->analyserCompetences();

        // Update or create a professional profile
        $profil = $chercheurEmploi->profilProfessionnel;
        if (!$profil) {
            $profil = $chercheurEmploi->profilProfessionnel()->create([
                'titre' => $chercheurEmploi->utilisateur->nom . ' ' . $chercheurEmploi->utilisateur->prenom,
                'extracted_from_cv' => true,
            ]);
        }

        // Try to generate profile data from CV
        $profil->genererDepuisCV($cv);

        return redirect()->back()->with('success', 'CV uploadé avec succès');
    }

    public function download(DocumentCV $cv)
    {
        // Check if the user is authorized to download this CV
        $this->authorize('view', $cv);

        return Storage::download($cv->chemin_stockage, $cv->nom_fichier);
    }

    public function delete(DocumentCV $cv)
    {
        // Check if the user is authorized to delete this CV
        $this->authorize('delete', $cv);

        // Delete the file
        Storage::delete($cv->chemin_stockage);

        // Delete the record
        $cv->delete();

        return redirect()->back()->with('success', 'CV supprimé avec succès');
    }
}
