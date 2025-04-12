<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialite extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function offres()
    {
        return $this->belongsToMany(Offre::class, 'offre_specialite', 'specialite_id', 'offre_id');
    }
}
