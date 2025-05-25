<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\Organizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function summary()
    {
        $organizer = Organizer::where('user_id', Auth::id())->first();

        if (!$organizer) {
            return response()->json(['error' => 'Organizer not found'], 404);
        }

        // Résumé général
        $totalEvents = Event::where('organizer_id', $organizer->id)->count();

        $totalRegistrations = Ticket::whereHas('event', function ($query) use ($organizer) {
            $query->where('organizer_id', $organizer->id);
        })->count();

        $totalTicketLimit = Event::where('organizer_id', $organizer->id)->sum('ticket_limit');

        $avgParticipationRate = $totalTicketLimit > 0
            ? round(($totalRegistrations / $totalTicketLimit) * 100, 2)
            : 0;

        $nextEvent = Event::where('organizer_id', $organizer->id)
            ->where('date', '>', now())
            ->orderBy('date', 'asc')
            ->first();

        // Statistiques par événement
        $eventsStats = Event::where('organizer_id', $organizer->id)
            ->withCount('tickets as registrations')
            ->get()
            ->map(function ($event) {
                $event->participation_rate = $event->ticket_limit > 0
                    ? round(($event->registrations / $event->ticket_limit) * 100, 2)
                    : 0;
                $event->views = $event->popularity;
                return $event;
            });

        return response()->json([
            'summary' => [
                'total_events' => $totalEvents,
                'total_registrations' => $totalRegistrations,
                'avg_participation_rate' => $avgParticipationRate,
                'next_event' => $nextEvent,
            ],
            'events_stats' => $eventsStats
        ]);
    }
}