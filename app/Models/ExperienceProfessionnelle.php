<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienceProfessionnelle extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function profilProfessionnel()
    {
        return $this->belongsTo(ProfilProfessionnel::class, 'profil_id');
    }
}
