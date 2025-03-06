<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class chercheuremploi extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'nom',
        'prenom',
        'civilité',
        'email',
        'password',
        'confirmpassword',
        'datenaissance',
        'gouvernorat'];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

}
