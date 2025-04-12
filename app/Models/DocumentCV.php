<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentCV extends Model
{
    // In DocumentCV model
    public function chercheurEmploi()
    {
        return $this->belongsTo(ChercheurEmploi::class);
    }

    // In ChercheurEmploi model
    public function documentCVs()
    {
        return $this->hasMany(DocumentCV::class);
    }
}
