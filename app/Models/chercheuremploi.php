<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class chercheuremploi extends Model
{
    use HasFactory;

    protected $table = 'chercheurs_emploi';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentCVs()
    {
        return $this->hasMany(DocumentCV::class);
    }

    public function profilProfessionnel()
    {
        return $this->hasOne(ProfilProfessionnel::class);
    }

    public function lettresMotivation()
    {
        return $this->hasMany(LettreMotivation::class);
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class);
    }

    // Get the active CV document
    public function activeCV()
    {
        return $this->documentCVs()->where('is_active', true)->first();
    }

    // Import a new CV
    public function importCV($request)
    {
        $file = $request->file('cv');
        $path = $file->store('cvs', 'public');

        // Set all existing CVs to inactive
        $this->documentCVs()->update(['is_active' => false]);

        // Create new CV document
        return $this->documentCVs()->create([
            'nom_fichier' => $file->getClientOriginalName(),
            'type_fichier' => $file->getClientMimeType(),
            'taille' => $file->getSize(),
            'date_import' => now(),
            'chemin_stockage' => $path,
            'is_active' => true
        ]);
    }
}
