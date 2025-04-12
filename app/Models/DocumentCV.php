<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentCV extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function recruteur()
    {
        return $this->belongsTo(Recruteur::class);
    }

    public function specialites()
    {
        return $this->belongsToMany(Specialite::class, 'offre_specialite', 'offre_id', 'specialite_id');
    }

    public function competences()
    {
        return $this->belongsToMany(Competence::class, 'offre_competence', 'offre_id', 'competence_id');
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class);
    }

    // Calculate match score with a CV
    public function calculerMatchScore(DocumentCV $cv)
    {
        // This would normally use NLP to compare job requirements with CV content
        // Placeholder implementation - returns random score between 0-100
        return rand(0, 100);
    }
}
