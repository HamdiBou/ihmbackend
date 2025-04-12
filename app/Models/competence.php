<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class competence extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function profils()
    {
        return $this->belongsToMany(ProfilProfessionnel::class, 'profil_competence', 'competence_id', 'profil_id');
    }

    public function offres()
    {
        return $this->belongsToMany(Offre::class, 'offre_competence', 'competence_id', 'offre_id');
    }
}
