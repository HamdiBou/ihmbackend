<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidature extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function chercheurEmploi()
    {
        return $this->belongsTo(ChercheurEmploi::class);
    }

    public function offre()
    {
        return $this->belongsTo(Offre::class);
    }

    public function documentCV()
    {
        return $this->belongsTo(DocumentCV::class);
    }

    public function lettreMotivation()
    {
        return $this->belongsTo(LettreMotivation::class);
    }
}
