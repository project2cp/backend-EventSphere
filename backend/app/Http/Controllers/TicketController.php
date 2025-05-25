<?php
 namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Stripe\Stripe;
use Stripe\Charge;
use App\Models\Event;
use App\Models\Ticket;

class TicketController extends Controller
{
    public function registerForEvent(Request $request, $eventId)
    {
        $user = Auth::user();
        $event = Event::findOrFail($eventId);

        // Vérifier si l'utilisateur est déjà inscrit
        if (Ticket::where('user_id', $user->id)->where('event_id', $event->id)->exists()) {
            return response()->json(["message" => "Vous êtes déjà inscrit à cet événement."], 400);
        }

        // Gestion du paiement si l'événement est payant
        if ($event->price > 0) {
            Stripe::setApiKey(config('services.stripe.secret'));

            try {
                $charge = Charge::create([
                    "amount" => $event->price * 100, // Stripe utilise des centimes
                    "currency" => "usd",
                    "source" => $request->stripeToken,
                    "description" => "Paiement pour l'événement : " . $event->name,
                ]);
            } catch (\Exception $e) {
                return response()->json(["message" => "Erreur de paiement : " . $e->getMessage()], 402);
            }
        }

        // Générer un ticket
        $ticket = Ticket::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'is_paid' => $event->price > 0,
            'status' => 'confirmed'
        ]);

        // Génération du QR Code
        $qrCodePath = 'qr_codes/' . uniqid() . '.svg';
        QrCode::format('svg')->size(300)->generate($ticket->id, storage_path('app/public/' . $qrCodePath));
        $ticket->qr_code = $qrCodePath;
        $ticket->save();

        // Envoi d'un email de confirmation
        Mail::to($user->email)->send(new \App\Mail\TicketConfirmationMail($ticket));

        return response()->json([
            "message" => "Inscription réussie",
            "ticket" => $ticket
        ], 201);
    }
    public function refundTicket($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        // Vérifier si le ticket est payé
        if ($ticket->is_paid == 0) {
            return response()->json(["message" => "Ce ticket n'est pas éligible au remboursement"], 400);
        }

        // Vérifier si le statut est éligible
        if ($ticket->status !== 'confirmed') {
            return response()->json(["message" => "Le ticket doit être confirmé pour être remboursé"], 400);
        }

        // Effectuer le remboursement via Stripe
        $refund = \Stripe\Refund::create([
            'charge' => $ticket->stripe_charge_id,
        ]);

        if ($refund->status === 'succeeded') {
            $ticket->status = 'refunded';
            $ticket->save();
            return response()->json(["message" => "Remboursement effectué"], 200);
        }

        return response()->json(["message" => "Le remboursement a échoué"], 400);
    }

public function cancelRegistration($ticketId)
{
    $ticket = Ticket::findOrFail($ticketId);

    if ($ticket->status === 'confirmed') {
        $ticket->status = 'cancelled';
        $ticket->save();
        return response()->json(["message" => "Inscription annulée"], 200);
    }

    return response()->json(["message" => "Cette inscription ne peut pas être annulée"], 400);
}
public function buyTicket(Request $request, $eventId)
{
    $request->validate([
        'quantity' => 'required|integer|min:1',
    ]);

    $event = Event::findOrFail($eventId);

    if ($event->ticket_limit !== null && $event->tickets()->count() >= $event->ticket_limit) {
        return response()->json(['error' => 'Stock épuisé'], 400);
    }

    $totalPrice = $event->is_paid ? $event->ticket_price * $request->quantity : 0;

    $ticket = Ticket::create([
        'event_id' => $event->id,
        'user_id' => Auth::id(),
        'quantity' => $request->quantity,
        'total_price' => $totalPrice,
    ]);

    return response()->json($ticket, 201);
}
// Ajoutez cette méthode dans votre TicketController
public function getUserTickets()
{
    $user = Auth::user();

    $tickets = Ticket::with(['event' => function($query) {
        $query->select('id', 'title', 'date', 'location');
    }])
    ->where('user_id', $user->id)
    ->get([
        'id',
        'event_id',
        'status',
        'is_paid',
        'created_at',
        'qr_code'
    ]);

    // Formater la réponse
    $formattedTickets = $tickets->map(function($ticket) {
        return [
            'ticket_id' => $ticket->id,
            'status' => $ticket->status,
            'purchase_date' => $ticket->created_at->format('Y-m-d H:i'),
            'qr_code' => $ticket->qr_code ? asset('storage/' . $ticket->qr_code) : null,
            'event' => [
                'name' => $ticket->event->title,
                'date' => $ticket->event->date,
                'location' => $ticket->event->location
            ]
        ];
    });

    return response()->json(['tickets' => $formattedTickets]);
}
}
