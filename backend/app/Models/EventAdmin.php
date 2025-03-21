<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class EventAdmin extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id', 'user_id', 'permissions'
    ];

    protected $casts = [
        'permissions' => 'array', // Permet de stocker des permissions sous forme de tableau JSON
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
