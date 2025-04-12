<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LettreMotivation extends Model
{
    use HasFactory;

    protected $table = 'lettres_motivation';
    protected $guarded = [];

    public function chercheurEmploi()
    {
        return $this->belongsTo(ChercheurEmploi::class);
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class);
    }
}
