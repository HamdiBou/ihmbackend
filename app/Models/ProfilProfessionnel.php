<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilProfessionnel extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function chercheurEmploi()
    {
        return $this->belongsTo(ChercheurEmploi::class);
    }

    public function experiences()
    {
        return $this->hasMany(ExperienceProfessionnelle::class, 'profil_id');
    }

    public function formations()
    {
        return $this->hasMany(FormationAcademique::class, 'profil_id');
    }

    public function competences()
    {
        return $this->belongsToMany(Competence::class, 'profil_competence', 'profil_id', 'competence_id');
    }

    // Generate profile from CV
    public function genererDepuisCV(DocumentCV $cv)
    {
        $this->update(['extracted_from_cv' => true]);

        // This would normally use a CV parser to extract structured data
        // Placeholder implementation
        return true;
    }
}
