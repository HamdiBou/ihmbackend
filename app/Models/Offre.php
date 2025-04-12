<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offre extends Model
{
    public function candidatures()
    {
        return $this->hasMany(Candidature::class);
    }

    // In Candidature model
    public function offre()
    {
        return $this->belongsTo(Offre::class);
    }
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function candidature()
    {
        return $this->hasMany(Candidature::class);
    }
}
