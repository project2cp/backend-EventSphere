<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id', 'title', 'description', 'date',
        'location', 'ticket_limit', 'is_paid', 'ticket_price','category','popularity'
    ];
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

}
