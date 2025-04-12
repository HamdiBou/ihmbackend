<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    protected $guarded = [];
    public function recruteurs()
    {
        return $this->hasMany(Recruteur::class);
    }

    public function offres()
    {
        return $this->hasMany(Offre::class);
    }
}
