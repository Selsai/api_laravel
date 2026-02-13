<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

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

    /**
     * Vérifie si l'utilisateur utilise un email professionnel
     * (différent des domaines gratuits courants)
     */
    public function usesProfessionalEmail(): bool
    {
        $freeProviders = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'free.fr', 'laposte.net'];
        
        $domain = substr(strrchr($this->email, '@'), 1);
        
        return !in_array($domain, $freeProviders);
    }
}