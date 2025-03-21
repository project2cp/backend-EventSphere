<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Organizer;
use App\Models\Ticket;
use Illuminate\Contracts\Auth\MustVerifyEmail; // ✅ Ajout pour la vérification des emails
class User extends Authenticatable implements MustVerifyEmail // ✅ Activation de la vérification des emails

{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens,HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password','profile_photo', 'bio', 'interests','is_organizer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_organizer' => 'boolean',
        ];
    }
    public function organizer(): HasOne
    {
        return $this->hasOne(Organizer::class);
    }
 // ✅ Ajout de la relation entre User et Ticket
 public function tickets(): HasMany
 {
     return $this->hasMany(Ticket::class);
 }
}
