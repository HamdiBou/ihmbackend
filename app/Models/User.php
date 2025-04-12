<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    public function recruteur()
    {
        return $this->hasOne(Recruteur::class);
    }

    public function chercheurEmploi()
    {
        return $this->hasOne(ChercheurEmploi::class);
    }

    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'expediteur_id');
    }

    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'destinataire_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
