<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Organizer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'logo', 'description', 'category', 'status','email_verification_token','document','organization_type', 'organization_name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function admins()
{
    return $this->hasMany(EventAdmin::class);
}

}
