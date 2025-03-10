<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
class Ticket extends Model
{
    use HasFactory;

    protected $fillable = ['event_id', 'user_id', 'quantity',  'price','total_price','type','ticket_number','qr_id','is_paid','status'];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($ticket) {
            $ticket->ticket_number = strtoupper(Str::random(10));
        });
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
